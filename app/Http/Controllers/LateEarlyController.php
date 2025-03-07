<?php

namespace App\Http\Controllers;

use App\Models\Late_Early_Model;
use Illuminate\Http\Request;

class LateEarlyController extends Controller
{
    // Store function to insert data into the table
    public function store(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Extract data and map to database columns
        $lateEarly = Late_Early_Model::create([
            'HookDirectManagerName_ID' => $data['HookManagerAprove']['HookDirectManagerName']['Id'] ?? null,
            'HookDirectManagerName_Lable' => $data['HookManagerAprove']['HookDirectManagerName']['Label'] ?? null,
            'HookApprove' => $data['HookManagerAprove']['HookApprove'] ?? null,
            'HookNote' => $data['HookManagerAprove']['HookNote'] ?? null,
            'HookName_ID' => $data['HookMain']['HookName']['Id'] ?? null,
            'HookName_Lable' => $data['HookMain']['HookName']['Label'] ?? null,
            'HookTodaysDate' => $data['HookMain']['HookTodaysDate'] ?? null,
            'HookPleaseProvideAReasonForYourRequest' => $data['HookMain']['HookPleaseProvideAReasonForYourRequest'] ?? null,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $data['HookMain']['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? null,
            'HookComingLateLeavingEarlier' => $data['HookMain']['HookComingLateLeavingEarlier'] ?? null,
            'HookDepartment_ID' => $data['HookMain']['HookDepartment']['Id'] ?? null,
            'HookDepartment_Lable' => $data['HookMain']['HookDepartment']['Label'] ?? null,
            'HookComingHour' => $data['HookMain']['HookComingHour'] ?? null,
            'HookLeavingHour' => $data['HookMain']['HookLeavingHour'] ?? null,
            'HookShift2' => $data['HookMain']['HookShift2'] ?? null,
            'HookChangeSift' => json_encode($data['HookMain']['HookChangeSift'] ?? []),
            'HookStartAt' => $data['HookMain']['HookStartAt'] ?? null,
            'HookEndAt' => $data['HookMain']['HookEndAt'] ?? null,
            'AdminLink' => $data['Entry']['AdminLink'] ?? null,
            'DateCreated' => $data['Entry']['DateCreated'] ?? null,
            'DateSubmitted' => $data['Entry']['DateSubmitted'] ?? null,
            'DateUpdated' => $data['Entry']['DateUpdated'] ?? null,
            'EntryNumber' => $data['Entry']['Number'] ?? null,
            'PublicLink' => $data['Entry']['PublicLink'] ?? null,
            'InternalLink' => $data['Entry']['InternalLink'] ?? null,
        ]);

        return response()->json(['message' => 'Record stored successfully!', 'data' => $lateEarly], 201);
    }

    // Update function to update an existing record
    public function update(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by Cognito_ID
        $lateEarly = Late_Early_Model::where('EntryNumber', $data['Entry']['Number'])->first();

        if (!$lateEarly) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Update the record with the new data
        $lateEarly->update([
            'HookDirectManagerName_ID' => $data['HookManagerAprove']['HookDirectManagerName']['Id'] ?? null,
            'HookDirectManagerName_Lable' => $data['HookManagerAprove']['HookDirectManagerName']['Label'] ?? null,
            'HookApprove' => $data['HookManagerAprove']['HookApprove'] ?? null,
            'HookNote' => $data['HookManagerAprove']['HookNote'] ?? null,
            'HookName_ID' => $data['HookMain']['HookName']['Id'] ?? null,
            'HookName_Lable' => $data['HookMain']['HookName']['Label'] ?? null,
            'HookTodaysDate' => $data['HookMain']['HookTodaysDate'] ?? null,
            'HookPleaseProvideAReasonForYourRequest' => $data['HookMain']['HookPleaseProvideAReasonForYourRequest'] ?? null,
            'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA' => $data['HookMain']['HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA'] ?? null,
            'HookComingLateLeavingEarlier' => $data['HookMain']['HookComingLateLeavingEarlier'] ?? null,
            'HookDepartment_ID' => $data['HookMain']['HookDepartment']['Id'] ?? null,
            'HookDepartment_Lable' => $data['HookMain']['HookDepartment']['Label'] ?? null,
            'HookComingHour' => $data['HookMain']['HookComingHour'] ?? null,
            'HookLeavingHour' => $data['HookMain']['HookLeavingHour'] ?? null,
            'HookShift2' => $data['HookMain']['HookShift2'] ?? null,
            'HookChangeSift' => json_encode($data['HookMain']['HookChangeSift'] ?? []),
            'HookStartAt' => $data['HookMain']['HookStartAt'] ?? null,
            'HookEndAt' => $data['HookMain']['HookEndAt'] ?? null,
            'AdminLink' => $data['Entry']['AdminLink'] ?? null,
            'DateCreated' => $data['Entry']['DateCreated'] ?? null,
            'DateSubmitted' => $data['Entry']['DateSubmitted'] ?? null,
            'DateUpdated' => $data['Entry']['DateUpdated'] ?? null,
            'EntryNumber' => $data['Entry']['Number'] ?? null,
            'PublicLink' => $data['Entry']['PublicLink'] ?? null,
            'InternalLink' => $data['Entry']['InternalLink'] ?? null,
        ]);

        return response()->json(['message' => 'Record updated successfully!', 'data' => $lateEarly], 200);
    }

    // Destroy function to delete a record by Cognito_ID
    public function destroy(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by Cognito_ID
        $lateEarly = Late_Early_Model::where('EntryNumber', $data['Entry']['Number'])->first();

        if (!$lateEarly) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Delete the record
        $lateEarly->delete();

        return response()->json(['message' => 'Record deleted successfully!'], 200);
    }
}
