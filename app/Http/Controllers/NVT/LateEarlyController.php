<?php

namespace App\Http\Controllers\NVT;

use App\Models\Late_Early_Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class LateEarlyController extends Controller
{
    // Store function to insert data into the table
    public function store(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Extract data and map to database columns
        $lateEarly = Late_Early_Model::create([
            'HookDirectManagerName_ID' => $data['HookManagerAprove']['HookDirectManagerName']['Id'] ?? null,
            'HookDirectManagerName_Lable' => $data['HookManagerAprove']['HookDirectManagerName']['Label'] ?? null,
            'HookApprove' => $data['HookManagerAprove']['HookApprove'] ?? null,
            'HookNote' => $data['HookManagerAprove']['HookNote'] ?? null,
            'HookName_ID' => $data['HookMain']['HookName']['Id'] ?? null,
            'HookName_Lable' => $data['HookMain']['HookName']['Label'] ?? null,
            'HookTodaysDate' => $data['HookMain']['HookTodaysDate'] ?? null,
            'HookPleaseProvideAReasonForYourRequest' => $data['HookMain']['HookPleaseProvideAReasonForYourRequest'] ?? null,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $data['HookMain']['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? null,
            'HookComingLateLeavingEarlier' => $data['HookMain']['HookComingLateLeavingEarlier'] ?? null,
            'HookDepartment_ID' => $data['HookMain']['HookDepartment']['Id'] ?? null,
            'HookDepartment_Lable' => $data['HookMain']['HookDepartment']['Label'] ?? null,
            'HookComingHour' => $data['HookMain']['HookComingHour'] ?? null,
            'HookLeavingHour' => $data['HookMain']['HookLeavingHour'] ?? null,
            'HookShift2' => $data['HookMain']['HookShift2'] ?? null,
            'HookChangeSift' => json_encode($data['HookMain']['HookChangeSift'] ?? []),
            'HookStartAt' => $data['HookMain']['HookStartAt'] ?? null,
            'HookEndAt' => $data['HookMain']['HookEndAt'] ?? null,
            'AdminLink' => $data['Entry']['AdminLink'] ?? null,
            'DateCreated' => $data['Entry']['DateCreated'] ?? null,
            'DateSubmitted' => $data['Entry']['DateSubmitted'] ?? null,
            'DateUpdated' => $data['Entry']['DateUpdated'] ?? null,
            'EntryNumber' => $data['Entry']['Number'] ?? null,
            'PublicLink' => $data['Entry']['PublicLink'] ?? null,
            'InternalLink' => $data['Entry']['InternalLink'] ?? null,
        ]);

        return response()->json(['message' => 'Record stored successfully!', 'data' => $lateEarly], 201);
    }

    // Update function to update an existing record
    public function update(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by Cognito_ID
        $lateEarly = Late_Early_Model::where('EntryNumber', $data['Entry']['Number'])->first();

        if (!$lateEarly) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Update the record with the new data
        $lateEarly->update([
            'HookDirectManagerName_ID' => $data['HookManagerAprove']['HookDirectManagerName']['Id'] ?? null,
            'HookDirectManagerName_Lable' => $data['HookManagerAprove']['HookDirectManagerName']['Label'] ?? null,
            'HookApprove' => $data['HookManagerAprove']['HookApprove'] ?? null,
            'HookNote' => $data['HookManagerAprove']['HookNote'] ?? null,
            'HookName_ID' => $data['HookMain']['HookName']['Id'] ?? null,
            'HookName_Lable' => $data['HookMain']['HookName']['Label'] ?? null,
            'HookTodaysDate' => $data['HookMain']['HookTodaysDate'] ?? null,
            'HookPleaseProvideAReasonForYourRequest' => $data['HookMain']['HookPleaseProvideAReasonForYourRequest'] ?? null,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $data['HookMain']['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? null,
            'HookComingLateLeavingEarlier' => $data['HookMain']['HookComingLateLeavingEarlier'] ?? null,
            'HookDepartment_ID' => $data['HookMain']['HookDepartment']['Id'] ?? null,
            'HookDepartment_Lable' => $data['HookMain']['HookDepartment']['Label'] ?? null,
            'HookComingHour' => $data['HookMain']['HookComingHour'] ?? null,
            'HookLeavingHour' => $data['HookMain']['HookLeavingHour'] ?? null,
            'HookShift2' => $data['HookMain']['HookShift2'] ?? null,
            'HookChangeSift' => json_encode($data['HookMain']['HookChangeSift'] ?? []),
            'HookStartAt' => $data['HookMain']['HookStartAt'] ?? null,
            'HookEndAt' => $data['HookMain']['HookEndAt'] ?? null,
            'AdminLink' => $data['Entry']['AdminLink'] ?? null,
            'DateCreated' => $data['Entry']['DateCreated'] ?? null,
            'DateSubmitted' => $data['Entry']['DateSubmitted'] ?? null,
            'DateUpdated' => $data['Entry']['DateUpdated'] ?? null,
            'EntryNumber' => $data['Entry']['Number'] ?? null,
            'PublicLink' => $data['Entry']['PublicLink'] ?? null,
            'InternalLink' => $data['Entry']['InternalLink'] ?? null,
        ]);

        return response()->json(['message' => 'Record updated successfully!', 'data' => $lateEarly], 200);
    }

    // Destroy function to delete a record by Cognito_ID
    public function destroy(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by Cognito_ID
        $lateEarly = Late_Early_Model::where('EntryNumber', $data['Entry']['Number'])->first();

        if (!$lateEarly) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Delete the record
        $lateEarly->delete();

        return response()->json(['message' => 'Record deleted successfully!'], 200);
    }
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
