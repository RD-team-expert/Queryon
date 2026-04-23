<?php

namespace App\Http\Controllers;

use App\Models\FieldMissionModels\FieldMission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class FeildMessionController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->json()->all();
        $entryId = (string) data_get($data, 'Id');

        if (! $entryId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry ID missing',
            ], 400);
        }

        DB::beginTransaction();

        try {

            $record = FieldMission::updateOrCreate(
                ['entry_id' => $entryId],
                $this->mapData($data)
            );

            // 🔥 Handle Maintenance-specific logic
            $this->handleMaintenance($record, $data);

            DB::commit();

            return response()->json([
                'status' => 'created',
                'data' => $record,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        $entryId = (string) data_get($data, 'Id');

        if (! $entryId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry ID missing',
            ], 400);
        }

        DB::beginTransaction();

        try {

            $record = FieldMission::updateOrCreate(
                ['entry_id' => $entryId],
                $this->mapData($data)
            );

            $this->handleMaintenance($record, $data);

            DB::commit();

            return response()->json([
                'status' => 'updated',
                'data' => $record,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $data = $request->json()->all();
        $entryId = (string) data_get($data, 'Id');

        if (! $entryId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry ID missing',
            ], 400);
        }

        FieldMission::where('entry_id', $entryId)->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    public function export()
    {
        $records = FieldMission::orderByDesc('id')
            ->get();

        $headers = [
            'entry_id',
            'team',
            'my_name',
            'payment_for_week',
            'employee_name',
            'total_hour',
            'hour_pay',
            'mony_owed',
            'total_pay',
            'total_deduction',
            'net_pay',
            'miles2',
            'fuel',
            'submitted_at',
        ];

        $filename = 'field_missions'.now()->format('Y-m-d_H-i-s').'.csv';

        return Response::streamDownload(function () use ($records, $headers) {
            $out = fopen('php://output', 'w');

            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);

            foreach ($records as $record) {
                fputcsv($out, [
                    $record->entry_id,
                    $record->team,
                    $record->finance_name,
                    $record->payment_for_week,
                    $record->employee_name,
                    $record->total_hour,
                    $record->hour_pay,
                    $record->mony_owed,
                    $record->total_pay,
                    $record->total_deduction,
                    $record->net_pay,
                    $record->miles2,
                    $record->fuel,
                    optional($record->submitted_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function mapData(array $data): array
    {
        $team = data_get($data, 'FinanceMaintenance.Team');

        $base = [
            'entry_id' => (string) data_get($data, 'Id'),
            'team' => $team,
            'finance_name' => data_get($data, 'FinanceMaintenance.YourName.FirstAndLast'),
            'payment_for_week' => data_get($data, 'FinanceMaintenance.ThePaymentIsForWeek'),
            'submitted_at' => $this->parseDate(data_get($data, 'Entry.DateSubmitted')),
        ];

        if ($team === 'USA MGMT') {
            return array_merge($base, [
                'employee_name' => data_get($data, 'FinanceMaintenance.USAMGMT.EmployeeFullName.FirstAndLast'),
                'total_hour' => data_get($data, 'FinanceMaintenance.USAMGMT.TotalHours2'),
                'hour_pay' => data_get($data, 'FinanceMaintenance.USAMGMT.HourlyPay'),
                'mony_owed' => data_get($data, 'FinanceMaintenance.USAMGMT.MoneyOwed'),
                'total_pay' => data_get($data, 'FinanceMaintenance.USAMGMT.TotalPayWithoutDeductions'),
                'total_deduction' => data_get($data, 'FinanceMaintenance.USAMGMT.TotalDeductions'),
                'net_pay' => data_get($data, 'FinanceMaintenance.USAMGMT.NetPay'),
            ]);
        }

        return array_merge($base, [
            'employee_name' => data_get($data, 'FinanceMaintenance.Maintenance.EmployeeFullName.FirstAndLast'),
            'total_hour' => data_get($data, 'FinanceMaintenance.Maintenance.TotalHours2'),
            'hour_pay' => data_get($data, 'FinanceMaintenance.Maintenance.HourlyPay'),
            'mony_owed' => data_get($data, 'FinanceMaintenance.Maintenance.MoneyOwed'),
            'total_pay' => data_get($data, 'FinanceMaintenance.Maintenance.TotalPayWithoutDeductions'),
            'total_deduction' => data_get($data, 'FinanceMaintenance.Maintenance.TotalDeductions'),
            'net_pay' => data_get($data, 'FinanceMaintenance.Maintenance.NetPay'),
        ]);
    }

    private function parseDate($value)
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function handleMaintenance($record, array $data): void
    {
        $team = data_get($data, 'FinanceMaintenance.Team');

        // 🔥 always reset invoices first
        $record->invoices()->delete();

        if ($team !== 'Maintenance') {
            $record->update([
                'fuel' => null,
                'miles2' => null,
            ]);

            return;
        }

        $record->update([
            'fuel' => data_get($data, 'FinanceMaintenance.Maintenance.FuelIfThereIsAny'),
            'miles2' => data_get($data, 'FinanceMaintenance.Maintenance.MilesIfThereIsAny2'),
        ]);

        foreach (data_get($data, 'FinanceMaintenance.Maintenance.InvoicesIfThereIsAny', []) as $inv) {
            $record->invoices()->create([
                'file_name' => $inv['Name'],
                'file_url' => $inv['File'],
            ]);
        }
    }
}
