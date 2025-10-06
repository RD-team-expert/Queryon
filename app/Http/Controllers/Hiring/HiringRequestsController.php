<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hiring\HiringRequest;
use App\Models\Hiring\Hire;

class HiringRequestsController extends Controller
{
    /**
     * Create a new hiring request.
     */
    public function create(Request $request)
    {
        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);

            // Map data for hiring_requests table
            $hiringRequestData = $this->mapHiringRequestData($data);

            // Create the hiring request
            $hiringRequest = HiringRequest::create($hiringRequestData);

            // Create hire records if EMPInfo exists
            $this->createHireRecords($hiringRequest->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Hiring request created successfully',
                'data' => $hiringRequest->load('hires')
            ], 201);

        } catch (\Exception $e) {
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
        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);

            // Get cognito_id from the data (Entry -> Number)
            $cognitoId = $data['Entry']['Number'] ?? null;

            if (!$cognitoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required for update'
                ], 400);
            }

            // Find hiring request by cognito_id
            $hiringRequest = HiringRequest::where('cognito_id', $cognitoId)->first();

            if (!$hiringRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hiring request not found'
                ], 404);
            }

            // Map and update data
            $hiringRequestData = $this->mapHiringRequestData($data);

            $hiringRequest->update($hiringRequestData);

            // Delete existing hires and recreate them
            $deletedCount = $hiringRequest->hires()->count();
            $hiringRequest->hires()->delete();

            $this->createHireRecords($hiringRequest->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Hiring request updated successfully',
                'data' => $hiringRequest->fresh()->load('hires')
            ]);

        } catch (\Exception $e) {
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
        try {
            // Get JSON data and decode to array
            $data = $this->getJsonData($request);

            // Get cognito_id from the data (Entry -> Number)
            $cognitoId = $data['Entry']['Number'] ?? null;

            if (!$cognitoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required for deletion'
                ], 400);
            }

            // Find hiring request by cognito_id
            $hiringRequest = HiringRequest::where('cognito_id', $cognitoId)->first();

            if (!$hiringRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hiring request not found'
                ], 404);
            }

            // Delete associated hires first
            $hiresCount = $hiringRequest->hires()->count();
            $hiringRequest->hires()->delete();

            // Delete the hiring request
            $hiringRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hiring request deleted successfully'
            ]);

        } catch (\Exception $e) {
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
        $jsonData = $request->getContent();
        $decodedData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        return $decodedData;
    }

    /**
     * Map JSON data to hiring_requests table structure.
     */
    private function mapHiringRequestData($data)
    {
        $mappedData = [
            'first_name' => $data['YourRequest']['StoreManagersName']['First'] ?? null,
            'last_name' => $data['YourRequest']['StoreManagersName']['Last'] ?? null,
            'store' => $data['YourRequest']['StoreNumber']['Label'] ?? null,
            'date_of_request' => $data['YourRequest']['DateOfTheRequest'] ?? null,
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
            'cognito_id' => $data['Entry']['Number'] ?? null
        ];

        return $mappedData;
    }

    /**
     * Create hire records from EMPInfo data.
     */
    private function createHireRecords($requestId, $data)
    {
        $empInfoArray = $data['HRReview']['EMPInfo'] ?? [];

        foreach ($empInfoArray as $index => $empInfo) {
            $hireData = $this->mapHireData($requestId, $empInfo);
            $hire = Hire::create($hireData);
        }
    }

    /**
     * Map EMPInfo data to hires table structure.
     */
    private function mapHireData($requestId, $empInfo)
    {
        $section = $empInfo['Section'] ?? [];

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

        return $mappedHireData;
    }

    /**
     * Convert Yes/No strings to boolean.
     */
    private function convertYesNoToBoolean($value)
    {
        if (is_null($value)) {
            return null;
        }

        return strtolower(trim($value)) === 'yes' ? true : (strtolower(trim($value)) === 'no' ? false : null);
    }

    /**
     * Extract file URL from file array.
     */
    private function extractFileUrl($fileArray)
    {
        return !empty($fileArray) && isset($fileArray[0]['File']) ? $fileArray[0]['File'] : '';
    }
}
