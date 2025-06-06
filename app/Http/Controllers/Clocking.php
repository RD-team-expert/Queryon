<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use \ConvertApi\ConvertApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Add this import if not already present
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpDate;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;

use App\Models\ClockingDataTable;

class Clocking extends Controller
{
    public function index(Request $request){
        //get the jason data
        $jsonData = $request->json()->all();
        Log::info('Received JSON data', ['data' => $jsonData]);

        //calling the proocessFile function to get back the CSV file path
        $dataRows=$this->processFile($jsonData);
        Log::info('Received XLS data', ['dataRows' => $dataRows]);

        //Entry Number Value
        $entryNumber = $jsonData['Entry']['Number'] ?? null;

        // use the processClockInOut to store the data
        $this->processClockInOut($dataRows, $entryNumber);
        Log::info('Data Stored in thee Clocking table');
    }

    public function update(Request $request){
        //get the jason data
        $jsonData = $request->json()->all();
        Log::info('Received JSON data', ['data' => $jsonData]);

        //calling the proocessFile function to get back the CSV file path
        $dataRows=$this->processFile($jsonData);
        Log::info('Received XLS data', ['dataRows' => $dataRows]);

        //Entry Number Value
        $entryNumber = $jsonData['Entry']['Number'] ?? null;

        //delete the records with the same entry number
        ClockingDataTable::where('Entry_Number', $entryNumber)->delete();
        Log::info("Deleted existing clocking_data rows for Entry_Number = {$entryNumber}");

        // use the processClockInOut to store the updated data
        $this->processClockInOut($dataRows, $entryNumber);
        Log::info('Data Stored in thee Clocking table');
    }
    public function delete(Request $request){
        //get the jason data
        $jsonData = $request->json()->all();
        Log::info('Received JSON data', ['data' => $jsonData]);

        // get entry number
        $entryNumber = $jsonData['Entry']['Number'] ?? null;

        //delete the records with the same entry number
        ClockingDataTable::where('Entry_Number', $entryNumber)->delete();
        Log::info("Deleted existing clocking_data rows for Entry_Number = {$entryNumber}");

    }

    public function processFile(array $jsonData)
    {
        // 1. Verify JSON has a “File” URL
        if (!isset($jsonData['HookDataExcelFile'][0]['File'])) {
            Log::error('No file data provided in JSON');
            return response()->json(['error' => 'No file data provided'], 400);
        }
        $fileUrl = $jsonData['HookDataExcelFile'][0]['File'];
        Log::info('Received file URL', ['url' => $fileUrl]);

        try {
            // 2. Download whatever’s at that URL
            $response = Http::get($fileUrl);
            if ($response->failed()) {
                Log::error('Failed to download file', ['url' => $fileUrl]);
                return response()->json(['error' => 'File download failed'], 500);
            }

            // 3. Figure out its extension (xls, xlsx, or csv)
            $extension = strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (! in_array($extension, ['xls','xlsx','csv'])) {
                // If URL has no “.xls/.xlsx/.csv”, try to guess from Content-Type
                $contentType = $response->header('Content-Type') ?? '';
                if (str_contains($contentType, 'csv') || str_contains($contentType, 'text')) {
                    $extension = 'csv';
                } elseif (str_contains($contentType, 'spreadsheet') || str_contains($contentType, 'excel')) {
                    // default to xls if it looks like a spreadsheet
                    $extension = 'xls';
                } else {
                    // fallback
                    $extension = 'xls';
                }
            }

            // 4. Save the downloaded bytes into storage/app/hookdata_<uniqid>.<ext>
            $baseName      = 'hookdata_' . uniqid();
            $tempFilePath  = storage_path("app/{$baseName}.{$extension}");
            file_put_contents($tempFilePath, $response->body());
            Log::info('Saved temporary file', ['path' => $tempFilePath]);

            // 5. If it’s already a CSV, we’re done—otherwise we load XLS/XLSX and write out a new CSV
            $csvFilePath = null;
            if ($extension === 'csv') {
                $csvFilePath = $tempFilePath;
            } else {
                // Load XLS or XLSX
                $spreadsheet = IOFactory::load($tempFilePath);

                // Write out to a new CSV
                $csvFilePath = storage_path("app/{$baseName}.csv");
                $csvWriter   = new CsvWriter($spreadsheet);
                // (Optional: if your CSV uses commas/enclosures differently, you can call:
                //    $csvWriter->setDelimiter(',');
                //    $csvWriter->setEnclosure('"');
                // )
                $csvWriter->save($csvFilePath);

                // Free memory from the loaded spreadsheet
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }

            // 6. Now read the CSV row by row using native PHP (fgetcsv), skipping the header
            $rows    = [];
            $rowNum  = 0;
            if (($handle = fopen($csvFilePath, 'r')) !== false) {
                while (($columns = fgetcsv($handle, 0, ',')) !== false) {
                    // First line is header → skip it
                    if ($rowNum === 0) {
                        $rowNum++;
                        continue;
                    }

                    // Process “column C” (which is index 2 in $columns) into a proper datetime string
                    if (isset($columns[2]) && $columns[2] !== null && $columns[2] !== '') {
                        $rawValue = $columns[2];
                        try {
                            if (is_numeric($rawValue)) {
                                // Excel serial date/time
                                $dt = PhpDate::excelToDateTimeObject((float) $rawValue);
                                $columns[2] = Carbon::instance($dt)->toDateTimeString();
                            } else {
                                // Already a string like "2025-06-05 08:15:00" or "05/06/2025 08:15"
                                $columns[2] = Carbon::parse($rawValue)->toDateTimeString();
                            }
                        } catch (\Exception $e) {
                            Log::warning('Invalid datetime in column C', ['value' => $rawValue]);
                            $columns[2] = null;
                        }
                    }

                    $rows[] = $columns;
                    $rowNum++;
                }
                fclose($handle);
            } else {
                Log::error('Unable to open CSV for reading', ['path' => $csvFilePath]);
                return response()->json(['error' => 'Cannot read CSV'], 500);
            }

            // 7. Clean up all temps
            File::delete($tempFilePath);
            Log::info('Deleted temp file', ['path' => $tempFilePath]);
            if ($csvFilePath !== $tempFilePath && File::exists($csvFilePath)) {
                // (i.e. if we made a separate CSV instead of it being the same)
                File::delete($csvFilePath);
                Log::info('Deleted temp CSV', ['path' => $csvFilePath]);
            }

            // 8. Return the array of rows (you can wrap as JSON if you prefer)
            return $rows;

        } catch (\Exception $e) {
            Log::error('Error processing file', ['exception' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Processing failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function processClockInOut(array $dataRows, $entryNumber = null): void
    {
        // note the first row is already ignored in the processFile function

        // first group the data by AC_No
        $grouped = [];
        foreach ($dataRows as $row) {
            // see if we have the first 4 items
            if (count($row) < 4) {
                continue;
            }
            //set the values to the var
            $acNo   = $row[0];
            $name   = $row[1];
            $time   = $row[2];
            $state  = $row[3];

            //if any or these acNo,time,state then breack the loop
            if (is_null($acNo) || is_null($time) || is_null($state)) {
                continue;
            }

            // upper case the C/In', 'C/Out
            $stateNorm = strtoupper(trim($state));

            // Only consider "C/IN" or "C/OUT" and breack if other
            if (!in_array($stateNorm, ['C/IN', 'C/OUT'], true)) {
                continue;
            }

            // use carbon to parse the datetime
            try {
                $dt = Carbon::parse($time);
            } catch (\Exception $e) {
                Log::warning('Skipping invalid datetime row', [
                    'AC_No'   => $acNo,
                    'Name'    => $name,
                    'DateTime'=> $time,
                    'State'   => $state,
                ]);
                continue;
            }

            $grouped[$acNo][] = [
                'AC_No'    => $acNo,
                'Name'     => $name,
                'DateTime' => $dt,
                'State'    => $stateNorm, // "C/IN" or "C/OUT"
            ];
        }

        // second sorting and pairing
        foreach ($grouped as $acNo => $events) {
            // sort ascending by datetime
            usort($events, function ($a, $b) {
                return $a['DateTime']->timestamp <=> $b['DateTime']->timestamp;
            });

            $pendingIn = null;

            foreach ($events as $event) {
                $dt    = $event['DateTime'];
                $state = $event['State'];
                $name  = $event['Name'];

                if ($state === 'C/IN') {
                    if ($pendingIn !== null) {
                        // We already had an unmatched "In" → save it with null Clock_Out
                        ClockingDataTable::create([
                            'AC_No'        => $pendingIn['AC_No'],
                            'Name'         => $pendingIn['Name'],
                            'Date'         => $pendingIn['DateTime']->toDateString(),
                            'Clock_In'     => $pendingIn['DateTime']->toTimeString(),
                            'Clock_Out'    => null,
                            'Entry_Number' => $entryNumber,
                        ]);
                    }
                    // Now set this as the new pending "In"
                    $pendingIn = [
                        'AC_No'    => $acNo,
                        'Name'     => $name,
                        'DateTime' => $dt,
                    ];
                }
                else if ($state === 'C/OUT') {
                    if ($pendingIn !== null) {
                        // We have a pending "In" → pair them
                        ClockingDataTable::create([
                            'AC_No'        => $pendingIn['AC_No'],
                            'Name'         => $pendingIn['Name'],
                            'Date'         => $pendingIn['DateTime']->toDateString(),
                            'Clock_In'     => $pendingIn['DateTime']->toTimeString(),
                            'Clock_Out'    => $dt->toTimeString(),
                            'Entry_Number' => $entryNumber,
                        ]);
                        // Clear pendingIn
                        $pendingIn = null;
                    }
                    // If pendingIn is null, this is a "C/Out" without "C/In" → skip
                }
            }
             // third After iterating, if there’s still an unmatched "In" → store with null Clock_Out
             if ($pendingIn !== null) {
                ClockingDataTable::create([
                    'AC_No'        => $pendingIn['AC_No'],
                    'Name'         => $pendingIn['Name'],
                    'Date'         => $pendingIn['DateTime']->toDateString(),
                    'Clock_In'     => $pendingIn['DateTime']->toTimeString(),
                    'Clock_Out'    => null,
                    'Entry_Number' => $entryNumber,
                ]);
                $pendingIn = null;
            }
        }

    }

    /***************************** Exporting *********************************/


    public function exportcsv()
    {
        // 1. Fetch all records. If you want to filter (e.g. by Entry_Number), replace with a where() clause.
        $records = ClockingDataTable::all();

        // 2. Define a filename that includes a timestamp
        $fileName = 'clocking_data_' . now()->format('Ymd_His') . '.csv';

        // 3. Prepare HTTP headers for CSV download
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        // 4. Callback to stream CSV content row by row
        $callback = function() use ($records) {
            // Open output stream
            $handle = fopen('php://output', 'w');

            // Write the column headers as the first row
            fputcsv($handle, [
                'ID',
                'AC_No',
                'Name',
                'Date',
                'Clock_In',
                'Clock_Out',
                'Entry_Number',
                'Created_At',
                'Updated_At',
            ]);

            // Write each record’s data
            foreach ($records as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->AC_No,
                    $row->Name,
                    $row->Date,
                    $row->Clock_In,
                    $row->Clock_Out,
                    $row->Entry_Number,
                    $row->created_at,
                    $row->updated_at,
                ]);
            }

            // Close the output stream
            fclose($handle);
        };

        // 5. Return a streamed download response
        return Response::stream($callback, 200, $headers);
    }

    public function exportjson(){
         // 1. Fetch all records (or add a where clause if needed)
        $records = ClockingDataTable::all();

        // 2. Return as JSON. Laravel automatically sets application/json header.
        return response()->json([
            'status'  => 'success',
            'count'   => $records->count(),
            'results' => $records,
        ], 200);
    }


}
