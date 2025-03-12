<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapsData;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCapsDataController extends Controller
{
    /**
     * Export all Caps data as JSON.
     */
    public function create(Request $request)
    {
        // Retrieve all records from the CapsData table
        $capsData = CapsData::all();

        return response()->json([
            'message' => 'Caps data exported as JSON successfully',
            'data'    => $capsData
        ], 200);
    }

    /**
     * Export all Caps data as CSV.
     */
    public function update(Request $request)
    {
        // Retrieve all records from the CapsData table
        $capsData = CapsData::all();
        $filename = "caps_data_export.csv";

        $response = new StreamedResponse(function() use ($capsData) {
            $handle = fopen('php://output', 'w');

            if ($capsData->isNotEmpty()) {
                // Write CSV header using the keys from the first record
                $headers = array_keys($capsData->first()->toArray());
                fputcsv($handle, $headers);

                // Write each record as a CSV row
                foreach ($capsData as $data) {
                    fputcsv($handle, $data->toArray());
                }
            } else {
                // No data found, write an empty header
                fputcsv($handle, []);
            }

            fclose($handle);
        }, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}"
        ]);

        return $response;
    }

    /**
     * Export all Caps data as XML.
     */
    public function destroy(Request $request)
    {
        // Retrieve all records from the CapsData table
        $capsData = CapsData::all();
        $xml = new \SimpleXMLElement('<CapsDataExport/>');

        foreach ($capsData as $data) {
            $item = $xml->addChild('CapsData');
            foreach ($data->toArray() as $key => $value) {
                // Use htmlspecialchars to escape any special characters
                $item->addChild($key, htmlspecialchars($value));
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    }
}
