<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pizza\DepositDeliveryData;
use Illuminate\Support\Facades\Log;

class DepositDeliveryController extends Controller
{
    public function create(Request $request)
    {
        Log::info('Deposit Delivery Data creation request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        $json = json_decode($request->getContent(), true);

        if (!$json) {
            Log::error('Invalid JSON payload received for Deposit Delivery Data');
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON payload'
            ], 400);
        }

        Log::debug('Processing Deposit Delivery Data', [
            'franchisee' => data_get($json, 'HookFranchiseeNum.Label'),
            'date' => data_get($json, 'HookWorkDaysDate')
        ]);

        // 🔹 Basic Info
        $franchiseeNum   = data_get($json, 'HookFranchiseeNum.Label');
        $workDate        = data_get($json, 'HookWorkDaysDate');

        // 🔹 Deposit Process Info
        $depositAmount   = data_get($json, 'HookDepositProcess.HookCashDeposit', null);
        $tipsAmount      = data_get($json, 'HookDepositProcess.HookHowMuchTips', null);
        $overShort       = data_get($json, 'HookDepositProcess.HookOverShort', null);
        $altimetricWaste = data_get($json, 'HookDepositProcess.HookAltimetricWaste', null);
        $workingHours    = data_get($json, 'HookDepositProcess.HookEmployeesWorkingHours', null);

        // 🔹 DoorDash Data
        $dd = data_get($json, 'Hook_Delivery.Hook_DoorDash', []);
        $ddMostLoved               = data_get($dd, 'Hook_MostLovedRestaurant');
        $ddOptimizationScore       = data_get($dd, 'Hook_OptimizationScore');
        $ddAverageRating           = data_get($dd, 'Hook_RatingsAverageRating');
        $ddSalesLost               = data_get($dd, 'Hook_CancellationsSalesLost2');
        $ddErrorCharges            = data_get($dd, 'Hook_MissingOrIncorrectErrorCharges');
        $ddWaitMs                  = data_get($dd, 'Hook_AvoidableWaitMSec2');
        $ddDasherWaitMs            = data_get($dd, 'Hook_TotalDasherWaitMSec');
        $ddTopIncorrectItem        = data_get($dd, 'Hook_1TopMissingOrIncorrectItem');
        $ddDowntime                = data_get($dd, 'Hook_DowntimeHMM');
        $ddReviewsResponded        = data_get($dd, 'Hook_ReviewsResponded');
        $ddNAOTRating              = data_get($dd, 'Hook_NAOT_RatingsAverageRating');
        $ddNAOTSalesLost           = data_get($dd, 'Hook_NAOT_CancellationsSalesLost');
        $ddNAOTErrorCharges        = data_get($dd, 'Hook_NAOT_Missing or Incorrect Error Charges');
        $ddNAOTWait                = data_get($dd, 'Hook_NAOT_AvoidableWaitMSec');
        $ddNAOTDasherWait          = data_get($dd, 'Hook_NAOT_TotalDasherWaitMSec');
        $ddNAOTDowntime            = data_get($dd, 'Hook_NAOT_DowntimeHMM');

        // 🔹 UberEats Data
        $ue = data_get($json, 'Hook_Delivery.Hook_UberEats', []);
        $ueReviewScore             = data_get($ue, 'Hook_CustomerReviewsOverview');
        $ueRefundCost              = data_get($ue, 'Hook_CostOfRefunds');
        $ueUnfulfilledRate         = data_get($ue, 'Hook_UnfulfilledOrderRate');
        $ueUnavailableTime         = data_get($ue, 'Hook_TimeUnavailableDuringOpenHoursHhmm');
        $ueTopInaccurateItem       = data_get($ue, 'Hook_TopInaccurateItem');
        $ueReviewsResponded        = data_get($ue, 'Hook_ReviewsResponded');
        $ueNAOTReviewScore         = data_get($ue, 'Hook_NAOT_CustomerReviewsOverview');
        $ueNAOTRefundCost          = data_get($ue, 'Hook_NAOT_CostOfRefunds');
        $ueNAOTUnfulfilledRate     = data_get($ue, 'Hook_NAOT_UnfulfilledOrderRate');
        $ueNAOTUnavailableTime     = data_get($ue, 'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm');

        // 🔹 GrubHub Data
        $gh = data_get($json, 'Hook_Delivery.Hook_GrubHub', []);
        $ghRating                  = data_get($gh, 'Hook_Rating');
        $ghFoodWasGood             = data_get($gh, 'Hook_FoodWasGood');
        $ghOnTimeDelivery          = data_get($gh, 'Hook_DeliveryWasOnTime');
        $ghOrderAccurate           = data_get($gh, 'Hook_OrderWasAccurate');
        $ghNAOTRating              = data_get($gh, 'Hook_NAOT_Rating');
        $ghNAOTFoodGood            = data_get($gh, 'Hook_NAOT_FoodWasGood');
        $ghNAOTOnTime              = data_get($gh, 'Hook_NAOT_DeliveryWasOnTime');
        $ghNAOTAccurate            = data_get($gh, 'Hook_NAOT_OrderWasAccurate');
        $ghIncRating               = data_get($gh, 'Hook_Rating_IncrementBy');
        $ghIncFoodGood             = data_get($gh, 'Hook_FoodWasGood_IncrementBy');
        $ghIncOnTime               = data_get($gh, 'Hook_DeliveryWasOnTime_IncrementBy');
        $ghIncAccurate             = data_get($gh, 'Hook_OrderWasAccurate_IncrementBy');

        // 🔹 Entry Info
        $entryNumber = data_get($json, 'Entry.Number');

        // 🔹 Create Record
        try {
            $record = DepositDeliveryData::create([
                'HookFranchiseeNum' => $franchiseeNum,
                'HookWorkDaysDate' => $workDate,
                'HookDepositAmount' => $depositAmount,
                'HookHowMuchTips' => $tipsAmount,
                'HookOverShort' => $overShort,
                'HookAltimetricWaste' => $altimetricWaste,
                'HookEmployeesWorkingHours' => $workingHours,

                'Hook_MostLovedRestaurant' => $ddMostLoved,
                'Hook_OptimizationScore' => $ddOptimizationScore,
                'Hook_RatingsAverageRating' => $ddAverageRating,
                'Hook_CancellationsSalesLost2' => $ddSalesLost,
                'Hook_MissingOrIncorrectErrorCharges' => $ddErrorCharges,
                'Hook_AvoidableWaitMSec2' => $ddWaitMs,
                'Hook_TotalDasherWaitMSec' => $ddDasherWaitMs,
                'Hook_1TopMissingOrIncorrectItem' => $ddTopIncorrectItem,
                'Hook_DowntimeHMM' => $ddDowntime,
                'Hook_ReviewsResponded' => $ddReviewsResponded,
                'Hook_NAOT_RatingsAverageRating' => $ddNAOTRating,
                'Hook_NAOT_CancellationsSalesLost' => $ddNAOTSalesLost,
                'Hook_NAOT_MissingOrIncorrectErrorCharges' => $ddNAOTErrorCharges,
                'Hook_NAOT_AvoidableWaitMSec' => $ddNAOTWait,
                'Hook_NAOT_TotalDasherWaitMSec' => $ddNAOTDasherWait,
                'Hook_NAOT_DowntimeHMM' => $ddNAOTDowntime,

                'Hook_CustomerReviewsOverview' => $ueReviewScore,
                'Hook_CostOfRefunds' => $ueRefundCost,
                'Hook_UnfulfilledOrderRate' => $ueUnfulfilledRate,
                'Hook_TimeUnavailableDuringOpenHoursHhmm' => $ueUnavailableTime,
                'Hook_TopInaccurateItem' => $ueTopInaccurateItem,
                'Hook_ReviewsResponded_2' => $ueReviewsResponded,
                'Hook_NAOT_CustomerReviewsOverview' => $ueNAOTReviewScore,
                'Hook_NAOT_CostOfRefunds' => $ueNAOTRefundCost,
                'Hook_NAOT_UnfulfilledOrderRate' => $ueNAOTUnfulfilledRate,
                'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm' => $ueNAOTUnavailableTime,

                'Hook_Rating' => $ghRating,
                'Hook_FoodWasGood' => $ghFoodWasGood,
                'Hook_DeliveryWasOnTime' => $ghOnTimeDelivery,
                'Hook_OrderWasAccurate' => $ghOrderAccurate,
                'Hook_NAOT_Rating' => $ghNAOTRating,
                'Hook_NAOT_FoodWasGood' => $ghNAOTFoodGood,
                'Hook_NAOT_DeliveryWasOnTime' => $ghNAOTOnTime,
                'Hook_NAOT_OrderWasAccurate' => $ghNAOTAccurate,
                'Hook_Rating_IncrementBy' => $ghIncRating,
                'Hook_FoodWasGood_IncrementBy' => $ghIncFoodGood,
                'Hook_DeliveryWasOnTime_IncrementBy' => $ghIncOnTime,
                'Hook_OrderWasAccurate_IncrementBy' => $ghIncAccurate,

                'Entry_Number' => $entryNumber
            ]);

            Log::info('Deposit Delivery Data stored successfully', [
                'id' => $record->id,
                'franchisee' => $franchiseeNum,
                'date' => $workDate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit Delivery Data stored successfully.',
                'data'    => $record
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Deposit Delivery Data', [
                'franchisee' => $franchiseeNum,
                'date' => $workDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        Log::info('Deposit Delivery Data update request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        $json = json_decode($request->getContent(), true);

        if (!$json) {
            Log::error('Invalid JSON payload received for Deposit Delivery Data update');
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON payload'
            ], 400);
        }

        // Get Entry Number for lookup
        $entryNumber = data_get($json, 'Entry.Number');

        if (is_null($entryNumber)) {
            Log::error('Entry Number not provided for Deposit Delivery Data update');
            return response()->json([
                'success' => false,
                'message' => 'Entry Number not provided'
            ], 400);
        }

        // Find the record by Entry_Number
        $record = DepositDeliveryData::where('Entry_Number', $entryNumber)->first();

        if (!$record) {
            Log::error('Deposit Delivery Data record not found for update', [
                'entry_number' => $entryNumber
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Deposit Delivery Data record not found'
            ], 404);
        }

        Log::debug('Processing Deposit Delivery Data update', [
            'entry_number' => $entryNumber,
            'franchisee' => data_get($json, 'HookFranchiseeNum.Label'),
            'date' => data_get($json, 'HookWorkDaysDate')
        ]);

        // Extract data from JSON
        // 🔹 Basic Info
        $franchiseeNum   = data_get($json, 'HookFranchiseeNum.Label');
        $workDate        = data_get($json, 'HookWorkDaysDate');

        // 🔹 Deposit Process Info
        $depositAmount   = data_get($json, 'HookDepositProcess.HookCashDeposit', null);
        $tipsAmount      = data_get($json, 'HookDepositProcess.HookHowMuchTips', null);
        $overShort       = data_get($json, 'HookDepositProcess.HookOverShort', null);
        $altimetricWaste = data_get($json, 'HookDepositProcess.HookAltimetricWaste', null);
        $workingHours    = data_get($json, 'HookDepositProcess.HookEmployeesWorkingHours', null);

        // 🔹 DoorDash Data
        $dd = data_get($json, 'Hook_Delivery.Hook_DoorDash', []);
        $ddMostLoved               = data_get($dd, 'Hook_MostLovedRestaurant');
        $ddOptimizationScore       = data_get($dd, 'Hook_OptimizationScore');
        $ddAverageRating           = data_get($dd, 'Hook_RatingsAverageRating');
        $ddSalesLost               = data_get($dd, 'Hook_CancellationsSalesLost2');
        $ddErrorCharges            = data_get($dd, 'Hook_MissingOrIncorrectErrorCharges');
        $ddWaitMs                  = data_get($dd, 'Hook_AvoidableWaitMSec2');
        $ddDasherWaitMs            = data_get($dd, 'Hook_TotalDasherWaitMSec');
        $ddTopIncorrectItem        = data_get($dd, 'Hook_1TopMissingOrIncorrectItem');
        $ddDowntime                = data_get($dd, 'Hook_DowntimeHMM');
        $ddReviewsResponded        = data_get($dd, 'Hook_ReviewsResponded');
        $ddNAOTRating              = data_get($dd, 'Hook_NAOT_RatingsAverageRating');
        $ddNAOTSalesLost           = data_get($dd, 'Hook_NAOT_CancellationsSalesLost');
        $ddNAOTErrorCharges        = data_get($dd, 'Hook_NAOT_Missing or Incorrect Error Charges');
        $ddNAOTWait                = data_get($dd, 'Hook_NAOT_AvoidableWaitMSec');
        $ddNAOTDasherWait          = data_get($dd, 'Hook_NAOT_TotalDasherWaitMSec');
        $ddNAOTDowntime            = data_get($dd, 'Hook_NAOT_DowntimeHMM');

        // 🔹 UberEats Data
        $ue = data_get($json, 'Hook_Delivery.Hook_UberEats', []);
        $ueReviewScore             = data_get($ue, 'Hook_CustomerReviewsOverview');
        $ueRefundCost              = data_get($ue, 'Hook_CostOfRefunds');
        $ueUnfulfilledRate         = data_get($ue, 'Hook_UnfulfilledOrderRate');
        $ueUnavailableTime         = data_get($ue, 'Hook_TimeUnavailableDuringOpenHoursHhmm');
        $ueTopInaccurateItem       = data_get($ue, 'Hook_TopInaccurateItem');
        $ueReviewsResponded        = data_get($ue, 'Hook_ReviewsResponded');
        $ueNAOTReviewScore         = data_get($ue, 'Hook_NAOT_CustomerReviewsOverview');
        $ueNAOTRefundCost          = data_get($ue, 'Hook_NAOT_CostOfRefunds');
        $ueNAOTUnfulfilledRate     = data_get($ue, 'Hook_NAOT_UnfulfilledOrderRate');
        $ueNAOTUnavailableTime     = data_get($ue, 'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm');

        // 🔹 GrubHub Data
        $gh = data_get($json, 'Hook_Delivery.Hook_GrubHub', []);
        $ghRating                  = data_get($gh, 'Hook_Rating');
        $ghFoodWasGood             = data_get($gh, 'Hook_FoodWasGood');
        $ghOnTimeDelivery          = data_get($gh, 'Hook_DeliveryWasOnTime');
        $ghOrderAccurate           = data_get($gh, 'Hook_OrderWasAccurate');
        $ghNAOTRating              = data_get($gh, 'Hook_NAOT_Rating');
        $ghNAOTFoodGood            = data_get($gh, 'Hook_NAOT_FoodWasGood');
        $ghNAOTOnTime              = data_get($gh, 'Hook_NAOT_DeliveryWasOnTime');
        $ghNAOTAccurate            = data_get($gh, 'Hook_NAOT_OrderWasAccurate');
        $ghIncRating               = data_get($gh, 'Hook_Rating_IncrementBy');
        $ghIncFoodGood             = data_get($gh, 'Hook_FoodWasGood_IncrementBy');
        $ghIncOnTime               = data_get($gh, 'Hook_DeliveryWasOnTime_IncrementBy');
        $ghIncAccurate             = data_get($gh, 'Hook_OrderWasAccurate_IncrementBy');

        // Prepare update data
        $updateData = [
            'HookFranchiseeNum' => $franchiseeNum,
            'HookWorkDaysDate' => $workDate,
            'HookDepositAmount' => $depositAmount,
            'HookHowMuchTips' => $tipsAmount,
            'HookOverShort' => $overShort,
            'HookAltimetricWaste' => $altimetricWaste,
            'HookEmployeesWorkingHours' => $workingHours,

            'Hook_MostLovedRestaurant' => $ddMostLoved,
            'Hook_OptimizationScore' => $ddOptimizationScore,
            'Hook_RatingsAverageRating' => $ddAverageRating,
            'Hook_CancellationsSalesLost2' => $ddSalesLost,
            'Hook_MissingOrIncorrectErrorCharges' => $ddErrorCharges,
            'Hook_AvoidableWaitMSec2' => $ddWaitMs,
            'Hook_TotalDasherWaitMSec' => $ddDasherWaitMs,
            'Hook_1TopMissingOrIncorrectItem' => $ddTopIncorrectItem,
            'Hook_DowntimeHMM' => $ddDowntime,
            'Hook_ReviewsResponded' => $ddReviewsResponded,
            'Hook_NAOT_RatingsAverageRating' => $ddNAOTRating,
            'Hook_NAOT_CancellationsSalesLost' => $ddNAOTSalesLost,
            'Hook_NAOT_MissingOrIncorrectErrorCharges' => $ddNAOTErrorCharges,
            'Hook_NAOT_AvoidableWaitMSec' => $ddNAOTWait,
            'Hook_NAOT_TotalDasherWaitMSec' => $ddNAOTDasherWait,
            'Hook_NAOT_DowntimeHMM' => $ddNAOTDowntime,
            'Hook_CustomerReviewsOverview' => $ueReviewScore,
            'Hook_CostOfRefunds' => $ueRefundCost,
            'Hook_UnfulfilledOrderRate' => $ueUnfulfilledRate,
            'Hook_TimeUnavailableDuringOpenHoursHhmm' => $ueUnavailableTime,
            'Hook_TopInaccurateItem' => $ueTopInaccurateItem,
            'Hook_ReviewsResponded_2' => $ueReviewsResponded,
            'Hook_NAOT_CustomerReviewsOverview' => $ueNAOTReviewScore,
            'Hook_NAOT_CostOfRefunds' => $ueNAOTRefundCost,
            'Hook_NAOT_UnfulfilledOrderRate' => $ueNAOTUnfulfilledRate,
            'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm' => $ueNAOTUnavailableTime,
            'Hook_Rating' => $ghRating,
            'Hook_FoodWasGood' => $ghFoodWasGood,
            'Hook_DeliveryWasOnTime' => $ghOnTimeDelivery,
            'Hook_OrderWasAccurate' => $ghOrderAccurate,
            'Hook_NAOT_Rating' => $ghNAOTRating,
            'Hook_NAOT_FoodWasGood' => $ghNAOTFoodGood,
            'Hook_NAOT_DeliveryWasOnTime' => $ghNAOTOnTime,
            'Hook_NAOT_OrderWasAccurate' => $ghNAOTAccurate,
            'Hook_Rating_IncrementBy' => $ghIncRating,
            'Hook_FoodWasGood_IncrementBy' => $ghIncFoodGood,
            'Hook_DeliveryWasOnTime_IncrementBy' => $ghIncOnTime,
            'Hook_OrderWasAccurate_IncrementBy' => $ghIncAccurate,
            // Don't update Entry_Number as it's used for lookup
        ];

        try {
            // Update the record
            $record->update($updateData);

            Log::info('Deposit Delivery Data updated successfully', [
                'id' => $record->id,
                'entry_number' => $entryNumber,
                'franchisee' => $franchiseeNum,
                'date' => $workDate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit Delivery Data updated successfully.',
                'data'    => $record
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update Deposit Delivery Data', [
                'entry_number' => $entryNumber,
                'franchisee' => $franchiseeNum,
                'date' => $workDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        Log::info('Deposit Delivery Data delete request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        $json = json_decode($request->getContent(), true);

        if (!$json) {
            Log::error('Invalid JSON payload received for Deposit Delivery Data deletion');
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON payload'
            ], 400);
        }

        // Get Entry Number for lookup
        $entryNumber = data_get($json, 'Entry.Number');

        if (is_null($entryNumber)) {
            Log::error('Entry Number not provided for Deposit Delivery Data deletion');
            return response()->json([
                'success' => false,
                'message' => 'Entry Number not provided'
            ], 400);
        }

        // Find the record by Entry_Number
        $record = DepositDeliveryData::where('Entry_Number', $entryNumber)->first();

        if (!$record) {
            Log::error('Deposit Delivery Data record not found for deletion', [
                'entry_number' => $entryNumber
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Deposit Delivery Data record not found'
            ], 404);
        }

        try {
            // Store record info before deletion for logging
            $recordInfo = [
                'id' => $record->id,
                'entry_number' => $record->Entry_Number,
                'franchisee' => $record->HookFranchiseeNum,
                'date' => $record->HookWorkDaysDate
            ];

            // Delete the record
            $record->delete();

            Log::info('Deposit Delivery Data deleted successfully', $recordInfo);

            return response()->json([
                'success' => true,
                'message' => 'Deposit Delivery Data deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete Deposit Delivery Data', [
                'entry_number' => $entryNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * Export DepositDeliveryData data as a CSV for Excel.
     */
    public function exportToExcel(Request $request, $startDateParam = null, $endDateParam = null, $franchiseeNumParam = null)
    {
        Log::info('Deposit Delivery Data Excel export requested', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        // Get parameters from either URL segments or query parameters
        $startDate = $startDateParam ?? $request->query('start_date');
        $endDate = $endDateParam ?? $request->query('end_date');
        $franchiseeNum = $franchiseeNumParam ?? $request->query('franchisee_num');

        Log::debug('Export parameters', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'franchisee_num' => $franchiseeNum,
            'raw_query' => $request->getQueryString()
        ]);

        // Start with a base query
        $query = DepositDeliveryData::query();

        // Apply date range filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('HookWorkDaysDate', [$startDate, $endDate]);
            Log::debug('Filtering by date range', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
        } else if ($startDate) {
            $query->where('HookWorkDaysDate', '>=', $startDate);
            Log::debug('Filtering by start date only', ['start_date' => $startDate]);
        } else if ($endDate) {
            $query->where('HookWorkDaysDate', '<=', $endDate);
            Log::debug('Filtering by end date only', ['end_date' => $endDate]);
        }

        // Apply franchisee number filter if provided
        if ($franchiseeNum) {
            // Convert to string to ensure proper comparison if franchiseeNum is numeric
            $query->where('HookFranchiseeNum', (string)$franchiseeNum);
            Log::debug('Filtering by franchisee number', ['franchisee_num' => $franchiseeNum]);
        }

        // Execute the query
        $data = $query->get();

        $recordCount = $data->count();
        Log::info('Deposit Delivery Data retrieved successfully', [
            'record_count' => $recordCount,
            'date_range' => $startDate && $endDate ? "$startDate to $endDate" : 'all dates',
            'franchisee_num' => $franchiseeNum ?: 'all franchisees'
        ]);

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'HookFranchiseeNum',
            'HookWorkDaysDate',
            'HookDepositAmount',
            'HookHowMuchTips',
            'HookOverShort',
            'HookAltimetricWaste',
            'HookEmployeesWorkingHours',
            'Hook_MostLovedRestaurant',
            'Hook_OptimizationScore',
            'Hook_RatingsAverageRating',
            'Hook_CancellationsSalesLost2',
            'Hook_MissingOrIncorrectErrorCharges',
            'Hook_AvoidableWaitMSec2',
            'Hook_TotalDasherWaitMSec',
            'Hook_1TopMissingOrIncorrectItem',
            'Hook_DowntimeHMM',
            'Hook_ReviewsResponded',
            'Hook_NAOT_RatingsAverageRating',
            'Hook_NAOT_CancellationsSalesLost',
            'Hook_NAOT_MissingOrIncorrectErrorCharges',
            'Hook_NAOT_AvoidableWaitMSec',
            'Hook_NAOT_TotalDasherWaitMSec',
            'Hook_NAOT_DowntimeHMM',
            'Hook_CustomerReviewsOverview',
            'Hook_CostOfRefunds',
            'Hook_UnfulfilledOrderRate',
            'Hook_TimeUnavailableDuringOpenHoursHhmm',
            'Hook_TopInaccurateItem',
            'Hook_ReviewsResponded_2',
            'Hook_NAOT_CustomerReviewsOverview',
            'Hook_NAOT_CostOfRefunds',
            'Hook_NAOT_UnfulfilledOrderRate',
            'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm',

            'Hook_Rating',
            'Hook_FoodWasGood',
            'Hook_DeliveryWasOnTime',
            'Hook_OrderWasAccurate',
            'Hook_NAOT_Rating',
            'Hook_NAOT_FoodWasGood',
            'Hook_NAOT_DeliveryWasOnTime',
            'Hook_NAOT_OrderWasAccurate',
            'Entry_Number',
            'created_at',
            'updated_at'
        ];

        // Open a memory stream
        $handle = fopen('php://memory', 'r+');

        // Write CSV header row
        fputcsv($handle, $columns);

        // Loop through each record and write data to CSV
        foreach ($data as $item) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = $item->{$col};
            }
            fputcsv($handle, $row);
        }

        // Rewind the memory stream and get its contents
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Generate filename with filter information
        $filename = 'deposit_delivery_data';
        if ($startDate || $endDate || $franchiseeNum) {
            $filename .= '_filtered';
        }
        $filename .= '.csv';

        Log::info('Deposit Delivery Data Excel export completed', [
            'filename' => $filename,
            'record_count' => $recordCount
        ]);

        // Return the CSV as a response, with headers for Excel compatibility
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    /**
     * Return all DepositDeliveryData data as JSON.
     */
    public function getData(Request $request, $startDateParam = null, $endDateParam = null, $franchiseeNumParam = null)
    {
        Log::info('Deposit Delivery Data JSON requested', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        // Get parameters from either URL segments or query parameters
        $startDate = $startDateParam ?? $request->query('start_date');
        $endDate = $endDateParam ?? $request->query('end_date');
        $franchiseeNum = $franchiseeNumParam ?? $request->query('franchisee_num');

        Log::debug('JSON request parameters', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'franchisee_num' => $franchiseeNum,
            'raw_query' => $request->getQueryString()
        ]);

        // Start with a base query
        $query = DepositDeliveryData::query();

        // Apply date range filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('HookWorkDaysDate', [$startDate, $endDate]);
        } else if ($startDate) {
            $query->where('HookWorkDaysDate', '>=', $startDate);
        } else if ($endDate) {
            $query->where('HookWorkDaysDate', '<=', $endDate);
        }

        // Apply franchisee number filter if provided
        if ($franchiseeNum) {
            $query->where('HookFranchiseeNum', $franchiseeNum);
        }

        try {
            // Execute the query
            $data = $query->get();

            $recordCount = $data->count();
            Log::info('Deposit Delivery Data JSON retrieved successfully', [
                'record_count' => $recordCount,
                'date_range' => $startDate && $endDate ? "$startDate to $endDate" : 'all dates',
                'franchisee_num' => $franchiseeNum ?: 'all franchisees'
            ]);

            return response()->json([
                'success' => true,
                'record_count' => $recordCount,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving Deposit Delivery Data JSON', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export all DepositDeliveryData data as a downloadable CSV.
     */
    public function export(Request $request, $startDateParam = null, $endDateParam = null, $franchiseeNumParam = null)
    {
        try {
            Log::info('Deposit Delivery Data CSV export requested', [
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Get parameters from either URL segments or query parameters
            $startDate = $startDateParam ?? $request->query('start_date');
            $endDate = $endDateParam ?? $request->query('end_date');

            // Handle franchisee numbers as a comma-separated list
            $franchiseeNums = [];

            // First check if it was passed as a route parameter
            if (!empty($franchiseeNumParam)) {
                $franchiseeNums = array_map('trim', explode(',', $franchiseeNumParam));
            } else {
                // Try franchisee_num parameter from query
                $franchiseeNumString = $request->query('franchisee_num');
                if (!empty($franchiseeNumString)) {
                    // Check if it's a comma-separated string
                    if (strpos($franchiseeNumString, ',') !== false) {
                        $franchiseeNums = array_map('trim', explode(',', $franchiseeNumString));
                    } else {
                        $franchiseeNums = [$franchiseeNumString];
                    }
                }
            }

            // Filter out empty values
            $franchiseeNums = array_filter($franchiseeNums, function($value) {
                return !empty($value) && $value !== 'null' && $value !== 'undefined';
            });

            // For backward compatibility with the filename generation
            $franchiseeNum = !empty($franchiseeNums) ? implode(',', $franchiseeNums) : null;

            Log::debug('CSV Export parameters', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'franchisee_nums' => $franchiseeNums,
                'raw_query' => $request->getQueryString(),
                'route_params' => [$startDateParam, $endDateParam, $franchiseeNumParam]
            ]);

            // Start with a base query
            $query = DepositDeliveryData::query();

            // Apply date range filter if provided
            if ($startDate && $endDate) {
                $query->whereBetween('HookWorkDaysDate', [$startDate, $endDate]);
                Log::debug('Filtering by date range', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
            } else if ($startDate) {
                $query->where('HookWorkDaysDate', '>=', $startDate);
                Log::debug('Filtering by start date only', ['start_date' => $startDate]);
            } else if ($endDate) {
                $query->where('HookWorkDaysDate', '<=', $endDate);
                Log::debug('Filtering by end date only', ['end_date' => $endDate]);
            }

            // Apply franchisee number filter if provided
            if (!empty($franchiseeNums)) {
                // Convert all values to strings to ensure proper comparison
                $franchiseeNums = array_map('strval', $franchiseeNums);
                $query->whereIn('HookFranchiseeNum', $franchiseeNums);
                Log::debug('Filtering by franchisee numbers', ['franchisee_nums' => $franchiseeNums]);
            }

            // Execute the query
            $data = $query->get();

            $recordCount = $data->count();
            Log::info('Deposit Delivery Data CSV retrieved successfully', [
                'record_count' => $recordCount,
                'date_range' => $startDate && $endDate ? "$startDate to $endDate" : 'all dates',
                'franchisee_nums' => !empty($franchiseeNums) ? implode(', ', $franchiseeNums) : 'all franchisees'
            ]);

            // Define the columns to export with custom names
            $columns = [
                'id',
                'HookFranchiseeNum' =>'FranchiseeNum',
                'HookWorkDaysDate' =>'WorkDaysDate',
                'HookDepositAmount' =>'DepositAmount',
                'HookHowMuchTips' =>'Tips',
                'HookOverShort' =>'OverShort',
                'HookAltimetricWaste' =>'AltimetricWaste',
                'HookEmployeesWorkingHours' =>'EmployeesWorkingHours',
                'Hook_MostLovedRestaurant' =>'DD_MostLovedRestaurant',
                'Hook_OptimizationScore' =>'DD_OptimizationScore',
                'Hook_RatingsAverageRating' =>'DD_AverageRating',
                'Hook_CancellationsSalesLost2' =>'DD_CancellationsSalesLost',
                'Hook_MissingOrIncorrectErrorCharges' =>'DD_MissingOrIncorrectErrorCharges',
                'Hook_AvoidableWaitMSec2' =>'DD_AvoidableWaitMSec',
                'Hook_TotalDasherWaitMSec' =>'DD_TotalDasherWaitMSec',
                'Hook_1TopMissingOrIncorrectItem' =>'DD_TopMissingOrIncorrectItem',
                'Hook_DowntimeHMM' =>'DD_DowntimeHMM',
                'Hook_ReviewsResponded' =>'DD_ReviewsResponded',
                'Hook_NAOT_RatingsAverageRating' =>'NAOT_DD_RatingsAverageRating',
                'Hook_NAOT_CancellationsSalesLost' =>'NAOT_DD_CancellationsSalesLost',
                'Hook_NAOT_MissingOrIncorrectErrorCharges' =>'NAOT_DD_MissingOrIncorrectErrorCharges',
                'Hook_NAOT_AvoidableWaitMSec' =>'NAOT_DD_AvoidableWaitMSec',
                'Hook_NAOT_TotalDasherWaitMSec' =>'NAOT_DD_TotalDasherWaitMSec',
                'Hook_NAOT_DowntimeHMM' =>'NAOT_DD_DowntimeHMM',
                'Hook_CustomerReviewsOverview' =>'UE_CustomerReviewsOverview',
                'Hook_CostOfRefunds' =>'UE_CostOfRefunds',
                'Hook_UnfulfilledOrderRate' =>'UE_UnfulfilledOrderRate',
                'Hook_TimeUnavailableDuringOpenHoursHhmm' =>'UE_TimeUnavailableDuringOpenHoursHhmm',
                'Hook_TopInaccurateItem' =>'UE_TopInaccurateItem',
                'Hook_ReviewsResponded_2' =>'UE_ReviewsResponded',
                'Hook_NAOT_CustomerReviewsOverview' =>'UE_NAOT_CustomerReviewsOverview',
                'Hook_NAOT_CostOfRefunds' =>'UE_NAOT_CostOfRefunds',
                'Hook_NAOT_UnfulfilledOrderRate' =>'UE_NAOT_UnfulfilledOrderRate',
                'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm' =>'UE_NAOT_TimeUnavailableDuringOpenHoursHhmm',
                'Hook_Rating' =>'GH_Rating',
                'Hook_FoodWasGood' =>'GH_FoodWasGood',
                'Hook_DeliveryWasOnTime' =>'GH_DeliveryWasOnTime',
                'Hook_OrderWasAccurate' =>'GH_OrderWasAccurate',
                'Hook_NAOT_Rating' =>'GH_NAOT_Rating',
                'Hook_NAOT_FoodWasGood' =>'GH_NAOT_FoodWasGood',
                'Hook_NAOT_DeliveryWasOnTime' =>'GH_NAOT_DeliveryWasOnTime',
                'Hook_NAOT_OrderWasAccurate' =>'GH_NAOT_OrderWasAccurate',
                'Entry_Number',
                'created_at',
                'updated_at'
            ];

            // Define a callback that writes CSV rows directly to the output stream
            $callback = function() use ($data, $columns) {
                $file = fopen('php://output', 'w');

                // Write header row with custom column names
                $headers = [];
                foreach ($columns as $key => $value) {
                    $headers[] = is_numeric($key) ? $value : $value;
                }
                fputcsv($file, $headers);

                // Write each record as a CSV row
                foreach ($data as $item) {
                    $row = [];
                    foreach ($columns as $key => $value) {
                        $field = is_numeric($key) ? $value : $key;
                        $row[] = $item->{$field};
                    }
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            // Generate filename with filter information
            $filename = 'deposit_delivery_data';
            if ($startDate && $endDate) {
                $filename .= '_' . $startDate . '_to_' . $endDate;
            } else if ($startDate) {
                $filename .= '_from_' . $startDate;
            } else if ($endDate) {
                $filename .= '_until_' . $endDate;
            }

            if ($franchiseeNum) {
                $filename .= '_store_' . $franchiseeNum;
            }

            $filename .= '.csv';

            Log::info('Deposit Delivery Data CSV export completed', [
                'filename' => $filename,
                'record_count' => $recordCount
            ]);

            // Return a streaming download response
            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv',
                'Access-Control-Allow-Origin' => '*', // Add CORS header
                'Access-Control-Allow-Methods' => 'GET',
                'Access-Control-Allow-Headers' => 'Content-Type'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting Deposit Delivery Data CSV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => isset($query) ? $query->toSql() : 'Query not initialized',
                'bindings' => isset($query) ? $query->getBindings() : []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }
}
