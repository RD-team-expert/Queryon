<?php

namespace App\Http\Controllers\NVT;

use Illuminate\Http\Request;
use App\Models\RDO_Data_Model;
use App\Http\Controllers\Controller;
class RDO_Data_Controller extends Controller
{
    public function create(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        // Extract sections from the JSON payload
        $hookMain = $data['HookMain'] ?? [];
        $hookControlPanel = $data['HookControlPanel'] ?? [];
        $entry = $data['Entry'] ?? [];

        // Extract name mapping from HookMain->HookName and department mapping from HookMain->HookDepartment
        $hookName = $hookMain['HookName'] ?? [];
        $hookDepartment = $hookMain['HookDepartment'] ?? [];

        // Map the JSON fields to the corresponding table columns
        $rdoData = RDO_Data_Model::create([
            // HookMain -> HookName mapping
            'Name_Lable' => $hookName['Label'] ?? null,
            'Name_ID'    => $hookName['Id'] ?? null,

            // HookMain mapping
            'HookTodaysDate' => $hookMain['HookTodaysDate'] ?? null,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $hookMain['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? null,
            'HookStartDate' => $hookMain['HookStartDate'] ?? null,
            'HookEndDate'   => $hookMain['HookEndDate'] ?? null,
            'HookDepartment_ID' => $hookDepartment['Id'] ?? null,
            'HookDepartment_Lable' => $hookDepartment['Label'] ?? null,
            'HookHowManyDaysDoYouNeed2' => $hookMain['HowManyDaysDoYouNeed2'] ?? null,
            'HookType' => $hookMain['HookType'] ?? null,
            'HookAreYouAbleToProvideAProof' => isset($hookMain['HookAreYouAbleToProvideAProof'])
                ? (is_array($hookMain['HookAreYouAbleToProvideAProof']) ? json_encode($hookMain['HookAreYouAbleToProvideAProof']) : $hookMain['HookAreYouAbleToProvideAProof'])
                : null,

            'HookHowManyDaysDoYouNeed2_IncrementBy' => $hookMain['HookHowManyDaysDoYouNeed2_IncrementBy'] ?? null,
            'HookAreYouAbleToProvideMoreSpecificPlease_IsRequired' => $hookMain['HookAreYouAbleToProvideMoreSpecificPlease'] ?? null,
            // HookControlPanel mapping
            'DirectManagerName_ID' => $hookControlPanel['DirectManagerName']['Id'] ?? null,
            'DirectManagerName_Lable' => $hookControlPanel['DirectManagerName']['Label'] ?? null,
            'HookApprove' => $hookControlPanel['HookApprove'] ?? null,
            'Note' => $hookControlPanel['Note'] ?? null,

            // Entry mapping
            'AdminLink' => $entry['AdminLink'] ?? null,
            'Status' => $entry['Status'] ?? null,
            'PublicLink' => $entry['PublicLink'] ?? null,
            'InternalLink' => $entry['InternalLink'] ?? null,
            'Entry_Number' => isset($entry['Number']) ? (string)$entry['Number'] : null,
        ]);

        return response()->json([
            'message' => 'RDO data created successfully',
            'data'    => $rdoData
        ], 201);
    }

    public function update(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        // Extract the entry number to identify the record to update
        $entryNumber = isset($data['Entry']['Number']) ? (string)$data['Entry']['Number'] : null;

        if (!$entryNumber) {
            return response()->json(['message' => 'Entry_Number not provided'], 400);
        }

        // Find the record by Entry_Number
        $rdoData = RDO_Data_Model::where('Entry_Number', $entryNumber)->first();
        if (!$rdoData) {
            return response()->json(['message' => 'RDO data not found'], 404);
        }

        // Extract sections from the JSON payload
        $hookMain = $data['HookMain'] ?? [];
        $hookControlPanel = $data['HookControlPanel'] ?? [];
        $entry = $data['Entry'] ?? [];

        // Extract name mapping from HookMain->HookName and department mapping from HookMain->HookDepartment
        $hookName = $hookMain['HookName'] ?? [];
        $hookDepartment = $hookMain['HookDepartment'] ?? [];

        // Update the record with new values
        $rdoData->update([
            // HookMain -> HookName mapping
            'Name_Lable' => $hookName['Label'] ?? $rdoData->Name_Lable,
            'Name_ID'    => $hookName['Id'] ?? $rdoData->Name_ID,

            // HookMain mapping
            'HookTodaysDate' => $hookMain['HookTodaysDate'] ?? $rdoData->HookTodaysDate,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $hookMain['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? $rdoData->HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA,
            'HookStartDate' => $hookMain['HookStartDate'] ?? $rdoData->HookStartDate,
            'HookEndDate'   => $hookMain['HookEndDate'] ?? $rdoData->HookEndDate,
            'HookDepartment_ID' => $hookDepartment['Id'] ?? $rdoData->HookDepartment_ID,
            'HookDepartment_Lable' => $hookDepartment['Label'] ?? $rdoData->HookDepartment_Lable,
            'HowManyDaysDoYouNeed2' => $hookMain['HowManyDaysDoYouNeed2'] ?? $rdoData->HookHowManyDaysDoYouNeed2,
            'HookType' => $hookMain['HookType'] ?? $rdoData->HookType,
            'HookAreYouAbleToProvideAProof' => isset($hookMain['HookAreYouAbleToProvideAProof'])
                ? (is_array($hookMain['HookAreYouAbleToProvideAProof']) ? json_encode($hookMain['HookAreYouAbleToProvideAProof']) : $hookMain['HookAreYouAbleToProvideAProof'])
                : $rdoData->HookAreYouAbleToProvideAProof,
            'HookHowManyDaysDoYouNeed2_IncrementBy' => $hookMain['HookHowManyDaysDoYouNeed2_IncrementBy'] ?? $rdoData->HookHowManyDaysDoYouNeed2_IncrementBy,
            'HookAreYouAbleToProvideMoreSpecificPlease_IsRequired' => $hookMain['HookAreYouAbleToProvideMoreSpecificPlease'] ?? null,


            // HookControlPanel mapping
            'DirectManagerName_ID' => $hookControlPanel['DirectManagerName']['Id'] ?? $rdoData->DirectManagerName_ID,
            'DirectManagerName_Lable' => $hookControlPanel['DirectManagerName']['Label'] ?? $rdoData->DirectManagerName_Lable,
            'HookApprove' => $hookControlPanel['HookApprove'] ?? $rdoData->HookApprove,
            'Note' => $hookControlPanel['Note'] ?? $rdoData->Note,

            // Entry mapping
            'AdminLink' => $entry['AdminLink'] ?? $rdoData->AdminLink,
            'Status' => $entry['Status'] ?? $rdoData->Status,
            'PublicLink' => $entry['PublicLink'] ?? $rdoData->PublicLink,
            'InternalLink' => $entry['InternalLink'] ?? $rdoData->InternalLink,
        ]);

        return response()->json([
            'message' => 'RDO data updated successfully',
            'data'    => $rdoData
        ], 200);
    }

    public function destroy(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);
        $entryNumber = isset($data['Entry']['Number']) ? (string)$data['Entry']['Number'] : null;

        if (!$entryNumber) {
            return response()->json(['message' => 'Entry_Number not provided'], 400);
        }

        // Find the record by Entry_Number
        $rdoData = RDO_Data_Model::where('Entry_Number', $entryNumber)->first();
        if (!$rdoData) {
            return response()->json(['message' => 'RDO data not found'], 404);
        }

        // Delete the record
        $rdoData->delete();

        return response()->json([
            'message' => 'RDO data deleted successfully'
        ], 200);
    }

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
