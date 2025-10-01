<?php

namespace App\Http\Controllers\HealthPlan;

use App\Http\Controllers\Controller;
use App\Models\HealthPlan\ApplicationInfo;
use App\Models\HealthPlan\DependentInfo;
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

            $dependentsCreated = $this->processDependents($jsonData, $application->id);
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


        $application->dependents()->delete();
        $dependentsCreated = $this->processDependents($jsonData, $application->id);

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

    $tier = $this->determineCoverageTier($personalInfo);


    return [
        'store' =>$jsonData['jsonStore']['Label'],
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
        'coverage_tier' => $tier,
        'cognito_id' => $entry["Number"] ?? null
    ];
}
private function determineCoverageTier(array $personalInfo): ?string
{
    $selectedPlan = $personalInfo['SelectYourDesiredPlan2'] ?? null;

    if ($selectedPlan === "Outpatient Only Plan") {
        $planType = $personalInfo['OutpatientPlanTypes'] ?? null;

        switch ($planType) {
            case "Employee Only: $68.50":
                return "Member";
            case "Employee + Spouse: $188.50":
                return "Member + Spouse";
            case "Employee + Children: $160.50":
                return "Member + Child(ren)";
            case "Family: $280.50":
                return "Member + Family";
            default:
                return null;
        }
    }
    elseif ($selectedPlan === "Outpatient + Inpatient Plan") {
        $planType = $personalInfo['OutpatientInpatientPlanTypes'] ?? null;

        switch ($planType) {
            case "Employee Only: $141.50":
                return "Member";
            case "Employee + Spouse: $428.50":
                return "Member + Spouse";
            case "Employee + Children: $363.50":
                return "Member + Child(ren)";
            case "Family: $650.50":
                return "Member + Family";
            default:
                return null;
        }
    }

    return null;
}

private function processDependents(array $jsonData, int $applicationId): array
{
    $dependentsData = $jsonData['Dependents'] ?? [];
    $createdDependents = [];

    if (empty($dependentsData)) {
        return $createdDependents;
    }

    foreach ($dependentsData as $index => $dependentData) {
        $dependent1 = $dependentData['Dependent1'] ?? [];
        $name = $dependent1['Name'] ?? [];

        // Map dependent data to database fields
        $mappedDependent = [
            'application_id' => $applicationId,
            'count' => $dependentData['ItemNumber'] ?? ($index + 1),
            'dependent_first_name' => $name['First'] ?? null,
            'dependent_middle_initial' => $name['MiddleInitial'] ?? null,
            'dependent_last_name' => $name['Last'] ?? null,
            'ssn' => $dependent1['SocialSecurityNumberSSN'] ?? null,
            'gender' => is_array($dependent1['Gender'] ?? []) ?
                       implode(',', $dependent1['Gender']) :
                       ($dependent1['Gender'] ?? null),
            'dob' => !empty($dependent1['DateOfBirthDOB']) ? $dependent1['DateOfBirthDOB'] : null,
            'dependent_type' => $dependent1['DependentType'] ?? null,
            'cognito_id' => null // Dependents don't seem to have cognito_id in your structure
        ];

        // Validate dependent data
        $validator = Validator::make($mappedDependent, [
            'application_id' => 'required|integer|exists:applications_info,id',
            'count' => 'nullable|integer',
            'dependent_first_name' => 'nullable|string|max:255',
            'dependent_middle_initial' => 'nullable|string|max:10',
            'dependent_last_name' => 'nullable|string|max:255',
            'ssn' => 'nullable|string|max:15',
            'gender' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'dependent_type' => 'nullable|string|max:50',
            'cognito_id' => 'nullable|integer'
        ]);

        if ($validator->passes()) {
            $createdDependent = \App\Models\HealthPlan\DependentInfo::create($mappedDependent);
            $createdDependents[] = $createdDependent;

            Log::info('Dependent created successfully', [
                'dependent_id' => $createdDependent->id,
                'application_id' => $applicationId,
                'dependent_name' => $createdDependent->getFullNameAttribute()
            ]);
        } else {
            Log::warning('Dependent validation failed', [
                'errors' => $validator->errors(),
                'dependent_data' => $mappedDependent
            ]);
        }
    }

    return $createdDependents;
}

/**
 * Export applications info to CSV
 */
public function exportApplicationsInfo()
{
    try {
        // Get all applications with their relationships
        $applications = ApplicationInfo::with('dependents')->get();

        // Define CSV headers
        $headers = [
            'ID',
            'Store',
            'Add Term or Change',
            'Plan Choice',
            'First Name',
            'Middle Initial',
            'Last Name',
            'Full Name',
            'Date of Birth',
            'Street Address',
            'Street Address 2',
            'City',
            'State',
            'ZIP',
            'Phone',
            'Email',
            'Date of Hire',
            'Gender',
            'SSN',
            'Location',
            'Occupation',
            'Average Hours Per Week',
            'Marital Status',
            'Coverage Tier',
            'Cognito ID',
            'Dependents Count',
            'Created At',
            'Updated At'
        ];

        // Create CSV filename with timestamp
        $filename = 'health_plan_applications_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = storage_path('app/exports/' . $filename);

        // Create exports directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Open file for writing
        $file = fopen($filePath, 'w');

        // Write headers
        fputcsv($file, $headers);

        // Write data rows
        foreach ($applications as $application) {
            $row = [
                $application->id,
                $application->store,
                $application->add_term_or_change,
                $application->plan_choice,
                $application->first_name,
                $application->middle_initial,
                $application->last_name,
                $application->getFullNameAttribute(),
                $application->dob ? $application->dob->format('Y-m-d') : null,
                $application->street_address,
                $application->street_address_2,
                $application->city,
                $application->state_abbreviation,
                $application->zip,
                $application->phone,
                $application->email_address,
                $application->date_of_hire ? $application->date_of_hire->format('Y-m-d') : null,
                $application->gender,
                $application->ssn,
                $application->location,
                $application->occupation,
                $application->average_hours_worked_per_week,
                $application->marital_status,
                $application->coverage_tier,
                $application->cognito_id,
                $application->dependents->count(),
                $application->created_at->format('Y-m-d H:i:s'),
                $application->updated_at->format('Y-m-d H:i:s')
            ];
            fputcsv($file, $row);
        }

        fclose($file);

        Log::info('Applications CSV export completed', [
            'filename' => $filename,
            'records_count' => $applications->count(),
            'file_path' => $filePath
        ]);

        // Return file download response
        return response()->download($filePath, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        Log::error('Applications CSV Export Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to export applications CSV',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Export dependents info to CSV
 */
public function exportDependentsInfo()
{
    try {
        // Get all dependents with their application relationship
        $dependents = DependentInfo::with('application')->get();

        // Define CSV headers
        $headers = [
            'ID',
            'Application ID',
            'Application Cognito ID',
            'Primary Applicant Name',
            'Count',
            'Dependent First Name',
            'Dependent Middle Initial',
            'Dependent Last Name',
            'Dependent Full Name',
            'SSN',
            'Gender',
            'Date of Birth',
            'Dependent Type',
            'Cognito ID',
            'Created At',
            'Updated At'
        ];

        // Create CSV filename with timestamp
        $filename = 'health_plan_dependents_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = storage_path('app/exports/' . $filename);

        // Create exports directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Open file for writing
        $file = fopen($filePath, 'w');

        // Write headers
        fputcsv($file, $headers);

        // Write data rows
        foreach ($dependents as $dependent) {
            $row = [
                $dependent->id,
                $dependent->application_id,
                $dependent->application ? $dependent->application->cognito_id : null,
                $dependent->application ? $dependent->application->getFullNameAttribute() : null,
                $dependent->count,
                $dependent->dependent_first_name,
                $dependent->dependent_middle_initial,
                $dependent->dependent_last_name,
                $dependent->getFullNameAttribute(),
                $dependent->ssn,
                $dependent->gender,
                $dependent->dob ? $dependent->dob->format('Y-m-d') : null,
                $dependent->dependent_type,
                $dependent->cognito_id,
                $dependent->created_at->format('Y-m-d H:i:s'),
                $dependent->updated_at->format('Y-m-d H:i:s')
            ];
            fputcsv($file, $row);
        }

        fclose($file);

        Log::info('Dependents CSV export completed', [
            'filename' => $filename,
            'records_count' => $dependents->count(),
            'file_path' => $filePath
        ]);

        // Return file download response
        return response()->download($filePath, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        Log::error('Dependents CSV Export Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to export dependents CSV',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
