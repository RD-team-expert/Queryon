<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClockInOutData;

class Export_ClockInOutController extends Controller
{
    /**
     * Export ClockInOutData as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the clock_in_out_data table
        $data = ClockInOutData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'AC_No',
            'Name',
            'Date',
            'On_duty',
            'Off_duty',
            'Clock_In',
            'Clock_Out',
            'Late',
            'Early',
            'Work_Time',
            'Department',
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
            ->header('Content-Disposition', 'inline; filename="clock_in_out_data.csv"');
    }

    /**
     * Return all ClockInOutData as JSON.
     */
    public function getData()
    {
        // Fetch all records from the clock_in_out_data table
        $data = ClockInOutData::all();
        return response()->json($data);
    }

    /**
     * Export all ClockInOutData as a downloadable CSV.
     */
    public function export()
    {
        // Fetch all records from the clock_in_out_data table
        $data = ClockInOutData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'AC_No',
            'Name',
            'Date',
            'On_duty',
            'Off_duty',
            'Clock_In',
            'Clock_Out',
            'Late',
            'Early',
            'Work_Time',
            'Department',
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
        return response()->streamDownload($callback, 'clock_in_out_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}