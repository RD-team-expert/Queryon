<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pizza\DepositDeliveryData;
use Illuminate\Support\Facades\Log;

class DepositDeliveryController extends Controller
{
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
        Log::info('Deposit Delivery Data CSV export requested', [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        // Get parameters from either URL segments or query parameters
        $startDate = $startDateParam ?? $request->query('start_date');
        $endDate = $endDateParam ?? $request->query('end_date');
        $franchiseeNum = $franchiseeNumParam ?? $request->query('franchisee_num');

        Log::debug('CSV Export parameters', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'franchisee_num' => $franchiseeNum,
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
        if ($franchiseeNum) {
            // Convert to string to ensure proper comparison
            $query->where('HookFranchiseeNum', (string)$franchiseeNum);
            Log::debug('Filtering by franchisee number', ['franchisee_num' => $franchiseeNum]);
        }

        try {
            // Execute the query
            $data = $query->get();

            $recordCount = $data->count();
            Log::info('Deposit Delivery Data CSV retrieved successfully', [
                'record_count' => $recordCount,
                'date_range' => $startDate && $endDate ? "$startDate to $endDate" : 'all dates',
                'franchisee_num' => $franchiseeNum ?: 'all franchisees'
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
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export Deposit Delivery Data: ' . $e->getMessage()
            ], 500);
        }
    }
}
