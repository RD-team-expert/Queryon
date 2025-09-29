<?php

namespace App\Http\Controllers\HealthPlan;

use App\Http\Controllers\Controller;
use App\Models\HealthPlan\ApplicationInfo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HealthPlanController extends Controller
{
    /**
     * Create a new health plan application
     */
    public function create(Request $request): JsonResponse
    {
        try {
            // Decode JSON data from request
            $jsonData = $request->json()->all();

            // Validate JSON data exists
            if (empty($jsonData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No JSON data provided'
                ], 400);
            }

            // Map JSON fields to database fields based on the image mapping
            $applicationData = $this->mapJsonToApplicationData($jsonData);

            // Validate the mapped data
            $validator = Validator::make($applicationData, [
                'add_term_or_change' => 'nullable|string|max:255',
                'plan_choice' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'first_name' => 'nullable|string|max:255',
                'middle_initial' => 'nullable|string|max:10',
                'dob' => 'nullable|date',
                'street_address' => 'nullable|string|max:255',
                'street_address_2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state_abbreviation' => 'nullable|string|max:50',
                'zip' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'email_address' => 'nullable|email|max:255',
                'date_of_hire' => 'nullable|date',
                'gender' => 'nullable|string|max:20',
                'ssn' => 'nullable|string|max:15',
                'location' => 'nullable|string|max:255',
                'occupation' => 'nullable|string|max:255',
                'average_hours_worked_per_week' => 'nullable|numeric|min:0|max:168',
                'marital_status' => 'nullable|string|max:50',
                'coverage_tier' => 'nullable|string|max:255',
                'cognito_id' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the application record
            $application = ApplicationInfo::create($applicationData);

            return response()->json([
                'success' => true,
                'message' => 'Health plan application created successfully',
                'data' => [
                    'application_id' => $application->id,
                    'application' => $application
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Health Plan Application Creation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create health plan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing health plan application by cognito_id from request
     */
   /**
 * Update an existing health plan application by cognito_id from request
 */
public function update(Request $request): JsonResponse
{
    try {
        Log::info('Health Plan Update Started', [
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_headers' => $request->headers->all()
        ]);

        // Decode JSON data from request
        $jsonData = $request->json()->all();

        Log::info('Update JSON Data Received', [
            'json_data_keys' => array_keys($jsonData),
            'json_data_size' => count($jsonData),
            'has_entry' => isset($jsonData['Entry']),
            'has_personal_info' => isset($jsonData['PersonalInformation'])
        ]);

        if (empty($jsonData)) {
            Log::warning('Update Failed: No JSON data provided');
            return response()->json([
                'success' => false,
                'message' => 'No JSON data provided'
            ], 400);
        }

        // Get cognito_id from the request JSON data
        $entry = $jsonData["Entry"] ?? [];
        $cognitoId = $entry["Number"] ?? null;

        Log::info('Extracting Cognito ID', [
            'entry_data' => $entry,
            'cognito_id' => $cognitoId,
            'entry_keys' => array_keys($entry)
        ]);

        if (!$cognitoId) {
            Log::warning('Update Failed: Missing cognito_id', [
                'entry_structure' => $entry
            ]);
            return response()->json([
                'success' => false,
                'message' => 'cognito_id (Entry.Number) is required for update'
            ], 400);
        }

        Log::info('Searching for application by cognito_id', [
            'cognito_id' => $cognitoId
        ]);

        // Find application by cognito_id
        $application = ApplicationInfo::where('cognito_id', $cognitoId)->first();

        if (!$application) {
            Log::warning('Application not found', [
                'cognito_id' => $cognitoId,
                'total_applications' => ApplicationInfo::count(),
                'existing_cognito_ids' => ApplicationInfo::pluck('cognito_id')->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Application not found for cognito_id: ' . $cognitoId
            ], 404);
        }

        Log::info('Application found', [
            'application_id' => $application->id,
            'cognito_id' => $application->cognito_id,
            'current_data' => $application->toArray()
        ]);

        // Map JSON fields to database fields
        $applicationData = $this->mapJsonToApplicationData($jsonData);

        Log::info('Mapped application data', [
            'mapped_data' => $applicationData,
            'data_keys' => array_keys($applicationData),
            'non_null_values' => array_filter($applicationData, function($value) {
                return !is_null($value) && $value !== '';
            })
        ]);

        // Remove cognito_id from update to prevent changing it
        unset($applicationData['cognito_id']);

        Log::info('Removed cognito_id from update data', [
            'final_update_data' => $applicationData
        ]);

        // Update the application
        $application->update($applicationData);

        Log::info('Application updated successfully', [
            'application_id' => $application->id,
            'cognito_id' => $application->cognito_id,
            'updated_data' => $application->fresh()->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Health plan application updated successfully',
            'data' => [
                'application_id' => $application->id,
                'application' => $application->fresh()
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Health Plan Application Update Error', [
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'request_data' => $request->json()->all() ?? 'No JSON data'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update health plan application',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Delete a health plan application by cognito_id from request
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            // Decode JSON data from request
            $jsonData = $request->json()->all();

            if (empty($jsonData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No JSON data provided'
                ], 400);
            }

            // Get cognito_id from the request JSON data
            $entry = $jsonData["Entry"] ?? [];
            $cognitoId = $entry["Number"] ?? null;

            if (!$cognitoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'cognito_id (Entry.Number) is required for delete'
                ], 400);
            }

            // Find application by cognito_id
            $application = ApplicationInfo::where('cognito_id', $cognitoId)->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found for cognito_id: ' . $cognitoId
                ], 404);
            }

            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Health plan application deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Health Plan Application Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete health plan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map JSON data to ApplicationInfo database fields
     * Based on the mapping shown in the image
     */
    private function mapJsonToApplicationData(array $jsonData): array
{
    // Extract nested data safely
    $personalInfo = $jsonData['PersonalInformation'] ?? [];
    $streetAddress = $personalInfo['StreetAddress'] ?? [];
    $name = $personalInfo['Name'] ?? [];
    $entry = $jsonData["Entry"] ?? [];

    return [
        'add_term_or_change' => $jsonData['jsonGroupManager']['Status'] ?? null,
        'plan_choice' => $personalInfo['SelectYourDesiredPlan2'] ?? null,
        'last_name' => $name['Last'] ?? null,
        'first_name' => $name['First'] ?? null,
        'middle_initial' => $name['MiddleInitial'] ?? null,
        'dob' => !empty($personalInfo['DateOfBirthDOB']) ? $personalInfo['DateOfBirthDOB'] : null,
        'street_address' => $streetAddress['StreetAddress'] ?? null,
        'street_address_2' => $streetAddress['Line2'] ?? null,
        'city' => $streetAddress['City'] ?? null,
        'state_abbreviation' => $streetAddress['State'] ?? null,
        'zip' => $streetAddress['PostalCode'] ?? null,
        'phone' => $personalInfo['PhoneNumber'] ?? null,
        'email_address' => $personalInfo['Email'] ?? null,
        'date_of_hire' => !empty($personalInfo['DateOfHire']) ? $personalInfo['DateOfHire'] : null,
        'gender' => is_array($personalInfo['Gender'] ?? []) ?
                  ($personalInfo['Gender'][0] ?? null) :
                  ($personalInfo['Gender'] ?? null),
        'ssn' => $personalInfo['SocialSecurityNumberSSN'] ?? null,
        'location' => $personalInfo['WorkLocation']['FullAddress'] ?? null,
        'occupation' => $personalInfo['Occupation'] ?? null,
        'average_hours_worked_per_week' => $personalInfo['AverageHoursWorkedPerWeek'] ?? 0,
        'marital_status' => $personalInfo['MaritalStatus'] ?? null,
        'coverage_tier' => null,
        'cognito_id' => $entry["Number"] ?? null
    ];
}
}
