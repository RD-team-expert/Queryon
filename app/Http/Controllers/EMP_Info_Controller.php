<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EMPInfo;

class EMP_Info_Controller extends Controller
{
   //store function
    public function store(Request $request)
    {
        // Decode JSON data from the request body into an array
        $data = json_decode($request->getContent(), true);

        // Extract nested data (using null coalescing to account for missing values)
        $generalData = $data['GeneralData'] ?? [];
        $englishName = $generalData['EnglishName'] ?? [];
        $arabicName = $generalData['ArabicName'] ?? [];
        $workData = $data['WorkData'] ?? [];
        $internal = $data['Internal'] ?? [];
        $entry = $data['Entry'] ?? [];

        // Prepare the data for insertion, noting that all fields are nullable
        $empInfoData = [
            'First_Name_English'         => $englishName['First']         ?? null,
            'Last_Name_English'          => $englishName['Last']          ?? null,
            'First_And_Last_Name_English'=> $englishName['FirstAndLast']    ?? null,
            'First_Name_Arabic'          => $arabicName['First']          ?? null,
            'Last_Name_Arabic'           => $arabicName['Last']           ?? null,
            'First_And_Last_Name_Arabic' => $arabicName['FirstAndLast']   ?? null,
            'HiringDate'                 => $generalData['HiringDate']    ?? null,
            'PneEmail'                   => $generalData['PneEmail']      ?? null,
            'PersonalEmail'              => $generalData['PersonalEmail'] ?? null,
            'SYPhone'                    => $generalData['SYPhone']       ?? null,
            'USPhone'                    => $generalData['USPhone']       ?? null,
            // Store YourPicture as JSON if it's an array (adjust if you plan on a different implementation)
            'YourPicture'                => isset($generalData['YourPicture']) ? json_encode($generalData['YourPicture']) : null,
            'AboutYou'                   => $generalData['AboutYou']      ?? null,
            // Hash the password before storing (ensure you import bcrypt)
            'Password'                   => isset($generalData['Password']) ? bcrypt($generalData['Password']) : null,
            'Shift'                      => $workData['Shift']            ?? null,
            // Extract the department and position labels if available
            'Depatment_Name'             => isset($workData['Depatment']) ? $workData['Depatment']['Label'] : null,
            'Position_Name'              => isset($workData['Position']) ? $workData['Position']['Label'] : null,
            // Convert Offboarded ("No"/"Yes") to boolean: false for "No", true for "Yes"
            'Offboarded'                 => (isset($internal['Offboarded']) && strtolower($internal['Offboarded']) === 'yes') ? true : false,
            'Level'                      => $internal['Level']            ?? null,
            'Tier'                       => $internal['Tier']             ?? null,
            'Entry_Number'               => $entry['Number']              ?? null,
        ];

        // Insert the data into the EMP_Info table using the EMPInfo model
        $empInfo = EMPInfo::create($empInfoData);

        return response()->json([
            'success' => true,
            'data'    => $empInfo
        ], 201);
    }
   //Update function
   public function update(Request $request)
   {
       // Decode JSON data into an array
       $data = json_decode($request->getContent(), true);

       // Extract the entry data to get the Entry Number (used to identify the record)
       $entryData = $data['Entry'] ?? [];
       $entryNumber = $entryData['Number'] ?? null;

       if (!$entryNumber) {
           return response()->json([
               'success' => false,
               'message' => 'Entry number is required for update.'
           ], 400);
       }

       // Find the record based on Entry_Number
       $empInfo = EMPInfo::where('Entry_Number', $entryNumber)->first();
       if (!$empInfo) {
           return response()->json([
               'success' => false,
               'message' => 'Record not found.'
           ], 404);
       }

       // Extract nested data for update
       $generalData = $data['GeneralData'] ?? [];
       $englishName = $generalData['EnglishName'] ?? [];
       $arabicName = $generalData['ArabicName'] ?? [];
       $workData = $data['WorkData'] ?? [];
       $internal = $data['Internal'] ?? [];

       // Update only if new input exists; otherwise, keep previous data.
       $empInfo->First_Name_English = (isset($englishName['First']) && !empty($englishName['First']))
           ? $englishName['First']
           : $empInfo->First_Name_English;

       $empInfo->Last_Name_English = (isset($englishName['Last']) && !empty($englishName['Last']))
           ? $englishName['Last']
           : $empInfo->Last_Name_English;

       $empInfo->First_And_Last_Name_English = (isset($englishName['FirstAndLast']) && !empty($englishName['FirstAndLast']))
           ? $englishName['FirstAndLast']
           : $empInfo->First_And_Last_Name_English;

       $empInfo->First_Name_Arabic = (isset($arabicName['First']) && !empty($arabicName['First']))
           ? $arabicName['First']
           : $empInfo->First_Name_Arabic;

       $empInfo->Last_Name_Arabic = (isset($arabicName['Last']) && !empty($arabicName['Last']))
           ? $arabicName['Last']
           : $empInfo->Last_Name_Arabic;

       $empInfo->First_And_Last_Name_Arabic = (isset($arabicName['FirstAndLast']) && !empty($arabicName['FirstAndLast']))
           ? $arabicName['FirstAndLast']
           : $empInfo->First_And_Last_Name_Arabic;

       $empInfo->HiringDate = (isset($generalData['HiringDate']) && !empty($generalData['HiringDate']))
           ? $generalData['HiringDate']
           : $empInfo->HiringDate;

       $empInfo->PneEmail = (isset($generalData['PneEmail']) && !empty($generalData['PneEmail']))
           ? $generalData['PneEmail']
           : $empInfo->PneEmail;

       $empInfo->PersonalEmail = (isset($generalData['PersonalEmail']) && !empty($generalData['PersonalEmail']))
           ? $generalData['PersonalEmail']
           : $empInfo->PersonalEmail;

       $empInfo->SYPhone = (isset($generalData['SYPhone']) && !empty($generalData['SYPhone']))
           ? $generalData['SYPhone']
           : $empInfo->SYPhone;

       $empInfo->USPhone = (isset($generalData['USPhone']) && !empty($generalData['USPhone']))
           ? $generalData['USPhone']
           : $empInfo->USPhone;

       // For YourPicture, store as JSON if provided and not empty.
       if (isset($generalData['YourPicture']) && !empty($generalData['YourPicture'])) {
           $empInfo->YourPicture = json_encode($generalData['YourPicture']);
       }

       $empInfo->AboutYou = (isset($generalData['AboutYou']) && !empty($generalData['AboutYou']))
           ? $generalData['AboutYou']
           : $empInfo->AboutYou;

       // Update the password only if provided (hash it)
       if (isset($generalData['Password']) && !empty($generalData['Password'])) {
           $empInfo->Password = bcrypt($generalData['Password']);
       }

       $empInfo->Shift = (isset($workData['Shift']) && !empty($workData['Shift']))
           ? $workData['Shift']
           : $empInfo->Shift;

       if (isset($workData['Depatment']) && !empty($workData['Depatment']['Label'])) {
           $empInfo->Depatment_Name = $workData['Depatment']['Label'];
       }

       if (isset($workData['Position']) && !empty($workData['Position']['Label'])) {
           $empInfo->Position_Name = $workData['Position']['Label'];
       }

       // Update Offboarded from Internal: convert "Yes"/"No" to boolean.
       if (isset($internal['Offboarded']) && !empty($internal['Offboarded'])) {
           $empInfo->Offboarded = (strtolower($internal['Offboarded']) === 'yes') ? true : false;
       }

       $empInfo->Level = (isset($internal['Level']) && !empty($internal['Level']))
           ? $internal['Level']
           : $empInfo->Level;

       $empInfo->Tier = (isset($internal['Tier']) && !empty($internal['Tier']))
           ? $internal['Tier']
           : $empInfo->Tier;

       // Save updated record
       $empInfo->save();

       return response()->json([
           'success' => true,
           'data'    => $empInfo
       ], 200);
   }
   //destroy
   public function destroy(Request $request)
    {
        // Decode JSON data into an array
        $data = json_decode($request->getContent(), true);

        // Extract the entry data to get the Entry Number
        $entryData = $data['Entry'] ?? [];
        $entryNumber = $entryData['Number'] ?? null;

        if (!$entryNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Entry number is required for deletion.'
            ], 400);
        }

        // Find the record based on Entry_Number
        $empInfo = EMPInfo::where('Entry_Number', $entryNumber)->first();

        if (!$empInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        // Delete the record
        $empInfo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ], 200);
    }



}
