<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Add this import for the File facade
use Illuminate\Support\Str;
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

    // --- Step 1: Download and convert XLS to CSV using PhpSpreadsheet ---
    Log::info('Starting XLS to CSV conversion using PhpSpreadsheet');
    try {
        // Download the XLS file
        $response = Http::get($xlsUrl);
        if ($response->failed()) {
            Log::error('Failed to download XLS file', ['url' => $xlsUrl]);
            return response()->json(['error' => 'Failed to download XLS file'], 500);
        }

        // Save the XLS file temporarily
        $xlsPath = storage_path('app/temp_file.xls');
        file_put_contents($xlsPath, $response->body());
        Log::info('XLS file downloaded', ['path' => $xlsPath]);

        // Load the XLS file with PhpSpreadsheet
        $spreadsheet = IOFactory::load($xlsPath);
        Log::info('XLS file loaded with PhpSpreadsheet');

        // Create CSV Writer
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        // Save as CSV
        $writer->save($csvPath);
        Log::info('XLS converted to CSV', ['csvPath' => $csvPath]);

        // Remove temporary XLS file
        if (File::exists($xlsPath)) {
            File::delete($xlsPath);
            Log::info('Temporary XLS file deleted', ['xlsPath' => $xlsPath]);
        }
    } catch (\Exception $e) {
        Log::error('Error during XLS to CSV conversion', ['exception' => $e->getMessage()]);
        return response()->json(['error' => 'Conversion failed', 'details' => $e->getMessage()], 500);
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
    $insertedCount = 0;
    $skippedCount = 0;

    foreach ($csvRows as $index => $row) {
        try {
            // Check if Clock_In data exists and is not empty
            if (empty($row[5])) {
                Log::info("Skipping row $index - Clock_In is empty", ['row' => $row]);
                $skippedCount++;
                continue; // Skip this row
            }

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
                'Entry_Number' => $jsonData['Entry']['Number'] ?? null,
            ]);
            Log::info("Record created for row $index", ['row' => $row]);
            $insertedCount++;
        } catch (\Exception $e) {
            Log::error("Failed to create record for row $index", ['row' => $row, 'exception' => $e->getMessage()]);
        }
    }

    Log::info('Data processed successfully, returning JSON response', [
        'inserted_count' => $insertedCount,
        'skipped_count' => $skippedCount
    ]);

    return response()->json([
        'message'      => 'Data processed successfully',
        'record_count' => $insertedCount,
        'skipped_count' => $skippedCount
    ]);
}

/**
     * Delete all records with a specific Entry_Number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteByEntryNumber(Request $request)
    {
        Log::info('deleteByEntryNumber method called');

        // Receive and decode the JSON data from the POST request
        Log::info('Validating JSON payload');
        $jsonData = $request->json()->all();
        Log::info('Received JSON data', ['data' => $jsonData]);

        // Ensure the Entry data exists
        if (!isset($jsonData['Entry']) || !isset($jsonData['Entry']['Number'])) {
            Log::error('No Entry Number provided in JSON');
            return response()->json(['error' => 'No Entry Number provided'], 400);
        }

        // Extract Entry Number from the decoded data
        $entryNumber = $jsonData['Entry']['Number'];
        Log::info('Extracted Entry Number', ['Entry_Number' => $entryNumber]);

        try {
            // Count records before deletion for reporting
            $recordCount = ClockInOutData::where('Entry_Number', $entryNumber)->count();
            Log::info('Found records to delete', ['Entry_Number' => $entryNumber, 'count' => $recordCount]);

            if ($recordCount === 0) {
                Log::info('No records found with Entry_Number', ['Entry_Number' => $entryNumber]);
                return response()->json([
                    'message' => 'No records found with the specified Entry_Number',
                    'Entry_Number' => $entryNumber
                ], 404);
            }

            // Get record IDs for logging before deletion
            $recordIds = ClockInOutData::where('Entry_Number', $entryNumber)->pluck('id')->toArray();
            Log::info('Records to be deleted', ['record_ids' => $recordIds]);

            // Delete all records with the specified Entry_Number
            ClockInOutData::where('Entry_Number', $entryNumber)->delete();

            Log::info('Records deleted successfully', [
                'Entry_Number' => $entryNumber,
                'deleted_count' => $recordCount,
                'deleted_record_ids' => $recordIds
            ]);

            return response()->json([
                'message' => 'Records deleted successfully',
                'Entry_Number' => $entryNumber,
                'deleted_count' => $recordCount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete records', [
                'Entry_Number' => $entryNumber,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to delete records',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update all records with a specific Entry_Number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateByEntryNumber(Request $request)
    {
        Log::info('updateByEntryNumber method called');
        // Receive and decode the JSON data from the POST request
        Log::info('Validating JSON payload');
        $jsonData = $request->json()->all();
        Log::info('Received JSON data', ['data' => $jsonData]);
        // Ensure the Entry data exists
        if (!isset($jsonData['Entry']) || !isset($jsonData['Entry']['Number'])) {
            Log::error('No Entry Number provided in JSON');
            return response()->json(['error' => 'No Entry Number provided'], 400);
        }
        // Extract Entry Number from the decoded data
        $entryNumber = $jsonData['Entry']['Number'];
        Log::info('Extracted Entry Number', ['Entry_Number' => $entryNumber]);

        try {
            // First delete existing records
            $deleteResult = $this->deleteByEntryNumber($request);

            // Check if deletion was successful or if there were no records to delete
            if ($deleteResult->getStatusCode() !== 200 && $deleteResult->getStatusCode() !== 404) {
                // If deletion failed with an error other than "not found", return the error
                return $deleteResult;
            }

            // Then create new records
            return $this->Index($request);
        } catch (\Exception $e) {
            Log::error('Failed to update records', [
                'Entry_Number' => $entryNumber,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to update records',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

