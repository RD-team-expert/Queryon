<?php

namespace App\Http\Controllers;

use App\Models\UrgentActionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class UrgentActionRecordController extends Controller
{
    /**
     * CREATE (idempotent)
     */
    public function create(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = (string) data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Cognito urgent action CREATE', [
            'entry_number' => $entryNumber,
        ]);

        $record = UrgentActionRecord::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'created_or_updated',
            'id' => $record->id,
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = (string) data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Cognito urgent action UPDATE', [
            'entry_number' => $entryNumber,
        ]);

        $record = UrgentActionRecord::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'updated',
            'id' => $record->id,
        ]);
    }

    /**
     * DELETE
     */
    public function delete(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = (string) data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Cognito urgent action DELETE', [
            'entry_number' => $entryNumber,
        ]);

        UrgentActionRecord::where('external_entry_number', $entryNumber)->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    /**
     * EXPORT CSV
     */
    public function exportCsv()
    {
        $records = UrgentActionRecord::orderByDesc('id')->get();

        $headers = [
            'Entry Number',
            'Employee First Name',
            'Employee Last Name',
            'Date',
            'Store',
            'Action',
            'Why',
            'Manager First Name',
            'Manager Last Name',
            'Submitted At',
        ];

        $filename = 'urgent_actions_'.now()->format('Y-m-d_H-i-s').'.csv';

        return Response::streamDownload(function () use ($records, $headers) {

            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->external_entry_number,
                    $record->employee_first_name,
                    $record->employee_last_name,
                    $record->today_date,
                    $record->store_label,
                    $record->action_taken,
                    $record->why,
                    $record->manager_first_name,
                    $record->manager_last_name,
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);

        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Map Cognito payload → DB
     */
    private function mapData(array $data): array
    {
        return [
            'external_entry_number' => (string) data_get($data, 'Entry.Number'),

            'employee_first_name' => data_get($data, 'Details.Name.First'),
            'employee_last_name' => data_get($data, 'Details.Name.Last'),

            'today_date' => data_get($data, 'Details.TodaysDate'),

            'store_label' => data_get($data, 'Details.YourStore.Label'),

            'action_taken' => data_get($data, 'Details.WhatIsTheActionThatYouAreTaking'),
            'why' => data_get($data, 'Details.Why'),

            'manager_first_name' => data_get($data, 'Details.NameTheManagerWhoYouConsulted.First'),
            'manager_last_name' => data_get($data, 'Details.NameTheManagerWhoYouConsulted.Last'),
        ];
    }
}
