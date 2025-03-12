<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapsData;

class ExportCapsDataController extends Controller
{
    /**
     * Export CapsData as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the caps_data table
        $data = CapsData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'hook_date_of_incident',
            'hook_cap_type',
            'hook_managements_statement',
            'hook_name_person_doing_the_cap_id',
            'hook_name_person_doing_the_cap_label',
            'hook_email_person_doing_the_cap_label',
            'hook_employee_name_id',
            'hook_employee_name_label',
            'hook_employee_email_label',
            'hook_title_person_doing_the_cap',
            'hook_the_email_person_doing_the_cap',
            'hook_the_email_employee_email',
            'hook_depatmant_name_filter',
            'hook_emp_section',
            'hook_employees_statement',
            'admin_link',
            'number',
            'timestamp',
            'cap_link',
            'emp_link',
            'view1_link',
            'upper_management_link',
            'the_next_manager_above_link',
            'upper_manager_in_the_department_link',
            'witness1_link',
            'witness2_link',
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
            ->header('Content-Disposition', 'inline; filename="caps_data.csv"');
    }

    /**
     * Return all CapsData as JSON.
     */
    public function getData()
    {
        // Fetch all records from the caps_data table
        $data = CapsData::all();
        return response()->json($data);
    }

    /**
     * Export all CapsData as a downloadable CSV.
     */
    public function export()
    {
        // Fetch all records from the caps_data table
        $data = CapsData::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'hook_date_of_incident',
            'hook_cap_type',
            'hook_managements_statement',
            'hook_name_person_doing_the_cap_id',
            'hook_name_person_doing_the_cap_label',
            'hook_email_person_doing_the_cap_label',
            'hook_employee_name_id',
            'hook_employee_name_label',
            'hook_employee_email_label',
            'hook_title_person_doing_the_cap',
            'hook_the_email_person_doing_the_cap',
            'hook_the_email_employee_email',
            'hook_depatmant_name_filter',
            'hook_emp_section',
            'hook_employees_statement',
            'admin_link',
            'number',
            'timestamp',
            'cap_link',
            'emp_link',
            'view1_link',
            'upper_management_link',
            'the_next_manager_above_link',
            'upper_manager_in_the_department_link',
            'witness1_link',
            'witness2_link',
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
        return response()->streamDownload($callback, 'caps_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
