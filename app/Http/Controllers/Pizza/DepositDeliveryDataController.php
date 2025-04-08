<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pizza\DepositDeliveryData;
use Illuminate\Support\Facades\Log;

class DepositDeliveryDataController extends Controller
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
            'store' => data_get($json, 'HookStoreNum'),
            'date' => data_get($json, 'HookWorkDaysDate')
        ]);

        // 🔹 Basic Info
        $storeNumber    = data_get($json, 'HookStoreNum');
        $workDate       = data_get($json, 'HookWorkDaysDate');

        // 🔹 Safe Info - Check if these paths exist in your JSON
        $totalChange    = data_get($json, 'HookDepositProcess.HookTotalChange', null);
        $amountInSafe   = data_get($json, 'HookDepositProcess.HookAmountInSafe', null);
        $tipsAmount     = data_get($json, 'HookDepositProcess.HookHowMuchTips', null);

        // 🔹 Deposit Info
        $depositAmount  = data_get($json, 'HookDepositProcess.HookCashDeposit', null);

        // Log the extracted data for debugging
        Log::debug('Extracted data from JSON', [
            'totalChange' => $totalChange,
            'amountInSafe' => $amountInSafe,
            'tipsAmount' => $tipsAmount,
            'depositAmount' => $depositAmount
        ]);

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
                'HookStoreNum' => $storeNumber,

                'HookWorkDaysDate' => $workDate,
                'HookTotalChange' => $totalChange,
                'HookAmountInSafe' => $amountInSafe,
                'HookHowMuchTips' => $tipsAmount,
                'HookDepositAmount' => $depositAmount,
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
                'store' => $storeNumber,
                'date' => $workDate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit Delivery Data stored successfully.',
                'data'    => $record
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Deposit Delivery Data', [
                'store' => $storeNumber,
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
            'store' => data_get($json, 'HookStoreNum'),
            'date' => data_get($json, 'HookWorkDaysDate')
        ]);

        // Extract data from JSON
        // 🔹 Basic Info
        $storeNumber    = data_get($json, 'HookStoreNum');

        $workDate       = data_get($json, 'HookWorkDaysDate');

        // 🔹 Safe Info - Check if these paths exist in your JSON
        $totalChange    = data_get($json, 'HookDepositProcess.HookTotalChange', null);
        $amountInSafe   = data_get($json, 'HookDepositProcess.HookAmountInSafe', null);
        $tipsAmount     = data_get($json, 'HookDepositProcess.HookHowMuchTips', null);

        // 🔹 Deposit Info
        $depositAmount  = data_get($json, 'HookDepositProcess.HookDepositAmount', null);

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
            'HookStoreNum' => $storeNumber,

            'HookWorkDaysDate' => $workDate,
            'HookTotalChange' => $totalChange,
            'HookAmountInSafe' => $amountInSafe,
            'HookHowMuchTips' => $tipsAmount,
            'HookDepositAmount' => $depositAmount,
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
                'store' => $storeNumber,
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
                'store' => $storeNumber,
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
                'store' => $record->HookStoreNum,
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
}
