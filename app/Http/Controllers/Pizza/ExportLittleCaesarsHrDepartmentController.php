<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\LittleCaesarsHrDepartmentData;
use Illuminate\Http\Request;

class ExportLittleCaesarsHrDepartmentController extends Controller
{
    /**
     * Export LittleCaesarsHrDepartmentData as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the LITTLECAESARSHRDEPARTMENT_Data table
        $data = LittleCaesarsHrDepartmentData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'HookLanguage',
            'HookStore',
            'Hookالمتجر',
            'HookAlmacenar',
            'EN_Form_Type',
            'AR_Form_Type',
            'Sp_Form_Type',
            'EntryNum',
            'DateSubmitted',
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
            ->header('Content-Disposition', 'inline; filename="littlecaesars_hr_data.csv"');
    }

    /**
     * Return all LittleCaesarsHrDepartmentData as JSON.
     */
    public function getData()
    {
        // Fetch all records from the LITTLECAESARSHRDEPARTMENT_Data table
        $data = LittleCaesarsHrDepartmentData::all();
        return response()->json($data);
    }

    /**
     * Export all LittleCaesarsHrDepartmentData as a downloadable CSV.
     */
    public function export()
    {
        // Fetch all records from the LITTLECAESARSHRDEPARTMENT_Data table
        $data = LittleCaesarsHrDepartmentData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'HookLanguage',
            'HookStore',
            'Hookالمتجر',
            'HookAlmacenar',
            'EN_Form_Type',
            'AR_Form_Type',
            'Sp_Form_Type',
            'EntryNum',
            'DateSubmitted',
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
        return response()->streamDownload($callback, 'littlecaesars_hr_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}