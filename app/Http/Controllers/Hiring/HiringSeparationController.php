<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\Hiring\HiringSeparation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HiringSeparationController extends Controller
{
    /**
     * Create a new hiring separation record
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $mappedData = $this->mapWebhookData($request->all());

            $separation = HiringSeparation::create($mappedData);

            return response()->json([
                'success' => true,
                'message' => 'Hiring separation created successfully',
                'data' => $separation
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create hiring separation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create hiring separation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing hiring separation record by cognito_id
     */
    public function update(Request $request): JsonResponse
{
    try {
        // Log the incoming request
        Log::info('=== UPDATE REQUEST STARTED ===');
        Log::info('Raw request data received:', $request->all());

        // Map the webhook data
        $mappedData = $this->mapWebhookData($request->all());
        Log::info('Data mapped successfully:', $mappedData);

        // Extract cognito_id
        $cognitoId = $mappedData['cognito_id'] ?? null;
        Log::info('Extracted cognito_id:', ['cognito_id' => $cognitoId]);

        if (!$cognitoId) {
            Log::warning('Update failed: Cognito ID is missing from request');
            return response()->json([
                'success' => false,
                'message' => 'Cognito ID is required'
            ], 400);
        }

        // Try to find the record
        Log::info('Searching for hiring separation with cognito_id:', ['cognito_id' => $cognitoId]);
        $separation = HiringSeparation::where('cognito_id', $cognitoId)->first();

        if (!$separation) {
            Log::warning('Update failed: Hiring separation not found', [
                'cognito_id' => $cognitoId,
                'searched_in' => 'hiring_separations table'
            ]);

            // Log all existing cognito_ids for comparison
            $existingIds = HiringSeparation::pluck('cognito_id')->toArray();
            Log::info('Existing cognito_ids in database:', ['ids' => $existingIds]);

            return response()->json([
                'success' => false,
                'message' => 'Hiring separation not found'
            ], 404);
        }

        Log::info('Record found, current data:', $separation->toArray());

        // Perform the update
        Log::info('Attempting to update record with new data');
        $separation->update($mappedData);

        // Refresh and log the updated data
        $updatedSeparation = $separation->fresh();
        Log::info('Record updated successfully:', $updatedSeparation->toArray());
        Log::info('=== UPDATE REQUEST COMPLETED SUCCESSFULLY ===');

        return response()->json([
            'success' => true,
            'message' => 'Hiring separation updated successfully',
            'data' => $updatedSeparation
        ], 200);

    } catch (\Exception $e) {
        Log::error('=== UPDATE REQUEST FAILED ===');
        Log::error('Exception occurred during update', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update hiring separation',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Delete a hiring separation record by cognito_id
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $cognitoId = $request->input('Entry.Number');

            if (!$cognitoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required'
                ], 400);
            }

            $separation = HiringSeparation::where('cognito_id', $cognitoId)->first();

            if (!$separation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hiring separation not found'
                ], 404);
            }

            $separation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hiring separation deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete hiring separation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hiring separation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map webhook JSON data to database fields
     */
    private function mapWebhookData(array $webhookData): array
{
    return [
         // Store Manager Information
        'store_manager_first_name' => $webhookData['YourRequest']['StoreManagerSection']['store_manager_name']['First'] ?? null,
        'store_manager_last_name' => $webhookData['YourRequest']['StoreManagerSection']['store_manager_name']['Last'] ?? null,
        'franchisee_store' => $webhookData['YourRequest']['StoreManagerSection']['franchisee_store']['Label'] ?? null,
        'date_of_request' => $webhookData['YourRequest']['StoreManagerSection']['date_of_request'] ?? null,

        // Pizza Employee Information
        'pizza_emp_first_name' => $webhookData['YourRequest']['EmployeeInformation']['pizza_emp_name']['First'] ?? null,
        'pizza_emp_last_name' => $webhookData['YourRequest']['EmployeeInformation']['pizza_emp_name']['Last'] ?? null,
        'pizza_emp_paychex_id' => $webhookData['YourRequest']['EmployeeInformation']['pizza_emp_paychex_id'] ?? null,
        'separation_type' => $webhookData['YourRequest']['EmployeeInformation']['SeparationInformation']['separation_type'] ?? null,
        'final_w_date' => $webhookData['YourRequest']['EmployeeInformation']['SeparationInformation']['final_w_date'] ?? null,

        // Supervisor Information - NOTE: At root level, not in YourRequest
        'supervisor_first_name' => $webhookData['SuperviserSection']['supervisor_name']['First'] ?? null,
        'supervisor_last_name' => $webhookData['SuperviserSection']['supervisor_name']['Last'] ?? null,
        'supervisor_accepted' => $this->convertToBoolean($webhookData['SuperviserSection']['supervisor_accepted'] ?? null),

        // Hiring Specialist Information - NOTE: At root level, not in YourRequest
        'hiring_specialist_first_name' => $webhookData['HiringDepartmentSection']['hiring_specialist_name']['First'] ?? null,
        'hiring_specialist_last_name' => $webhookData['HiringDepartmentSection']['hiring_specialist_name']['Last'] ?? null,
        'hiring_completed_separation' => $this->convertToBoolean($webhookData['HiringDepartmentSection']['hiring_completed_separation'] ?? null),
        'hiring_date_finished' => $webhookData['HiringDepartmentSection']['hiring_date_finished'] ?? null,

        // Cognito ID
        'cognito_id' => $webhookData['Entry']['Number'] ?? null,
    ];
}

/**
 * Convert string values (yes/no, true/false, 1/0) to boolean
 */
private function convertToBoolean($value): bool
{
    if ($value === null) {
        return false;
    }

    // Handle string "yes" and "no"
    if (is_string($value)) {
        $value = strtolower($value);
        if ($value === 'yes') {
            return true;
        }
        if ($value === 'no') {
            return false;
        }
    }

    // Use PHP's filter_var for standard boolean strings
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}


public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
{
    try {
        $fileName = 'hiring_separations_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Store Manager First Name',
                'Store Manager Last Name',
                'Franchisee Store',
                'Date of Request',
                'Pizza Employee First Name',
                'Pizza Employee Last Name',
                'Pizza Employee Paychex ID',
                'Separation Type',
                'Final Work Date',
                'Supervisor First Name',
                'Supervisor Last Name',
                'Supervisor Accepted',
                'Hiring Specialist First Name',
                'Hiring Specialist Last Name',
                'Hiring Completed Separation',
                'Hiring Date Finished',
                'Cognito ID',
                'Created At',
                'Updated At'
            ]);

            // Export data in chunks to handle large datasets efficiently
            HiringSeparation::chunk(500, function ($separations) use ($handle) {
                foreach ($separations as $separation) {
                    fputcsv($handle, [
                        $separation->id,
                        $separation->store_manager_first_name,
                        $separation->store_manager_last_name,
                        $separation->franchisee_store,
                        // Use Carbon format or fallback to string
                        $separation->date_of_request ? \Carbon\Carbon::parse($separation->date_of_request)->format('Y-m-d') : '',
                        $separation->pizza_emp_first_name,
                        $separation->pizza_emp_last_name,
                        $separation->pizza_emp_paychex_id,
                        $separation->separation_type,
                        // Use Carbon format or fallback to string
                        $separation->final_w_date ? \Carbon\Carbon::parse($separation->final_w_date)->format('Y-m-d') : '',
                        $separation->supervisor_first_name,
                        $separation->supervisor_last_name,
                        $separation->supervisor_accepted ? 'Yes' : 'No',
                        $separation->hiring_specialist_first_name,
                        $separation->hiring_specialist_last_name,
                        $separation->hiring_completed_separation ? 'Yes' : 'No',
                        // Use Carbon format or fallback to string
                        $separation->hiring_date_finished ? \Carbon\Carbon::parse($separation->hiring_date_finished)->format('Y-m-d') : '',
                        $separation->cognito_id,
                        // Format timestamps
                        $separation->created_at ? \Carbon\Carbon::parse($separation->created_at)->format('Y-m-d H:i:s') : '',
                        $separation->updated_at ? \Carbon\Carbon::parse($separation->updated_at)->format('Y-m-d H:i:s') : '',
                    ]);
                }
            });

            fclose($handle);

        }, $fileName, $headers);

    } catch (\Exception $e) {
        Log::error('Failed to export hiring separations: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to export CSV',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
