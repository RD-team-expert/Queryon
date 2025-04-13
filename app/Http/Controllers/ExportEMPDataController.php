<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeesDataModel;

class ExportEMPDataController extends Controller
{
    /**
     * Export EmployeesDataModel data as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the Employees_Data table
        $data = EmployeesDataModel::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'first_name_english',
            'first_and_last_name_english',
            'last_name_english',
            'first_name_arabic',
            'first_and_last_name_arabic',
            'last_name_arabic',
            'hiring_date',
            'pne_email',
            'personal_email',
            'sy_phone',
            'us_phone',
            'CLockInOutID',
            'img_link',
            'about_you',
            'password2',
            'shift',
            'depatment_id',
            'depatment_label',
            'position_id',
            'position_label',
            'direct_manager1_id',
            'direct_manager1_label',
            'is_manager',
            'second_level_manager_id',
            'second_level_manager_label',
            'dm_email_id',
            'dm_email_label',
            'second_email_id',
            'second_email_label',
            'offboarded',
            'direct_manager2_id',
            'direct_manager2_label',
            'dm_email2_id',
            'dm_email2_label',
            'level',
            'tier',
            'entry_admin_link',
            'Cognito_ID',
            'entry_status',
            'entry_public_link',
            'entry_internal_link',
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
            ->header('Content-Disposition', 'inline; filename="employees_data.csv"');
    }

    /**
     * Return all EmployeesDataModel data as JSON.
     */
    public function getData()
    {
        // Fetch all records from the Employees_Data table
        $data = EmployeesDataModel::all();
        return response()->json($data);
    }

    /**
     * Export all EmployeesDataModel data as a downloadable CSV.
     */
    public function export()
{
    // Fetch all records from the Employees_Data table
    $data = EmployeesDataModel::all();

    // Define the columns to export (all fields)
    // In both exportToExcel() and export() methods, add 'CLockInOutID' to the $columns array:
    $columns = [
        'id',
        'first_name_english',
        'first_and_last_name_english',
        'last_name_english',
        'first_name_arabic',
        'first_and_last_name_arabic',
        'last_name_arabic',
        'hiring_date',
        'pne_email',
        'personal_email',
        'sy_phone',
        'us_phone',
        'CLockInOutID',
        'img_link',
        'about_you',
        'password2',
        'shift',
        'depatment_id',
        'depatment_label',
        'position_id',
        'position_label',
        'direct_manager1_id',
        'direct_manager1_label',
        'is_manager',
        'second_level_manager_id',
        'second_level_manager_label',
        'dm_email_id',
        'dm_email_label',
        'second_email_id',
        'second_email_label',
        'offboarded',
        'direct_manager2_id',
        'direct_manager2_label',
        'dm_email2_id',
        'dm_email2_label',
        'level',
        'tier',
        'entry_admin_link',
        'Cognito_ID',
        'entry_status',
        'entry_public_link',
        'entry_internal_link',
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
    return response()->streamDownload($callback, 'employees_data.csv', [
        'Content-Type' => 'text/csv',
    ]);
}   

}
