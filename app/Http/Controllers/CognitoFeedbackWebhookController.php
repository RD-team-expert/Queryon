<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class CognitoFeedbackWebhookController extends Controller
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

        Log::info('Cognito feedback CREATE', [
            'entry_number' => $entryNumber,
        ]);

        $feedback = Feedback::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'created',
            'id' => $feedback->id,
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

        Log::info('Cognito feedback UPDATE', [
            'entry_number' => $entryNumber,
        ]);

        $feedback = Feedback::updateOrCreate(
            ['external_entry_number' => $entryNumber],
            $this->mapData($data)
        );

        return response()->json([
            'status' => 'updated',
            'id' => $feedback->id,
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

        Log::info('Cognito feedback DELETE', [
            'entry_number' => $entryNumber,
        ]);

        Feedback::where('external_entry_number', $entryNumber)->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    public function exportCsv()
    {
        $feedbackRows = Feedback::orderByDesc('id')->get();

        $headers = [
            'external_entry_number',
            'improvement_feedback',
            'first_name',
            'last_name',
            'valued_respected_appreciated_rating',
            'work_schedule_satisfaction_rating',
            'submitted_at',
        ];

        $filename = 'feedback_export_'.now()->format('Y-m-d_H-i-s').'.csv';

        return Response::streamDownload(function () use ($feedbackRows, $headers) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($feedbackRows as $row) {
                fputcsv($handle, [
                    $row->external_entry_number,
                    $row->improvement_feedback,
                    $row->first_name,
                    $row->last_name,
                    $row->valued_respected_appreciated_rating,
                    $row->work_schedule_satisfaction_rating,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function mapData(array $data): array
    {
        return [
            'external_entry_number' => data_get($data, 'Entry.Number'),

            'improvement_feedback' => data_get($data, 'YourFeedback.WhatIsOneThingWeCanDoToImproveYourExperience'),

            'first_name' => data_get($data, 'YourFeedback.YourName.First'),
            'last_name' => data_get($data, 'YourFeedback.YourName.Last'),

            'valued_respected_appreciated_rating' => data_get($data, 'YourFeedback.OutOf5Stars.DoYouFeelValuedRespectedAndAppreciated_Rating'),

            'work_schedule_satisfaction_rating' => data_get($data, 'YourFeedback.OutOf5Stars.HowSatisfiedAreYouWithTheConsistencyOfYourWorkScheduleAndHours2_Rating'),

        ];
    }
}
