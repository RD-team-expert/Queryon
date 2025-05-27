<?php

namespace App\Http\Controllers;

use App\Models\Late_Early_Model;
use Illuminate\Http\Request;

class Export_Late_Early_Controller extends Controller
{
    /**
     * Export Late_Early_Model data as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the Late_Early table
        $data = Late_Early_Model::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'HookDirectManagerName_ID',
            'HookDirectManagerName_Lable',
            'HookApprove',
            'HookNote',
            'HookName_ID',
            'HookName_Lable',
            'HookTodaysDate',
            'HookPleaseProvideAReasonForYourRequest',
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
            'HookComingLateLeavingEarlier',
            'HookDepartment_ID',
            'HookDepartment_Lable',
            'HookComingHour',
            'HookLeavingHour',
            'HookShift2',
            'HookChangeSift',
            'HookStartAt',
            'HookEndAt',
            'AdminLink',
            'DateCreated',
            'DateSubmitted',
            'DateUpdated',
            'EntryNumber',
            'PublicLink',
            'InternalLink',
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
            ->header('Content-Disposition', 'inline; filename="late_early_data.csv"');
    }

    /**
     * Return all Late_Early_Model data as JSON.
     */
    public function getData()
    {
        // Fetch all records from the Late_Early table
        $data = Late_Early_Model::all();
        return response()->json($data);
    }

    /**
     * Export all Late_Early_Model data as a downloadable CSV.
     */
    /**
     * Export all Late_Early_Model data as a downloadable CSV.
     */
    public function export()
    {
        // Fetch all records, group by EntryNumber and merge split records
        $data = Late_Early_Model::all()
            ->groupBy('EntryNumber')
            ->map(function ($group) {
                // Merge all records in the group into one
                $mergedRecord = $group->reduce(function ($carry, $record) {
                    if (!$carry) return $record;

                    // Merge any non-null values from subsequent records
                    foreach ($record->getAttributes() as $key => $value) {
                        if (!is_null($value) && $carry->{$key} != $value) {
                            $carry->{$key} = $value;
                        }
                    }
                    return $carry;
                });

                return $mergedRecord;
            });

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'HookDirectManagerName_ID',
            'HookDirectManagerName_Lable',
            'HookApprove',
            'HookNote',
            'HookName_ID',
            'HookName_Lable',
            'HookTodaysDate',
            'HookPleaseProvideAReasonForYourRequest',
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
            'HookComingLateLeavingEarlier',
            'HookDepartment_ID',
            'HookDepartment_Lable',
            'HookComingHour',
            'HookLeavingHour',
            'HookShift2',
            'HookChangeSift',
            'HookStartAt',
            'HookEndAt',
            'AdminLink',
            'DateCreated',
            'DateSubmitted',
            'DateUpdated',
            'EntryNumber',
            'PublicLink',
            'InternalLink',
            'created_at',
            'updated_at'
        ];

        // Define a callback that writes CSV rows directly to the output stream
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            // Write header row
            fputcsv($file, $columns);

            // Write each merged record as a CSV row
            foreach ($data as $record) {
                $row = [];
                foreach ($columns as $col) {
                    $value = $record->{$col};
                    if (is_string($value)) {
                        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
                    }
                    $row[] = $value;
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        // Return a streaming download response
        return response()->streamDownload($callback, 'late_early_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }



}
