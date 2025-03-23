<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Add this import for the File facade
use App\Models\ClockInOutData;
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use \ConvertApi\ConvertApi;

class ClockInOutController extends Controller
{

    /**
     * Process CSV file and store data in the database
     *
     * @param string $filePath Path to the CSV file
     * @return void
     */

    public function Index(Request $request)
{
    Log::info('Index method called');

    // 1. Receive and decode the JSON data from the POST request
    Log::info('Validating JSON payload');
    $jsonData = $request->json()->all();
    Log::info('Received JSON data', ['data' => $jsonData]);

    // 2. Ensure the file data exists
    if (!isset($jsonData['HookDataExcelFile'][0])) {
        Log::error('No file data provided in JSON');
        return response()->json(['error' => 'No file data provided'], 400);
    }

    // 3. Extract file URL from the decoded data
    $fileData = $jsonData['HookDataExcelFile'][0];
    $xlsUrl = $fileData['File']; // Download URL from JSON
    Log::info('Extracted XLS URL', ['xlsUrl' => $xlsUrl]);

    // Define temporary CSV file path using storage_path() for consistency with Laravel's storage folder.
    $csvPath = storage_path('app/temp_file.csv'); // Temporary CSV file path.
    Log::info('Temporary CSV file path set', ['csvPath' => $csvPath]);

    // --- Step 1: Convert XLS to CSV via ConvertApi using the URL directly ---
    Log::info('Starting XLS to CSV conversion via ConvertApi using URL');
    try {
        // Set API credentials for ConvertApi
        ConvertApi::setApiCredentials('secret_ThRmzEB7TmkhGlgK');
        Log::info('ConvertApi credentials set');

        // Convert the XLS file to CSV by providing the URL directly
        $result = ConvertApi::convert('csv', [
            'File'     => $xlsUrl,
            'FileName' => 'temp_file',
        ], 'xls');
        Log::info('Conversion API call successful');

        // Save the converted CSV file to the app storage directory
        $result->saveFiles(storage_path('app'));
        Log::info('Converted CSV file saved', ['csvPath' => $csvPath]);
    } catch (\Exception $e) {
        Log::error('Error during conversion API call', ['exception' => $e->getMessage()]);
        return response()->json(['error' => 'Conversion API call failed', 'details' => $e->getMessage()], 500);
    }

    // --- Step 2: Read CSV Data and Skip Header ---
    Log::info('Reading CSV file', ['csvPath' => $csvPath]);
    $csvRows = [];
    if (($handle = fopen($csvPath, 'r')) !== false) {
        // Read and log the header row (assuming the first row contains column headers)
        $header = fgetcsv($handle, 1000, ',');
        Log::info('CSV header read', ['header' => $header]);

        // Loop through the rest of the CSV rows.
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $csvRows[] = $row;
        }
        fclose($handle);
        Log::info('CSV file read complete', ['row_count' => count($csvRows)]);
    } else {
        Log::error('Failed to open CSV file for reading', ['csvPath' => $csvPath]);
        return response()->json(['error' => 'Failed to open CSV file'], 500);
    }

    // --- Step 3: Delete Temporary CSV File ---
    Log::info('Deleting temporary CSV file');
    if (File::exists($csvPath)) {
        File::delete($csvPath);
        Log::info('Deleted temporary CSV file', ['csvPath' => $csvPath]);
    } else {
        Log::warning('Temporary CSV file not found for deletion', ['csvPath' => $csvPath]);
    }

    // --- Step 4: Save Data to the Database ---
    Log::info('Saving CSV data to the database', ['record_count' => count($csvRows)]);
    foreach ($csvRows as $index => $row) {
        try {
            // Use ClockInOutData model instead of Record
            ClockInOutData::create([
                'AC_No'      => $row[0] ?? null,
                'Name'       => $row[1] ?? null,
                'Date'       => !empty($row[2]) ? date('Y-m-d', strtotime($row[2])) : null,
                'On_duty'    => !empty($row[3]) ? date('H:i:s', strtotime($row[3])) : null,
                'Off_duty'   => !empty($row[4]) ? date('H:i:s', strtotime($row[4])) : null,
                'Clock_In'   => !empty($row[5]) ? date('H:i:s', strtotime($row[5])) : null,
                'Clock_Out'  => !empty($row[6]) ? date('H:i:s', strtotime($row[6])) : null,
                'Late'       => $row[7] ?? null,
                'Early'      => $row[8] ?? null,
                'Work_Time'  => $row[9] ?? null,
                'Department' => $row[10] ?? null,
            ]);
            Log::info("Record created for row $index", ['row' => $row]);
        } catch (\Exception $e) {
            Log::error("Failed to create record for row $index", ['row' => $row, 'exception' => $e->getMessage()]);
        }
    }

    Log::info('Data processed successfully, returning JSON response');
    return response()->json([
        'message'      => 'Data processed successfully',
        'record_count' => count($csvRows)
    ]);





}



}

