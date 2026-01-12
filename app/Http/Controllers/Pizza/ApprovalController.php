<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\Approval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
}
