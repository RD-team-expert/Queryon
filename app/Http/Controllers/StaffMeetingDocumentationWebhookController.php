<?php

namespace App\Http\Controllers;

use App\Models\StaffMeetingDocumentation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StaffMeetingDocumentationWebhookController extends Controller
{
    /**
     * Create a new staff meeting documentation record from the Cognito webhook.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (! $json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $mappedData = $this->mapJsonToDatabase($json);

            if (! $mappedData['cognito_id']) {
                return $this->errorResponse('Cognito ID is required', 400);
            }

            DB::beginTransaction();

            $record = StaffMeetingDocumentation::updateOrCreate(
                ['cognito_id' => $mappedData['cognito_id']],
                $mappedData
            );

            DB::commit();

            return $this->successResponse('Staff meeting documentation created successfully', $record, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create staff meeting documentation: '.$e->getMessage(), 500);
        }
    }

    /**
     * Delete a staff meeting documentation record.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $json = $this->getJsonPayload($request);

            if (! $json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $entryNumber = data_get($json, 'Entry.Number');
            $formId = data_get($json, 'Form.Id');
            $cognitoId = data_get($json, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

            if (! $cognitoId && ! $entryNumber) {
                return $this->errorResponse('Cognito ID or Entry Number is required', 400);
            }

            DB::beginTransaction();

            $record = StaffMeetingDocumentation::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $entryNumber)
                ->first();

            if (! $record) {
                return $this->errorResponse('Staff meeting documentation not found', 404);
            }

            $record->delete();

            DB::commit();

            return $this->successResponse('Staff meeting documentation deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete staff meeting documentation: '.$e->getMessage(), 500);
        }
    }

    /**
     * Export staff meeting documentation records to CSV.
     */
    public function exportCsv(): Response
    {
        try {
            $records = StaffMeetingDocumentation::orderByDesc('id')->get();
            $columns = StaffMeetingDocumentation::getCsvColumns();

            $handle = fopen('php://memory', 'r+');

            fputcsv($handle, $columns);

            foreach ($records as $record) {
                $row = [];
                foreach ($columns as $col) {
                    $value = $record->{$col};

                    if ($value instanceof \Carbon\Carbon) {
                        $value = $value->toDateTimeString();
                    } elseif (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $row[] = $value;
                }
                fputcsv($handle, $row);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            $filename = 'staff_meeting_documentation_export_'.now()->format('Y-m-d_His').'.csv';

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (Exception $e) {
            return response('Failed to export: '.$e->getMessage(), 500);
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
     * Extract the full names from a repeatable Cognito name-list section
     * (GeneralManagers / StoreManagers / Specialists). Each entry can be
     * zero, one, or many items.
     */
    private function extractFullNames(array $data, string $key): array
    {
        return collect(data_get($data, $key, []))
            ->map(fn ($item) => data_get($item, 'Name.FirstAndLast'))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Map Cognito JSON data to database columns.
     */
    private function mapJsonToDatabase(array $data): array
    {
        $section = data_get($data, 'GMSection', []);
        $entry = data_get($data, 'Entry', []);

        $formId = data_get($data, 'Form.Id');
        $entryNumber = data_get($entry, 'Number');
        $cognitoId = data_get($data, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

        return [
            'cognito_id' => $cognitoId,
            'entry_number' => $entryNumber,

            'meeting_date' => data_get($section, 'TadaysDate'),
            'store_label' => data_get($section, 'Store.Label'),

            'attendance_screenshot_url' => data_get($section, 'PleaseProvideAScreenshotOfTheMeetingShowingWhoAttended.0.File'),
            'reports_screenshot_url' => data_get($section, 'PleaseAttachAScreenshotOfTheMeetingShowingTheReportsThatWereSharedSalesCustomerServiceHNRUpselling.0.File'),

            'meeting_outcome' => data_get($section, 'WhatWasTheMeetingOutcome'),
            'notes' => data_get($section, 'Notes'),

            'general_managers' => $this->extractFullNames($section, 'GeneralManagers'),
            'store_managers' => $this->extractFullNames($section, 'StoreManagers'),
            'specialists' => $this->extractFullNames($section, 'Specialists'),

            'submitted_at' => data_get($entry, 'DateSubmitted'),
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
