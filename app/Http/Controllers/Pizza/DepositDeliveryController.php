<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pizza\DepositDeliveryData;

class DepositDeliveryController extends Controller
{
    /**
     * Export DepositDeliveryData data as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the deposit_delivery_data table
        $data = DepositDeliveryData::all();

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

        // Return the CSV as a response, with headers for Excel compatibility
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'inline; filename="deposit_delivery_data.csv"');
    }

    /**
     * Return all DepositDeliveryData data as JSON.
     */
    public function getData()
    {
        // Fetch all records from the deposit_delivery_data table
        $data = DepositDeliveryData::all();
        return response()->json($data);
    }

    /**
     * Export all DepositDeliveryData data as a downloadable CSV.
     */
    public function export()
    {
        // Fetch all records from the deposit_delivery_data table
        $data = DepositDeliveryData::all();

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

        // Define a callback that writes CSV rows directly to the output stream
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            // Write header row
            fputcsv($file, $columns);

            // Write each record as a CSV row
            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $item->{$col};
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        // Return a streaming download response using Laravel's response()->streamDownload method
        return response()->streamDownload($callback, 'deposit_delivery_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
