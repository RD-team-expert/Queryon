<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\WeeklyPizzaMissionsHour;
use App\Models\Pizza\WeeklyPizzaMissionsHourItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;

class WeeklyPizzaMissionsHoursController extends Controller
{
    /**
     * Create a weekly pizza missions hours record from Cognito webhook.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $mappedData = $this->mapJsonToDatabase($json);

            if (!$mappedData['cognito_id']) {
                return $this->errorResponse('Cognito ID is required', 400);
            }

            if (WeeklyPizzaMissionsHour::where('cognito_id', $mappedData['cognito_id'])->exists()) {
                return $this->errorResponse('Weekly pizza missions hours already exists', 409);
            }

            DB::beginTransaction();

            $record = WeeklyPizzaMissionsHour::create($mappedData);

            $this->createItemRecords($record->id, $json);

            DB::commit();

            return $this->successResponse('Weekly pizza missions hours created successfully', $record->load('items'), 201);

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create weekly pizza missions hours: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing weekly pizza missions hours record from Cognito webhook.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $mappedData = $this->mapJsonToDatabase($json);
            $cognitoId = $mappedData['cognito_id'];

            if (!$cognitoId) {
                return $this->errorResponse('Cognito ID is required', 400);
            }

            DB::beginTransaction();

            $record = WeeklyPizzaMissionsHour::where('cognito_id', $cognitoId)->first();

            if (!$record) {
                // If record doesn't exist, create it instead (upsert behavior)
                $record = WeeklyPizzaMissionsHour::create($mappedData);

                $this->createItemRecords($record->id, $json);

                DB::commit();

                return $this->successResponse('Weekly pizza missions hours created successfully (upsert)', $record->load('items'), 201);
            }

            $record->update($mappedData);

            // Replace the daily rows with what was resubmitted
            $record->items()->delete();
            $this->createItemRecords($record->id, $json);

            DB::commit();

            return $this->successResponse('Weekly pizza missions hours updated successfully', $record->fresh('items'));

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update weekly pizza missions hours: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a weekly pizza missions hours record.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $cognitoId = $this->resolveCognitoId($json);

            if (!$cognitoId) {
                return $this->errorResponse('Cognito ID is required', 400);
            }

            $record = WeeklyPizzaMissionsHour::where('cognito_id', $cognitoId)->first();

            if (!$record) {
                return $this->errorResponse('Weekly pizza missions hours not found', 404);
            }

            DB::beginTransaction();

            $record->items()->delete();
            $record->delete();

            DB::commit();

            return $this->successResponse('Weekly pizza missions hours deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete weekly pizza missions hours: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export weekly pizza missions hours to CSV, one row per daily entry (parent fields repeated per entry).
     */
    public function exportCsv(): Response
    {
        try {
            $records = WeeklyPizzaMissionsHour::with('items')->orderByDesc('id')->get();

            $columns = [
                'cognito_id',
                'entry_number',
                'name_full',
                'week_number',
                'days_filled',
                'hourly_pay',
                'day',
                'day_total_hours',
                'work_type',
                'what_work',
                'store_label',
                'hours',
                'gas',
                'invoice',
                'deduction',
                'proof',
                'money_owed',
                'note',
            ];

            $handle = fopen('php://memory', 'r+');

            fputcsv($handle, $columns);

            foreach ($records as $record) {
                $parentRow = [
                    $record->cognito_id,
                    $record->entry_number,
                    $record->name_full,
                    $record->week_number,
                    implode(', ', $record->days_filled ?? []),
                    $record->hourly_pay,
                ];

                if ($record->items->isEmpty()) {
                    fputcsv($handle, array_merge($parentRow, array_fill(0, 12, '')));
                    continue;
                }

                // Keep rows in week order (Tuesday first), then in the order they were entered
                $items = $record->items->sortBy(fn ($item) => array_search($item->day, WeeklyPizzaMissionsHour::DAYS));

                foreach ($items as $item) {
                    fputcsv($handle, array_merge($parentRow, [
                        $item->day,
                        $record->{WeeklyPizzaMissionsHour::totalColumnForDay($item->day)},
                        $item->work_type,
                        $item->what_work,
                        $item->store_label,
                        $item->hours,
                        $item->gas,
                        $item->invoice,
                        $item->deduction,
                        collect($item->proof ?? [])->pluck('Name')->implode(', '),
                        $item->money_owed,
                        $item->note,
                    ]));
                }
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            $filename = 'weekly_pizza_missions_hours_export_' . now()->format('Y-m-d_His') . '.csv';

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (Exception $e) {
            return response('Failed to export: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Parse JSON from request body.
     */
    private function getJsonPayload(Request $request): ?array
    {
        $json = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $json;
    }

    /**
     * Build the Cognito entry ID from the top-level Id, falling back to Form.Id + Entry.Number.
     */
    private function resolveCognitoId(array $data): ?string
    {
        $formId = data_get($data, 'Form.Id');
        $entryNumber = data_get($data, 'Entry.Number');

        return data_get($data, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);
    }

    /**
     * Map Cognito JSON data to database columns.
     */
    private function mapJsonToDatabase(array $data): array
    {
        $mapped = [
            'cognito_id' => $this->resolveCognitoId($data),
            'entry_number' => data_get($data, 'Entry.Number'),

            'name_full' => data_get($data, 'Name.FirstAndLast'),
            'week_number' => data_get($data, 'WeekNumber'),
            'days_filled' => data_get($data, 'WhichDaysYouWannaFillOut'),
            'hourly_pay' => data_get($data, 'HourlyPay'),
        ];

        foreach (WeeklyPizzaMissionsHour::DAYS as $day) {
            $mapped[WeeklyPizzaMissionsHour::totalColumnForDay($day)] = data_get($data, "{$day}TotalHours");
        }

        return $mapped;
    }

    /**
     * Create daily entry records from the repeatable Tuesday ... Monday arrays.
     */
    private function createItemRecords(int $recordId, array $data): void
    {
        foreach (WeeklyPizzaMissionsHour::DAYS as $day) {
            foreach (data_get($data, $day, []) ?? [] as $entry) {
                $itemData = $this->mapItemData($recordId, $day, $entry);

                // Days that weren't filled out still arrive with one blank entry, skip those
                if ($this->isBlankItem($itemData)) {
                    continue;
                }

                WeeklyPizzaMissionsHourItem::create($itemData);
            }
        }
    }

    /**
     * Map a single daily entry to child-table columns.
     */
    private function mapItemData(int $recordId, string $day, array $item): array
    {
        $whatWork = strtolower((string) data_get($item, 'WhatWork'));
        $isMaintenance = str_contains($whatWork, 'maint');
        $isMgmt = str_contains($whatWork, 'mgmt');

        // Maintenance Work carries gas, invoice and proof; MGMT Work carries money owed
        $proof = $isMaintenance ? data_get($item, 'Proof') : null;

        return [
            'weekly_pizza_missions_hour_id' => $recordId,
            'day' => $day,
            'work_type' => data_get($item, 'WorkType'),
            'what_work' => data_get($item, 'WhatWork'),
            'note' => data_get($item, 'Note'),
            'store_label' => data_get($item, 'Store.Label'),
            'hours' => data_get($item, 'Hours'),
            'gas' => $isMaintenance ? data_get($item, 'Gas') : null,
            'invoice' => $isMaintenance ? data_get($item, 'Invoice') : null,
            'deduction' => data_get($item, 'Deduction'),
            'proof' => $proof ?: null,
            'money_owed' => $isMgmt ? data_get($item, 'MoneyOwed') : null,
        ];
    }

    /**
     * True when a mapped daily entry has no values besides its keys.
     */
    private function isBlankItem(array $itemData): bool
    {
        return collect($itemData)
            ->except(['weekly_pizza_missions_hour_id', 'day'])
            ->every(fn ($value) => $value === null || $value === '');
    }

    /**
     * Return a success JSON response.
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
