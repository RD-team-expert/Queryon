<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class CognitoComplaintWebhookController extends Controller
{
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

        Log::info('Cognito complaint CREATE', [
            'entry_number' => $entryNumber,
        ]);

        $complaint = Complaint::create($this->mapData($data));

        return response()->json([
            'status' => 'created',
            'id' => $complaint->id,
        ]);
    }

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

        Log::info('Cognito complaint UPDATE', [
            'entry_number' => $entryNumber,
        ]);

        $complaint = Complaint::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'updated',
            'id' => $complaint->id,
        ]);
    }

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

        Log::info('Cognito complaint DELETE', [
            'entry_number' => $entryNumber,
        ]);

        Complaint::where(
            'external_entry_number',
            $entryNumber
        )->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    public function exportCsv()
    {
        $complaints = Complaint::orderByDesc('id')->get();

        $headers = [
            'Entry Number',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Issue',
            'Suggestion',
            'Manager Informed',
            'Complaint Date',
            'Submitted At',
        ];

        $filename = 'complaints_export_'.now()->format('Y-m-d_H-i-s').'.csv';

        return Response::streamDownload(function () use ($complaints, $headers) {

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM (important for Excel)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($complaints as $complaint) {

                fputcsv($handle, [
                    $complaint->external_entry_number,
                    $complaint->first_name,
                    $complaint->last_name,
                    $complaint->email,
                    $complaint->phone,
                    $complaint->issue,
                    $complaint->suggestion,
                    $complaint->manager_informed,
                    optional($complaint->complaint_date)->format('Y-m-d'),
                    optional($complaint->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);

        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Map Cognito payload to DB fields
     */
    private function mapData(array $data): array
    {
        return [
            'external_entry_number' => data_get($data, 'Entry.Number'),

            'issue' => data_get($data, 'YourComplaint.WhatIssueOrConcernWouldYouLikeToAddress'),

            'suggestion' => data_get($data, 'YourComplaint.DoYouHaveAnySuggestionsForResolvingThisIssue'),

            'first_name' => data_get($data, 'YourComplaint.YourName.First'),
            'last_name' => data_get($data, 'YourComplaint.YourName.Last'),

            'phone' => data_get($data, 'YourComplaint.Phone'),
            'email' => data_get($data, 'YourComplaint.Email'),

            'complaint_date' => data_get($data, 'YourComplaint.Date'),

            'manager_informed' => data_get(
                $data,
                'YourComplaint.HasTheStoreManagerBeenInformedOfThisComplaint'
            ),
        ];
    }
}
