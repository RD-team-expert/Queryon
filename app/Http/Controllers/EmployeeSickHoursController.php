<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSickHoursTransition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EmployeeSickHoursController extends Controller
{
    // CREATE / STORE
    public function store(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Entry.Number');

        if (! $entryNumber) {
            return response()->json(['success' => false, 'message' => 'Entry number missing'], 400);
        }

        $payload = [
            'external_entry_number' => data_get($data, 'Id'),
            'store_label' => data_get($data, 'Store.Label'),
            'store_manager_name' => data_get($data, 'StoreManagerName.FirstAndLast'),
            'employee_name' => data_get($data, 'EmployeeName.FirstAndLast'),
            'date' => data_get($data, 'Date'),
            'amount_of_sick_hours' => data_get($data, 'AmountOfSickHours'),
        ];

         foreach ($payload as $key => $value) {
            if ($value === null) {
                return response()->json(['success' => false, 'message' => "$key is required"], 422);
            }
        }

        try {
            $record = EmployeeSickHoursTransition::updateOrCreate(
                ['external_entry_number' => $entryNumber],
                $payload
            );
            return response()->json(['success' => true, 'data' => $record], 201);
        } catch (\Throwable $e) {
            Log::error('EmployeeSickHour STORE ERROR: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // UPDATE
    public function update(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Id');

        if (! $entryNumber) {
            return response()->json(['success' => false, 'message' => 'Entry number missing'], 400);
        }

        $payload = [
            'store_label' => data_get($data, 'Store.Label'),
            'store_manager_name' => data_get($data, 'StoreManagerName.FirstAndLast'),
            'employee_name' => data_get($data, 'EmployeeName.FirstAndLast'),
            'date' => data_get($data, 'Date'),
            'amount_of_sick_hours' => data_get($data, 'AmountOfSickHours'),
        ];

        // التحقق من جميع الحقول required
        foreach ($payload as $key => $value) {
            if ($value === null) {
                return response()->json(['success' => false, 'message' => "$key is required"], 422);
            }
        }

        try {
            $record = EmployeeSickHoursTransition::updateOrCreate(
                ['external_entry_number' => $entryNumber],
                $payload
            );
            return response()->json(['success' => true, 'data' => $record], 200);
        } catch (\Throwable $e) {
            Log::error('EmployeeSickHour UPDATE ERROR: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE
    public function delete(Request $request): JsonResponse
    {
        log::info('EmployeeSickHour DELETE REQUEST', ['payload' => $request->json()->all()]);
        $data = $request->json()->all();
        $entryNumber = data_get($data, 'Id');
         if (! $entryNumber) {
            return response()->json(['success' => false, 'message' => 'Entry number missing'], 400);
        }

        try {
            $deleted = EmployeeSickHoursTransition::where('external_entry_number', $entryNumber)->delete();
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Record deleted'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }
        } catch (\Throwable $e) {
            Log::error('EmployeeSickHour DELETE ERROR: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // EXPORT CSV
    public function exportCsv()
    {
        try {
            $records = EmployeeSickHoursTransition::orderByDesc('id')->get();

            $filename = 'employee_sick_hours_'.now()->format('Y-m-d_H-i-s').'.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return Response::streamDownload(function () use ($records) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM for Excel
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($handle, ['Entry Number','Store','Store Manager','Employee Name','Date','Amount of Sick Hours']);

                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row->external_entry_number,
                        $row->store_label,
                        $row->store_manager_name,
                        $row->employee_name,
                        $row->date,
                        $row->amount_of_sick_hours,
                    ]);
                }

                fclose($handle);
            }, $filename, $headers);

        } catch (\Throwable $e) {
            Log::error('EmployeeSickHour EXPORT ERROR: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}