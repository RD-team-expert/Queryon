<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EmployeeTransitionWebhookController extends Controller
{
    /**
     * Create a new employee transition.
     */
    public function create(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Employee Transition CREATE', [
            'entry_number' => $entryNumber,
        ]);

        $transition = EmployeeTransition::create($this->mapData($data));

        return response()->json([
            'status' => 'created',
            'id' => $transition->id,
        ]);
    }

    /**
     * Update an existing employee transition.
     */
    public function update(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Employee Transition UPDATE', [
            'entry_number' => $entryNumber,
        ]);

        $transition = EmployeeTransition::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'updated',
            'id' => $transition->id,
        ]);
    }

    /**
     * Delete an employee transition.
     */
    public function delete(Request $request)
    {
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry number missing',
            ], 400);
        }

        Log::info('Employee Transition DELETE', [
            'entry_number' => $entryNumber,
        ]);

        EmployeeTransition::where(
            'external_entry_number',
            $entryNumber
        )->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    /**
     * Export employee transitions to CSV.
     */
    public function exportCsv()
    {
        $transitions = EmployeeTransition::orderByDesc('id')->get();

        $headers = [
            'Entry Number',
            'Store Manager Name',
            'Employee Full Name',
            'From Store',
            'To Store',
            'Hours',
            'Transition Date',
            'Submitted At',
        ];

        $filename = 'employee_transitions_export_'.now()->format('Y-m-d_H-i-s').'.csv';

        return Response::streamDownload(function () use ($transitions, $headers) {

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM (important for Excel)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($transitions as $transition) {

                fputcsv($handle, [
                    $transition->external_entry_number,
                    $transition->store_manager_name,
                    $transition->employee_full_name,
                    $transition->from_store,
                    $transition->to_store,
                    $transition->hours,
                    optional($transition->transition_date)->format('Y-m-d'),
                    optional($transition->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);

        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Map the Cognito payload to the DB fields.
     */
    private function mapData(array $data): array
    {
        return [
            'external_entry_number' => data_get($data, 'Entry.Number'),
            'store_manager_name' => data_get($data, 'StoreManagerName.FirstAndLast'),
            'employee_full_name' => data_get($data, 'SM.EmployeeFullName.FirstAndLast'),
            'from_store' => data_get($data, 'SM.FromStore.Label'),
            'to_store' => data_get($data, 'SM.ToStore.Label'),
            'hours' => data_get($data, 'SM.Hours'),
            'transition_date' => data_get($data, 'SM.TransitionDate'),
        ];
    }
}
