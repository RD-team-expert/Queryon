<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthPlan;

class Health_Plan_Controller extends Controller
{
    //index
    public function create(Request $request){
        $data = json_decode($request->getContent(), true);


        $modeldata = HealthPlan::create([
            'first_name'=>$data['jsonName']['First']?? null,
            'last_name'=>$data['jsonName']['Last']?? null,
            'email'=>$data['jsonEmailAdress']?? null,
            'store'=>$data['jsonStore']['Label']?? null,
            'onboarding_offboarding'=>$data['jsonGroupManager']['jsonOnboardingOffboarding']?? null,
            'working_start_date'=>$data['jsonGroupManager']['jsonWorkingStartDate']?? null,
            'working_end_date'=>$data['jsonGroupManager']['jsonWorkingEndDate']?? null,
            'reason'=>$data['jsonGroupManager']['jsonReason']?? null,
            'form_id'=>$data['Entry']['Number']?? null,
        ]);

        return response()->json([
            'message' => 'healthplan data created successfully',
            'data'    => $modeldata
        ], 201);
    }
    public function update(Request $request)
    {
        // Decode incoming JSON payload
        $data = json_decode($request->getContent(), true);

        // 1) Ensure Cognito_ID (form_id) is present
        $cognitoId = $data['Entry']['Number'] ?? null;
        if (!$cognitoId) {
            return response()->json(['message' => 'Cognito_ID not provided'], 400);
        }

        // 2) Look up the existing HealthPlan by form_id
        $healthPlan = HealthPlan::where('form_id', $cognitoId)->first();
        if (!$healthPlan) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // 3) Prepare only the fields you intend to update
        $updateData = [
            'first_name'               => $data['jsonName']['First']                    ?? null,
            'last_name'                => $data['jsonName']['Last']                     ?? null,
            'email'                    => $data['jsonEmailAdress']                      ?? null,// note spelling
            'store'                    => $data['jsonStore']['Label']                   ?? null,
            'onboarding_offboarding'   => $data['jsonGroupManager']['jsonOnboardingOffboarding'] ?? null,
            'working_start_date'       => $data['jsonGroupManager']['jsonWorkingStartDate']   ?? null,
            'working_end_date'         => $data['jsonGroupManager']['jsonWorkingEndDate']     ?? null,
            'reason'                   => $data['jsonGroupManager']['jsonReason']             ?? null,
            // you already have form_id—no need to reassign it
        ];

        // 4) Run the update
        $healthPlan->update($updateData);

        // 5) Return the refreshed model
        return response()->json([
            'message' => 'HealthPlan data updated successfully',
            'data'    => $healthPlan->fresh(),
        ], 200);
    }

    public function delete(Request $request){
        $data = json_decode($request->getContent(), true);

        // Ensure Cognito_ID is provided
        $cognitoId = $data['Entry']['Number'] ?? null;
        if (is_null($cognitoId)) {
            return response()->json(['message' => 'form_id not provided'], 400);
        }

        // Find the record by Cognito_ID
        $employeeData = HealthPlan::where('form_id', $cognitoId)->first();
        if (!$employeeData) {
            return response()->json(['message' => 'healthplan not found'], 404);
        }

        // Delete the record
        $employeeData->delete();

        return response()->json([
            'message' => 'healthplan data deleted successfully'
        ], 200);
    }

    //export
    public function exportToCsv(Request $request)
    {
        $fileName = 'HealthPlan_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');
            // optional: add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'first_name',
                'last_name',
                'email',
                'store',
                'onboarding_offboarding',
                'working_start_date',
                'working_end_date',
                'reason',
            ]);

            HealthPlan::chunk(500, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->first_name,
                        $row->last_name,
                        $row->email,
                        $row->store,
                        $row->onboarding_offboarding,
                        $row->working_start_date,
                        $row->working_end_date,
                        $row->reason,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function getData(Request $request){

    }
}
