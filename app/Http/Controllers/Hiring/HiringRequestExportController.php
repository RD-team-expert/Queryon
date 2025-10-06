<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\Hiring\HiringRequest;
use App\Models\Hiring\Hire;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HiringRequestExportController extends Controller
{
    public function exportRequests(): StreamedResponse
    {
        $filename = 'hiring_requests_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $columns = [
            'id',
            'first_name',
            'last_name',
            'store',
            'date_of_request',
            'num_of_emp_needed',
            'desired_start_date',
            'additional_notes',
            'supervisors_first_name',
            'supervisors_last_name',
            'supervisors_accept',
            'supervisors_notes',
            'hr_first_name',
            'hr_last_name',
            'hr_num_of_hires',
            'cognito_id',
        ];

        $callback = function () use ($columns) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($out, $columns);

            HiringRequest::query()
                ->orderBy('id')
                ->chunk(1000, function ($chunk) use ($out) {
                    foreach ($chunk as $row) {
                        $data = [
                            $row->id,
                            $row->first_name,
                            $row->last_name,
                            $row->store,  // ADDED: Missing field
                            optional($row->date_of_request)->format('Y-m-d'),  // ADDED: Missing field
                            $row->num_of_emp_needed,
                            optional($row->desired_start_date)->format('Y-m-d'),
                            $row->additional_notes,
                            $row->supervisors_first_name,
                            $row->supervisors_last_name,
                            is_null($row->supervisors_accept) ? '' : ($row->supervisors_accept ? '1' : '0'),
                            $row->supervisors_notes,
                            $row->hr_first_name,
                            $row->hr_last_name,
                            $row->hr_num_of_hires,
                            $row->cognito_id,
                        ];
                        fputcsv($out, $data);
                    }
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportHires(): StreamedResponse
    {
        $filename = 'hiring_hires_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $columns = [
            'id',
            'request_id',
            'emp_first_name',
            'emp_middle_name',
            'emp_last_name',
            'date_of_birth',
            'gender',
            'available_shifts',
            'work_days',
            'alta_clock_in_out_img',
            'paychex_profile_img',
            'paychex_direct_deposit_img',
            'signed_contract_img',
        ];

        $callback = function () use ($columns) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($out, $columns);

            Hire::query()
                ->orderBy('id')
                ->chunk(1000, function ($chunk) use ($out) {
                    foreach ($chunk as $row) {
                        $data = [
                            $row->id,
                            $row->request_id,
                            $row->emp_first_name,
                            $row->emp_middle_name,
                            $row->emp_last_name,
                            optional($row->date_of_birth)->format('Y-m-d'),
                            $row->gender,
                            $row->available_shifts,
                            $row->work_days,
                            $row->alta_clock_in_out_img,
                            $row->paychex_profile_img,
                            $row->paychex_direct_deposit_img,
                            $row->signed_contract_img,
                        ];
                        fputcsv($out, $data);
                    }
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
