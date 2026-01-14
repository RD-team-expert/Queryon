<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\Approval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;

class ApprovalController extends Controller
{
    /**
     * Create a new approval record from Cognito webhook.
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('Approval creation request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        try {
            $json = $this->getJsonPayload($request);
            
            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            $mappedData = $this->mapJsonToDatabase($json);

            // Check if record already exists
            if (Approval::where('cognito_id', $mappedData['cognito_id'])->exists()) {
                Log::warning('Approval record already exists', ['cognito_id' => $mappedData['cognito_id']]);
                return $this->errorResponse('Approval record already exists', 409);
            }

            DB::beginTransaction();

            $approval = Approval::create($mappedData);

            DB::commit();

            Log::info('Approval created successfully', [
                'id' => $approval->id,
                'cognito_id' => $approval->cognito_id,
                'store' => $approval->store_label,
            ]);

            return $this->successResponse('Approval created successfully', $approval, 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to create approval: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing approval record from Cognito webhook.
     */
    public function update(Request $request): JsonResponse
    {
        Log::info('Approval update request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

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

            $approval = Approval::where('cognito_id', $cognitoId)->first();

            if (!$approval) {
                // If record doesn't exist, create it instead (upsert behavior)
                Log::info('Approval not found, creating new record', ['cognito_id' => $cognitoId]);
                $approval = Approval::create($mappedData);
                
                DB::commit();
                
                return $this->successResponse('Approval created successfully (upsert)', $approval, 201);
            }

            // Update existing record
            $approval->update($mappedData);

            DB::commit();

            Log::info('Approval updated successfully', [
                'id' => $approval->id,
                'cognito_id' => $approval->cognito_id,
            ]);

            return $this->successResponse('Approval updated successfully', $approval->fresh());

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to update approval: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an approval record.
     */
    public function delete(Request $request): JsonResponse
    {
        Log::info('Approval delete request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        try {
            $json = $this->getJsonPayload($request);
            
            if (!$json) {
                return $this->errorResponse('Invalid JSON payload', 400);
            }

            // Get the Cognito ID from Entry.Number or Id
            $entryNumber = data_get($json, 'Entry.Number');
            $formId = data_get($json, 'Form.Id');
            $cognitoId = $formId && $entryNumber ? "{$formId}-{$entryNumber}" : data_get($json, 'Id');

            if (!$cognitoId && !$entryNumber) {
                return $this->errorResponse('Cognito ID or Entry Number is required', 400);
            }

            DB::beginTransaction();

            // Try to find by cognito_id first, then by entry_number
            $approval = Approval::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $entryNumber)
                ->first();

            if (!$approval) {
                Log::warning('Approval record not found for deletion', [
                    'cognito_id' => $cognitoId,
                    'entry_number' => $entryNumber,
                ]);
                return $this->errorResponse('Approval record not found', 404);
            }

            $recordInfo = [
                'id' => $approval->id,
                'cognito_id' => $approval->cognito_id,
                'store' => $approval->store_label,
            ];

            $approval->delete();

            DB::commit();

            Log::info('Approval deleted successfully', $recordInfo);

            return $this->successResponse('Approval deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to delete approval: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all approvals as JSON with optional filters.
     */
    public function getData(Request $request): JsonResponse
    {
        Log::info('Approval data request received', [
            'ip' => $request->ip(),
            'filters' => $request->query(),
        ]);

        try {
            $query = Approval::query();

            // Apply filters
            $query->dateRange(
                $request->query('start_date'),
                $request->query('end_date')
            );

            if ($storeId = $request->query('store_id')) {
                $query->forStore($storeId);
            }

            if ($status = $request->query('status')) {
                $query->withStatus($status);
            }

            if ($decision = $request->query('decision')) {
                $query->withDecision($decision);
            }

            // Order by most recent first
            $query->orderBy('created_at', 'desc');

            $data = $query->get();

            Log::info('Approval data retrieved successfully', ['count' => $data->count()]);

            return response()->json([
                'success' => true,
                'record_count' => $data->count(),
                'data' => $data,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve approval data', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Failed to retrieve data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export approvals to CSV.
     */
    public function exportCsv(Request $request): Response
    {
        Log::info('Approval CSV export requested', [
            'ip' => $request->ip(),
            'filters' => $request->query(),
        ]);

        try {
            $query = Approval::query();

            // Apply filters
            $query->dateRange(
                $request->query('start_date'),
                $request->query('end_date')
            );

            if ($storeId = $request->query('store_id')) {
                $query->forStore($storeId);
            }

            if ($status = $request->query('status')) {
                $query->withStatus($status);
            }

            if ($decision = $request->query('decision')) {
                $query->withDecision($decision);
            }

            $query->orderBy('created_at', 'desc');

            $data = $query->get();
            $columns = Approval::getCsvColumns();

            // Create CSV
            $handle = fopen('php://memory', 'r+');

            // Write header row
            fputcsv($handle, $columns);

            // Write data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $col) {
                    $value = $item->{$col};
                    // Format datetime fields
                    if ($value instanceof \Carbon\Carbon) {
                        $value = $value->toDateTimeString();
                    }
                    $row[] = $value;
                }
                fputcsv($handle, $row);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            // Generate filename
            $filename = 'approvals_export_' . now()->format('Y-m-d_His') . '.csv';

            Log::info('Approval CSV export completed', [
                'filename' => $filename,
                'record_count' => $data->count(),
            ]);

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (Exception $e) {
            Log::error('Failed to export approvals to CSV', [
                'error' => $e->getMessage(),
            ]);

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
            Log::error('JSON parsing error', ['error' => json_last_error_msg()]);
            return null;
        }
        
        return $json;
    }

    /**
     * Map Cognito JSON data to database columns.
     */
    private function mapJsonToDatabase(array $data): array
    {
        // Extract sections
        $form = data_get($data, 'Form', []);
        $details = data_get($data, 'Details', []);
        $finalDecision = data_get($data, 'TheFinalDecision', []);
        $entry = data_get($data, 'Entry', []);
        $origin = data_get($entry, 'Origin', []);
        $user = data_get($entry, 'User', []);

        // Build cognito_id from Form.Id and Entry.Number
        $formId = data_get($form, 'Id');
        $entryNumber = data_get($entry, 'Number');
        $cognitoId = data_get($data, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

        // Parse timestamps
        $dateCreated = $this->parseTimestamp(data_get($entry, 'DateCreated'));
        $dateSubmitted = $this->parseTimestamp(data_get($entry, 'DateSubmitted'));
        $dateUpdated = $this->parseTimestamp(data_get($entry, 'DateUpdated'));

        return [
            // Cognito Form Info
            'cognito_id' => $cognitoId,
            'form_id' => $formId,
            'form_internal_name' => data_get($form, 'InternalName'),
            'form_name' => data_get($form, 'Name'),

            // Details Section
            'approval_reason' => data_get($details, 'WhatIsTheThingThatYouNeedApprovalFor'),
            'why' => data_get($details, 'Why'),
            'requester_first_name' => data_get($details, 'Name.First'),
            'requester_last_name' => data_get($details, 'Name.Last'),
            'request_date' => data_get($details, 'TodaysDate'),
            'store_id' => data_get($details, 'YourStore.Id'),
            'store_label' => data_get($details, 'YourStore.Label'),
            'consulted_manager_first_name' => data_get($details, 'NameTheManagerWhoYouConsulted.First'),
            'consulted_manager_last_name' => data_get($details, 'NameTheManagerWhoYouConsulted.Last'),

            // The Final Decision Section
            'decision' => data_get($finalDecision, 'Decision'),
            'decision_notes' => data_get($finalDecision, 'Notes'),

            // Entry Metadata
            'entry_number' => $entryNumber,
            'entry_admin_link' => data_get($entry, 'AdminLink'),
            'entry_date_created' => $dateCreated,
            'entry_date_submitted' => $dateSubmitted,
            'entry_date_updated' => $dateUpdated,
            'entry_public_link' => data_get($entry, 'PublicLink'),
            'entry_final_view_link' => data_get($entry, 'FinalViewLink'),
            'document_1_link' => data_get($entry, 'Document1'),
            'document_2_link' => data_get($entry, 'Document2'),

            // Entry Origin Info
            'origin_ip_address' => data_get($origin, 'IpAddress'),
            'origin_city' => data_get($origin, 'City'),
            'origin_country_code' => data_get($origin, 'CountryCode'),
            'origin_region' => data_get($origin, 'Region'),
            'origin_timezone' => data_get($origin, 'Timezone'),
            'origin_user_agent' => data_get($origin, 'UserAgent'),
            'origin_is_imported' => data_get($origin, 'IsImported', false),

            // Entry User Info
            'user_email' => data_get($user, 'Email'),
            'user_name' => data_get($user, 'Name'),

            // Entry Status
            'entry_action' => data_get($entry, 'Action'),
            'entry_role' => data_get($entry, 'Role'),
            'entry_status' => data_get($entry, 'Status'),
            'entry_version' => data_get($entry, 'Version', 1),
        ];
    }

    /**
     * Parse ISO 8601 timestamp to Carbon instance.
     */
    private function parseTimestamp(?string $timestamp): ?\Carbon\Carbon
    {
        if (!$timestamp) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timestamp);
        } catch (Exception $e) {
            Log::warning('Failed to parse timestamp', ['timestamp' => $timestamp]);
            return null;
        }
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

    /**
     * Show the import form.
     */
    public function showImportForm()
    {
        return view('approvals.import');
    }

    /**
     * Import approvals from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                return back()->with('error', 'Excel file is empty');
            }

            // Get header row
            $headers = array_shift($rows);
            
            // Map headers to column indexes
            $columnMap = $this->mapExcelColumns($headers);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because array is 0-indexed and we removed header

                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = $this->mapExcelRowToDatabase($row, $columnMap);

                    // Skip if no cognito_id
                    if (empty($data['cognito_id'])) {
                        $skipped++;
                        continue;
                    }

                    // Check if record exists
                    $existing = Approval::where('cognito_id', $data['cognito_id'])->first();

                    if ($existing) {
                        // Update existing record
                        $existing->update($data);
                    } else {
                        // Create new record
                        Approval::create($data);
                    }

                    $imported++;

                } catch (Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error('Import error on row ' . $rowNumber, [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }
            }

            DB::commit();

            $message = "Successfully imported {$imported} records.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} records.";
            }
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
            }

            Log::info('Excel import completed', [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => count($errors),
            ]);

            return back()->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Excel import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Map Excel column headers to array indexes.
     */
    private function mapExcelColumns(array $headers): array
    {
        $map = [];
        
        foreach ($headers as $index => $header) {
            $map[$header] = $index;
        }
        
        return $map;
    }

    /**
     * Map Excel row data to database fields.
     */
    private function mapExcelRowToDatabase(array $row, array $columnMap): array
    {
        $getValue = function($column) use ($row, $columnMap) {
            return isset($columnMap[$column]) ? ($row[$columnMap[$column]] ?? null) : null;
        };

        // Get values from Excel
        $approvalId = $getValue('APPROVALS_Id');
        $requestDate = $getValue('Details_TodaysDate');
        $entryDateCreated = $getValue('Entry_DateCreated');
        $entryDateSubmitted = $getValue('Entry_DateSubmitted');
        $entryDateUpdated = $getValue('Entry_DateUpdated');

        return [
            // Cognito Form Info
            'cognito_id' => $approvalId,
            'form_id' => '1318', // Static form ID for APPROVALS
            'form_internal_name' => 'APPROVALS',
            'form_name' => 'APPROVALS',

            // Details Section
            'approval_reason' => $getValue('Details_WhatIsTheThingThatYouNeedApprovalFor'),
            'why' => $getValue('Details_Why'),
            'requester_first_name' => $getValue('Details_Name_First'),
            'requester_last_name' => $getValue('Details_Name_Last'),
            'request_date' => $this->parseExcelDate($requestDate),
            'store_id' => $getValue('Details_YourStore'),
            'store_label' => $getValue('Details_YourStore_Label'),
            'consulted_manager_first_name' => $getValue('Details_NameTheManagerWhoYouConsulted_First'),
            'consulted_manager_last_name' => $getValue('Details_NameTheManagerWhoYouConsulted_Last'),

            // The Final Decision Section
            'decision' => $getValue('TheFinalDecision_Decision'),
            'decision_notes' => $getValue('TheFinalDecision_Notes'),

            // Entry Metadata
            'entry_number' => null,
            'entry_date_created' => $this->parseExcelDateTime($entryDateCreated),
            'entry_date_submitted' => $this->parseExcelDateTime($entryDateSubmitted),
            'entry_date_updated' => $this->parseExcelDateTime($entryDateUpdated),

            // Entry Status
            'entry_status' => $getValue('Entry_Status'),
        ];
    }

    /**
     * Parse Excel date value (M/D/YYYY format).
     */
    private function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If it's a numeric Excel date
            if (is_numeric($value)) {
                $date = Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }

            // If it's a string date (M/D/YYYY or M/D/YYYY H:MM AM)
            $date = \Carbon\Carbon::createFromFormat('n/j/Y', trim(explode(' ', $value)[0]));
            return $date->format('Y-m-d');

        } catch (Exception $e) {
            Log::warning('Failed to parse Excel date', ['value' => $value]);
            return null;
        }
    }

    /**
     * Parse Excel datetime value (M/D/YYYY H:MM AM format).
     */
    private function parseExcelDateTime($value): ?\Carbon\Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If it's a numeric Excel date
            if (is_numeric($value)) {
                return \Carbon\Carbon::instance(Date::excelToDateTimeObject($value));
            }

            // If it's a string datetime (M/D/YYYY H:MM AM)
            if (preg_match('/(\d+\/\d+\/\d+)\s+(\d+:\d+\s+[AP]M)/', $value, $matches)) {
                $datePart = $matches[1];
                $timePart = $matches[2];
                return \Carbon\Carbon::createFromFormat('n/j/Y g:i A', "{$datePart} {$timePart}");
            }

            // If it's just a date (M/D/YYYY)
            $date = \Carbon\Carbon::createFromFormat('n/j/Y', trim($value));
            return $date->startOfDay();

        } catch (Exception $e) {
            Log::warning('Failed to parse Excel datetime', ['value' => $value]);
            return null;
        }
    }
}
