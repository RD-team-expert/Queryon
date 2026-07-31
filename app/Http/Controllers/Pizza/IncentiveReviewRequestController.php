<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\IncentiveReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;

class IncentiveReviewRequestController extends Controller
{
    /**
     * Create a new incentive review request record from Cognito webhook.
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

            if (IncentiveReviewRequest::where('cognito_id', $mappedData['cognito_id'])->exists()) {
                return $this->errorResponse('Incentive review request already exists', 409);
            }

            DB::beginTransaction();

            $incentiveReviewRequest = IncentiveReviewRequest::create($mappedData);

            DB::commit();


            return $this->successResponse('Incentive review request created successfully', $incentiveReviewRequest, 201);

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create incentive review request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing incentive review request record from Cognito webhook.
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

            $incentiveReviewRequest = IncentiveReviewRequest::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $mappedData['entry_number'])
                ->first();

            if (!$incentiveReviewRequest) {
                // If record doesn't exist, create it instead (upsert behavior)
                $incentiveReviewRequest = IncentiveReviewRequest::create($mappedData);

                DB::commit();

                return $this->successResponse('Incentive review request created successfully (upsert)', $incentiveReviewRequest, 201);
            }

            $incentiveReviewRequest->update($mappedData);

            DB::commit();



            return $this->successResponse('Incentive review request updated successfully', $incentiveReviewRequest->fresh());

        } catch (Exception $e) {
            DB::rollBack();


            return $this->errorResponse('Failed to update incentive review request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an incentive review request record.
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

            $incentiveReviewRequest = IncentiveReviewRequest::where('cognito_id', $cognitoId)
                ->orWhere('entry_number', $entryNumber)
                ->first();

            if (!$incentiveReviewRequest) {
                return $this->errorResponse('Incentive review request not found', 404);
            }

            $recordInfo = [
                'id' => $incentiveReviewRequest->id,
                'cognito_id' => $incentiveReviewRequest->cognito_id,
                'store' => $incentiveReviewRequest->store_label,
            ];

            $incentiveReviewRequest->delete();

            DB::commit();


            return $this->successResponse('Incentive review request deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete incentive review request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export incentive review requests to CSV.
     */
    public function exportCsv(Request $request): Response
    {

        try {
            $query = IncentiveReviewRequest::query();

            // Apply filters
            $query->dateRange(
                $request->query('start_date'),
                $request->query('end_date')
            );

            if ($storeLabel = $request->query('store_label')) {
                $query->forStore($storeLabel);
            }

            if ($approval = $request->query('manager_approval')) {
                $query->withApproval($approval);
            }

            $query->orderBy('created_at', 'desc');

            $data = $query->get();
            $columns = IncentiveReviewRequest::getCsvColumns();

            // Create CSV
            $handle = fopen('php://memory', 'r+');

            // Write header row
            fputcsv($handle, $columns);

            // Write data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $col) {
                    $value = $item->{$col};

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

            // Generate filename
            $filename = 'incentive_review_requests_export_' . now()->format('Y-m-d_His') . '.csv';


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
        // Extract sections
        $storeManagerSection = data_get($data, 'StoreManagerSection', []);
        $managementSection = data_get($data, 'ManagementSection', []);
        $entry = data_get($data, 'Entry', []);

        // Build cognito_id from Form.Id and Entry.Number, falling back to the top-level Id
        $formId = data_get($data, 'Form.Id');
        $entryNumber = data_get($entry, 'Number');
        $cognitoId = data_get($data, 'Id') ?? ($formId && $entryNumber ? "{$formId}-{$entryNumber}" : null);

        return [
            // Cognito Entry Identity
            'cognito_id' => $cognitoId,
            'entry_number' => $entryNumber,

            // Store Manager Section
            'store_manager_first_name' => data_get($storeManagerSection, 'StoreManagerName.First'),
            'store_manager_last_name' => data_get($storeManagerSection, 'StoreManagerName.Last'),
            'todays_date' => data_get($storeManagerSection, 'TadaysDate'),
            'shift' => data_get($storeManagerSection, 'SHIFT'),
            'store_label' => data_get($storeManagerSection, 'YourStoreIs.Label'),
            'issue_details' => data_get($storeManagerSection, 'PleaseProvideMoreDetailsAboutTheIssueIncompleteOrIncorrectInformationMayResultInTheRejectionOfYourRequest'),
            'review_aspects' => data_get($storeManagerSection, 'WhatAspectOfTheIncentiveReviewAreYouRequestingToAddress', []),
            'week_start_date' => data_get($storeManagerSection, 'TheStartDateOfTheWeekTuesday'),
            'week_end_date' => data_get($storeManagerSection, 'TheEndDateOfTheWeekMonday'),

            // Management Section
            'manager_first_name' => data_get($managementSection, 'ManagerName.First'),
            'manager_last_name' => data_get($managementSection, 'ManagerName.Last'),
            'manager_approval' => data_get($managementSection, 'ManagerAppraval'),
            // Only one of FinalDecision/RejectionReason is ever populated, depending on ManagerAppraval
            'decision_notes' => data_get($managementSection, 'FinalDecision') ?? data_get($managementSection, 'RejectionReason'),
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
