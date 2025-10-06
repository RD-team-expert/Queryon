<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hiring\HiringRequest;
use App\Models\Hiring\Hire;
use Illuminate\Support\Facades\Log;

class HiringRequestsController extends Controller
{
    /**
     * Create a new hiring request.
     */
    public function create(Request $request)
    {
        Log::info('=== HIRING REQUEST CREATE START ===');

        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);
            Log::info('Raw JSON data received:', ['data' => $data]);

            // Map data for hiring_requests table
            $hiringRequestData = $this->mapHiringRequestData($data);
            Log::info('Mapped hiring request data:', ['mapped_data' => $hiringRequestData]);

            // Create the hiring request
            $hiringRequest = HiringRequest::create($hiringRequestData);
            Log::info('Hiring request created successfully:', ['id' => $hiringRequest->id, 'cognito_id' => $hiringRequest->cognito_id]);

            // Create hire records if EMPInfo exists
            $this->createHireRecords($hiringRequest->id, $data);

            Log::info('=== HIRING REQUEST CREATE SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Hiring request created successfully',
                'data' => $hiringRequest->load('hires')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating hiring request:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating hiring request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing hiring request.
     */
    public function update(Request $request)
    {
        Log::info('=== HIRING REQUEST UPDATE START ===');

        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);
            Log::info('Raw JSON data received for update:', ['data' => $data]);

            // Get cognito_id from the data (Entry -> Number)
            $cognitoId = $data['Entry']['Number'] ?? null;
            Log::info('Extracted Cognito ID from Entry->Number:', ['cognito_id' => $cognitoId]);

            if (!$cognitoId) {
                Log::warning('Cognito ID missing in update request at Entry->Number');
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required for update'
                ], 400);
            }

            // Find hiring request by cognito_id
            $hiringRequest = HiringRequest::where('cognito_id', $cognitoId)->first();
            Log::info('Hiring request lookup result:', ['found' => $hiringRequest ? true : false]);

            if (!$hiringRequest) {
                Log::warning('Hiring request not found for cognito_id:', ['cognito_id' => $cognitoId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Hiring request not found'
                ], 404);
            }

            // Map and update data
            $hiringRequestData = $this->mapHiringRequestData($data);
            Log::info('Mapped data for update:', ['mapped_data' => $hiringRequestData]);

            $hiringRequest->update($hiringRequestData);
            Log::info('Hiring request updated successfully:', ['id' => $hiringRequest->id]);

            // Delete existing hires and recreate them
            $deletedCount = $hiringRequest->hires()->count();
            $hiringRequest->hires()->delete();
            Log::info('Deleted existing hires:', ['count' => $deletedCount]);

            $this->createHireRecords($hiringRequest->id, $data);

            Log::info('=== HIRING REQUEST UPDATE SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Hiring request updated successfully',
                'data' => $hiringRequest->fresh()->load('hires')
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating hiring request:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating hiring request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a hiring request.
     */
    public function delete(Request $request)
    {
        Log::info('=== HIRING REQUEST DELETE START ===');

        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);
            Log::info('Raw JSON data received for delete:', ['data' => $data]);

            // Get cognito_id from the data (Entry -> Number)
            $cognitoId = $data['Entry']['Number'] ?? null;
            Log::info('Extracted Cognito ID for deletion from Entry->Number:', ['cognito_id' => $cognitoId]);

            if (!$cognitoId) {
                Log::warning('Cognito ID missing in delete request at Entry->Number');
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required for deletion'
                ], 400);
            }

            // Find hiring request by cognito_id
            $hiringRequest = HiringRequest::where('cognito_id', $cognitoId)->first();
            Log::info('Hiring request lookup for deletion:', ['found' => $hiringRequest ? true : false]);

            if (!$hiringRequest) {
                Log::warning('Hiring request not found for deletion:', ['cognito_id' => $cognitoId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Hiring request not found'
                ], 404);
            }

            // Delete associated hires first
            $hiresCount = $hiringRequest->hires()->count();
            $hiringRequest->hires()->delete();
            Log::info('Deleted associated hires:', ['count' => $hiresCount]);

            // Delete the hiring request
            $hiringRequest->delete();
            Log::info('Hiring request deleted successfully:', ['id' => $hiringRequest->id]);

            Log::info('=== HIRING REQUEST DELETE SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Hiring request deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting hiring request:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting hiring request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get and decode JSON data from request.
     */
    private function getJsonData(Request $request)
    {
        Log::info('Getting JSON data from request');
        $jsonData = $request->getContent();
        Log::info('Raw JSON string:', ['json_string' => $jsonData]);

        $decodedData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error:', ['error' => json_last_error_msg()]);
            throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        Log::info('JSON decoded successfully');
        return $decodedData;
    }

    /**
     * Map JSON data to hiring_requests table structure.
     */
    private function mapHiringRequestData($data)
    {
        Log::info('Starting data mapping for hiring_requests table');

        // Log the structure we're looking for
        Log::info('Looking for Entry structure:', [
            'exists' => isset($data['Entry']),
            'number_exists' => isset($data['Entry']['Number'])
        ]);

        Log::info('Looking for YourRequest structure:', [
            'exists' => isset($data['YourRequest']),
            'store_managers_name_exists' => isset($data['YourRequest']['StoreManagersName']),
            'hiring_needs_exists' => isset($data['YourRequest']['HiringNeeds'])
        ]);

        Log::info('Looking for SupervisorsApproval structure:', [
            'exists' => isset($data['SupervisorsApproval']),
            'supervisors_name_exists' => isset($data['SupervisorsApproval']['SupervisorsName'])
        ]);

        Log::info('Looking for HRReview structure:', [
            'exists' => isset($data['HRReview']),
            'hiring_specialist_name_exists' => isset($data['HRReview']['HiringSpecialistName'])
        ]);

        $mappedData = [
            'first_name' => $data['YourRequest']['StoreManagersName']['First'] ?? null,
            'last_name' => $data['YourRequest']['StoreManagersName']['Last'] ?? null,
            'num_of_emp_needed' => $data['YourRequest']['HiringNeeds']['NumberOfEmployeesNeeded'] ?? null,
            'desired_start_date' => $data['YourRequest']['HiringNeeds']['DesiredStartDate'] ?? null,
            'additional_notes' => $data['YourRequest']['HiringNeeds']['AdditionalNotes']['DoYouWantToAddAnyNotes'] ?? null,
            'supervisors_first_name' => $data['SupervisorsApproval']['SupervisorsName']['First'] ?? null,
            'supervisors_last_name' => $data['SupervisorsApproval']['SupervisorsName']['Last'] ?? null,
            'supervisors_accept' => $this->convertYesNoToBoolean($data['SupervisorsApproval']['AfterReviewingTheStoresNeedsDoYouAcceptThisRequest'] ?? null),
            'supervisors_notes' => $data['SupervisorsApproval']['DoYouWantToAddAnyNote'] ?? null,
            'hr_first_name' => $data['HRReview']['HiringSpecialistName']['First'] ?? null,
            'hr_last_name' => $data['HRReview']['HiringSpecialistName']['Last'] ?? null,
            'hr_num_of_hires' => $data['HRReview']['NumberOfHires'] ?? null,
            'cognito_id' => $data['Entry']['Number'] ?? null  // CORRECTED: Entry->Number instead of Id
        ];

        Log::info('Mapped data result:', ['mapped_data' => $mappedData]);
        return $mappedData;
    }

    /**
     * Create hire records from EMPInfo data.
     */
    private function createHireRecords($requestId, $data)
    {
        Log::info('Starting hire records creation for request:', ['request_id' => $requestId]);

        $empInfoArray = $data['HRReview']['EMPInfo'] ?? [];
        Log::info('EMPInfo array found:', ['count' => count($empInfoArray), 'exists' => isset($data['HRReview']['EMPInfo'])]);

        foreach ($empInfoArray as $index => $empInfo) {
            Log::info('Processing hire record:', ['index' => $index, 'emp_info' => $empInfo]);

            $hireData = $this->mapHireData($requestId, $empInfo);
            Log::info('Mapped hire data:', ['hire_data' => $hireData]);

            $hire = Hire::create($hireData);
            Log::info('Hire record created:', ['hire_id' => $hire->id]);
        }

        Log::info('Completed hire records creation');
    }

    /**
     * Map EMPInfo data to hires table structure.
     */
    private function mapHireData($requestId, $empInfo)
    {
        Log::info('Mapping hire data for request:', ['request_id' => $requestId]);

        $section = $empInfo['Section'] ?? [];
        Log::info('Section data:', ['section' => $section]);

        // Log structure checks
        Log::info('Hire data structure checks:', [
            'section_exists' => isset($empInfo['Section']),
            'employee_full_name_exists' => isset($section['EmployeeFullName']),
            'availabilities_exists' => isset($section['InformationOfTheAvailabilities']),
            'verification_uploads_exists' => isset($section['VerificationUploads'])
        ]);

        $mappedHireData = [
            'request_id' => $requestId,
            'emp_first_name' => $section['EmployeeFullName']['First'] ?? '',
            'emp_middle_name' => $section['EmployeeFullName']['Middle'] ?? '',
            'emp_last_name' => $section['EmployeeFullName']['Last'] ?? '',
            'date_of_birth' => $section['DateOfBirthDOB'] ?? null,
            'gender' => $section['Gender'] ?? '',
            'available_shifts' => $section['InformationOfTheAvailabilities']['AvailableShifts'] ?? null,
            'work_days' => $section['InformationOfTheAvailabilities']['DaysOfWork'] ?? null,
            'alta_clock_in_out_img' => $this->extractFileUrl($section['VerificationUploads']['AltimetricsClockInOutScreenshot'] ?? []),
            'paychex_profile_img' => $this->extractFileUrl($section['VerificationUploads']['PaychexProfileScreenshot'] ?? []),
            'paychex_direct_deposit_img' => $this->extractFileUrl($section['VerificationUploads']['PaychexDirectDeposit'] ?? []),
            'signed_contract_img' => $this->extractFileUrl($section['VerificationUploads']['SignedContractDocument'] ?? [])
        ];

        Log::info('Mapped hire data result:', ['mapped_hire_data' => $mappedHireData]);
        return $mappedHireData;
    }

    /**
     * Convert Yes/No strings to boolean.
     */
    private function convertYesNoToBoolean($value)
    {
        Log::info('Converting Yes/No to boolean:', ['input' => $value]);

        if (is_null($value)) {
            Log::info('Value is null, returning null');
            return null;
        }

        $result = strtolower(trim($value)) === 'yes' ? true : (strtolower(trim($value)) === 'no' ? false : null);
        Log::info('Conversion result:', ['input' => $value, 'output' => $result]);

        return $result;
    }

    /**
     * Extract file URL from file array.
     */
    private function extractFileUrl($fileArray)
    {
        Log::info('Extracting file URL:', ['file_array' => $fileArray]);

        $url = !empty($fileArray) && isset($fileArray[0]['File']) ? $fileArray[0]['File'] : '';
        Log::info('Extracted URL:', ['url' => $url]);

        return $url;
    }
}
