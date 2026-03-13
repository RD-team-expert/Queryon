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
            'form_id',
            'form_internal_name',
            'form_name',
            'form_url_name',
            'improvement_feedback',
            'first_name',
            'last_name',
            'full_name',
            'store_label',
            'valued_respected_appreciated',
            'valued_respected_appreciated_rating',
            'work_schedule_satisfaction',
            'work_schedule_satisfaction_rating',
            'gm_email',
            'director_email',
            'entry_status',
            'submitted_at',
            'updated_at_external',
            'created_at',
        ];

        $filename = 'feedback_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Response::streamDownload(function () use ($feedbackRows, $headers) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($feedbackRows as $row) {
                fputcsv($handle, [
                    $row->external_entry_number,
                    $row->form_id,
                    $row->form_internal_name,
                    $row->form_name,
                    $row->form_url_name,
                    $row->improvement_feedback,
                    $row->first_name,
                    $row->last_name,
                    $row->full_name,
                    $row->store_label,
                    $row->valued_respected_appreciated,
                    $row->valued_respected_appreciated_rating,
                    $row->work_schedule_satisfaction,
                    $row->work_schedule_satisfaction_rating,
                    $row->gm_email,
                    $row->director_email,
                    $row->entry_status,
                    optional($row->submitted_at)->format('Y-m-d H:i:s'),
                    optional($row->updated_at_external)->format('Y-m-d H:i:s'),
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

            'form_id' => data_get($data, 'Form.Id'),
            'form_internal_name' => data_get($data, 'Form.InternalName'),
            'form_name' => data_get($data, 'Form.Name'),
            'form_url_name' => data_get($data, 'Form.UrlName'),

            'improvement_feedback' => data_get($data, 'YourFeedback.WhatIsOneThingWeCanDoToImproveYourExperience'),

            'first_name' => data_get($data, 'YourFeedback.YourName.First'),
            'last_name' => data_get($data, 'YourFeedback.YourName.Last'),
            'full_name' => data_get($data, 'YourFeedback.YourName.FirstAndLast'),

            'store_label' => data_get($data, 'YourFeedback.Store.Label'),

            'valued_respected_appreciated' => data_get($data, 'YourFeedback.OutOf5Stars.DoYouFeelValuedRespectedAndAppreciated'),
            'valued_respected_appreciated_rating' => data_get($data, 'YourFeedback.OutOf5Stars.DoYouFeelValuedRespectedAndAppreciated_Rating'),

            'work_schedule_satisfaction' => data_get($data, 'YourFeedback.OutOf5Stars.HowSatisfiedAreYouWithTheConsistencyOfYourWorkScheduleAndHours2'),
            'work_schedule_satisfaction_rating' => data_get($data, 'YourFeedback.OutOf5Stars.HowSatisfiedAreYouWithTheConsistencyOfYourWorkScheduleAndHours2_Rating'),

            'gm_email' => data_get($data, 'CP.GMs'),
            'director_email' => data_get($data, 'CP.Director'),

            'entry_status' => data_get($data, 'Entry.Status'),
            'submitted_at' => data_get($data, 'Entry.DateSubmitted'),
            'updated_at_external' => data_get($data, 'Entry.DateUpdated'),

            'payload' => $data,
        ];
    }
}