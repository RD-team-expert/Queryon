<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RDO_Data_Model;

class ExportRDODataController extends Controller
{
    /**
     * Export RDO_Data_Model data as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the rdo_data_table
        $data = RDO_Data_Model::all();

        // Define the columns to export
        $columns = [
            'id',
            'Name_Lable',
            'Name_ID',
            'HookTodaysDate',
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
            'HookStartDate',
            'HookEndDate',
            'HookDepartment_ID',
            'HookDepartment_Lable',
            'HookHowManyDaysDoYouNeed2',
            'HookType',
            'HookAreYouAbleToProvideAProof',
            'HookHowManyDaysDoYouNeed2_IncrementBy',
            'HookAreYouAbleToProvideMoreSpecificPlease_IsRequired',
            'DirectManagerName_ID',
            'DirectManagerName_Lable',
            'HookApprove',
            'Note',
            'AdminLink',
            'Status',
            'PublicLink',
            'InternalLink',
            'Entry_Number',
            'created_at',
            'updated_at'
        ];

        // Open a memory stream
        $handle = fopen('php://memory', 'r+');

        // Write CSV header row
        fputcsv($handle, $columns);

        // Write each record as a CSV row
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
            ->header('Content-Disposition', 'inline; filename="rdo_data.csv"');
    }

    /**
     * Return all RDO_Data_Model data as JSON.
     */
    public function getData()
    {
        $data = RDO_Data_Model::all();
        return response()->json($data);
    }

    /**
     * Export all RDO_Data_Model data as a downloadable CSV.
     */
    public function export()
    {
        $data = RDO_Data_Model::all();

        $columns = [
            'id',
            'Name_Lable',
            'Name_ID',
            'HookTodaysDate',
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
            'HookStartDate',
            'HookEndDate',
            'HookDepartment_ID',
            'HookDepartment_Lable',
            'HookHowManyDaysDoYouNeed2',
            'HookType',
            'HookAreYouAbleToProvideAProof',
            'HookHowManyDaysDoYouNeed2_IncrementBy',
            'HookAreYouAbleToProvideMoreSpecificPlease_IsRequired',
            'DirectManagerName_ID',
            'DirectManagerName_Lable',
            'HookApprove',
            'Note',
            'AdminLink',
            'Status',
            'PublicLink',
            'InternalLink',
            'Entry_Number',
            'created_at',
            'updated_at'
        ];

        // Define a callback to write CSV rows directly to the output stream
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

        // Return a streaming download response
        return response()->streamDownload($callback, 'rdo_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
