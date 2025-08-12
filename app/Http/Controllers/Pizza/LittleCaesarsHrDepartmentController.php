<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\LittleCaesarsHrDepartmentData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LittleCaesarsHrDepartmentController extends Controller
{
    // Store function to insert data into the table
    public function store(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Format the date properly
        $dateSubmitted = null;
        if (isset($data['Entry']['DateSubmitted']) && !empty($data['Entry']['DateSubmitted'])) {
            $dateSubmitted = Carbon::parse($data['Entry']['DateSubmitted'])->format('Y-m-d');
        }

        // Extract data and map to database columns
        $littleCaesarsData = LittleCaesarsHrDepartmentData::create([
            'HookLanguage' => $data['HookMain']['HookLanguage'] ?? null,
            'HookStore' => $data['HookMain']['HookEnglish']['HookStore']['Label'] ?? null,
            'Hookالمتجر' => $data['HookMain']['HookArabic']['Hookالمتجر']['Label'] ?? null,
            'HookAlmacenar' => $data['HookMain']['HookEspañol']['HookAlmacenar']['Label'] ?? null,
            'HookSelectYourRequestType' => $data['HookMain']['HookEnglish']['HookSelectYourRequestType'] ?? null,
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات' => $data['HookMain']['HookArabic']['Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات'] ?? null,
            'HookSeleccioneSuTipoDeSolicitud' => $data['HookMain']['HookEspañol']['HookSeleccioneSuTipoDeSolicitud'] ?? null,
            'EntryNum' => $data['Entry']['Number'] ?? null,
            'DateSubmitted' => $dateSubmitted,
        ]);

        return response()->json(['message' => 'Record stored successfully!', 'data' => $littleCaesarsData], 201);
    }

    // Update function to update an existing record
    public function update(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by EntryNum
        $littleCaesarsData = LittleCaesarsHrDepartmentData::where('EntryNum', $data['Entry']['Number'])->first();

        if (!$littleCaesarsData) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Format the date properly
        $dateSubmitted = null;
        if (isset($data['Entry']['DateSubmitted']) && !empty($data['Entry']['DateSubmitted'])) {
            $dateSubmitted = Carbon::parse($data['Entry']['DateSubmitted'])->format('Y-m-d');
        }

        // Update the record with the new data
        $littleCaesarsData->update([
            'HookLanguage' => $data['HookMain']['HookLanguage'] ?? null,
            'HookStore' => $data['HookMain']['HookEnglish']['HookStore']['Label'] ?? null,
            'Hookالمتجر' => $data['HookMain']['HookArabic']['Hookالمتجر']['Label'] ?? null,
            'HookAlmacenar' => $data['HookMain']['HookEspañol']['HookAlmacenar']['Label'] ?? null,
            'HookSelectYourRequestType' => $data['HookMain']['HookEnglish']['HookSelectYourRequestType'] ?? null,
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات' => $data['HookMain']['HookArabic']['Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات'] ?? null,
            'HookSeleccioneSuTipoDeSolicitud' => $data['HookMain']['HookEspañol']['HookSeleccioneSuTipoDeSolicitud'] ?? null,
            'EntryNum' => $data['Entry']['Number'] ?? null,
            'DateSubmitted' => $dateSubmitted,
        ]);

        return response()->json(['message' => 'Record updated successfully!', 'data' => $littleCaesarsData], 200);
    }

    // Destroy function to delete a record by EntryNum
    public function destroy(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by EntryNum
        $littleCaesarsData = LittleCaesarsHrDepartmentData::where('EntryNum', $data['Entry']['Number'])->first();

        if (!$littleCaesarsData) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Delete the record
        $littleCaesarsData->delete();

        return response()->json(['message' => 'Record deleted successfully!'], 200);
    }

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
            'HookSelectYourRequestType',
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات',
            'HookSeleccioneSuTipoDeSolicitud',
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
            'HookSelectYourRequestType',
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات',
            'HookSeleccioneSuTipoDeSolicitud',
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
