<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\NewHireException;
use App\Models\Pizza\NewHireExceptionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;

class NewHireExceptionController extends Controller
{
    /**
     * Create a new hire exception record from Cognito webhook.
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

            if (NewHireException::where('cognito_id', $mappedData['cognito_id'])->exists()) {
                return $this->errorResponse('New hire exception already exists', 409);
            }

            DB::beginTransaction();

            $newHireException = NewHireException::create($mappedData);

            $this->createItemRecords($newHireException->id, $json);

            DB::commit();

            return $this->successResponse('New hire exception created successfully', $newHireException->load('items'), 201);

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create new hire exception: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing new hire exception record from Cognito webhook.
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

            $newHireException = NewHireException::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $mappedData['entry_number'])
                ->first();

            if (!$newHireException) {
                // If record doesn't exist, create it instead (upsert behavior)
                $newHireException = NewHireException::create($mappedData);

                $this->createItemRecords($newHireException->id, $json);

                DB::commit();

                return $this->successResponse('New hire exception created successfully (upsert)', $newHireException->load('items'), 201);
            }

            $newHireException->update($mappedData);

            // Replace the new hire rows with what was resubmitted
            $newHireException->items()->delete();
            $this->createItemRecords($newHireException->id, $json);

            DB::commit();

            return $this->successResponse('New hire exception updated successfully', $newHireException->fresh('items'));

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update new hire exception: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a new hire exception record.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            // Get the Cognito ID from Form.Id + Entry.Number, or the top-level Id
            $entryNumber = data_get($json, 'Entry.Number');
            $formId = data_get($json, 'Form.Id');
            $cognitoId = data_get($json, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

            if (!$cognitoId && !$entryNumber) {
                return $this->errorResponse('Cognito ID or Entry Number is required', 400);
            }

            DB::beginTransaction();

            $newHireException = NewHireException::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $entryNumber)
                ->first();

            if (!$newHireException) {
                return $this->errorResponse('New hire exception not found', 404);
            }

            $newHireException->items()->delete();
            $newHireException->delete();

            DB::commit();

            return $this->successResponse('New hire exception deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete new hire exception: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export new hire exceptions to CSV, one row per new hire (parent fields repeated per item).
     */
    public function exportCsv(): Response
    {
        try {
            $records = NewHireException::with('items')->orderByDesc('id')->get();

            $columns = [
                'cognito_id',
                'entry_number',
                'store_manager_full_name',
                'store_label',
                'week',
                'submitted_date',
                'status',
                'new_hire_name_full',
                'new_hire_start_date',
                'new_hire_shifts_worked',
                'new_hire_hours_worked',
                'new_hire_feedback',
                'new_hire_hours_exception',
            ];

            $handle = fopen('php://memory', 'r+');

            fputcsv($handle, $columns);

            foreach ($records as $record) {
                $parentRow = [
                    $record->cognito_id,
                    $record->entry_number,
                    $record->store_manager_full_name,
                    $record->store_label,
                    $record->week,
                    optional($record->submitted_date)->toDateTimeString(),
                    $record->status,
                ];

                if ($record->items->isEmpty()) {
                    fputcsv($handle, array_merge($parentRow, ['', '', '', '', '', '']));
                    continue;
                }

                foreach ($record->items as $item) {
                    fputcsv($handle, array_merge($parentRow, [
                        $item->name_full,
                        optional($item->start_date)->toDateString(),
                        $item->shifts_worked,
                        $item->hours_worked,
                        $item->feedback,
                        $item->hours_exception,
                    ]));
                }
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            $filename = 'new_hire_exceptions_export_' . now()->format('Y-m-d_His') . '.csv';

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
     * Map Cognito JSON data to database columns.
     */
    private function mapJsonToDatabase(array $data): array
    {
        $storeManager = data_get($data, 'StoreManager', []);
        $store = data_get($data, 'Store', []);
        $entry = data_get($data, 'Entry', []);

        // Build cognito_id from Form.Id and Entry.Number, falling back to the top-level Id
        $formId = data_get($data, 'Form.Id');
        $entryNumber = data_get($entry, 'Number');
        $cognitoId = data_get($data, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

        return [
            'cognito_id' => $cognitoId,
            'entry_number' => $entryNumber,

            'store_manager_full_name' => data_get($storeManager, 'FirstAndLast'),
            'store_label' => data_get($store, 'Label'),
            'week' => data_get($data, 'Week'),
            'submitted_date' => data_get($entry, 'DateSubmitted'),
            'status' => data_get($entry, 'Status'),
        ];
    }

    /**
     * Create new hire item records from the repeatable NewHires array.
     */
    private function createItemRecords(int $newHireExceptionId, array $data): void
    {
        $newHires = data_get($data, 'NewHires', []);

        foreach ($newHires as $newHire) {
            NewHireExceptionItem::create($this->mapItemData($newHireExceptionId, $newHire));
        }
    }

    /**
     * Map a single NewHires array item to child-table columns.
     */
    private function mapItemData(int $newHireExceptionId, array $item): array
    {
        return [
            'new_hire_exception_id' => $newHireExceptionId,
            'name_full' => data_get($item, 'Name.FirstAndLast'),
            'start_date' => data_get($item, 'StartDate'),
            'shifts_worked' => data_get($item, 'ShiftsWorked'),
            'hours_worked' => data_get($item, 'HoursWorked'),
            'feedback' => data_get($item, 'Feedback'),
            'hours_exception' => data_get($item, 'HoursException'),
        ];
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
