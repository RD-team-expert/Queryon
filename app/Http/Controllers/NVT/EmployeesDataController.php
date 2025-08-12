<?php

namespace App\Http\Controllers\NVT;

use Illuminate\Http\Request;
use App\Models\EmployeesDataModel;
use App\Http\Controllers\Controller;
class EmployeesDataController extends Controller
{
    public function create(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        // Extract sections from the JSON using the new Hook keys
        $generalData    = $data['HookGeneralData'] ?? [];
        $englishName    = $generalData['HookEnglishName'] ?? [];
        $arabicName     = $generalData['HookArabicName'] ?? [];
        $workData       = $data['HookWorkData'] ?? [];
        $department     = $workData['HookDepatment'] ?? [];
        $position       = $workData['HookPosition'] ?? [];
        $internal       = $data['HookInternal'] ?? [];
        $directManager1 = $internal['HookDirectManagerNameGroupManagerName1'] ?? [];
        $secondLevelMgr = $internal['Hook_2ndLevelManager'] ?? [];
        $dmEmail        = $internal['HookDmEmail'] ?? [];
        $secondEmail    = $internal['Hook_2ndEmail'] ?? [];
        $directManager2 = $internal['HookDirectManagerNameGroupManagerName2'] ?? [];
        $dmEmail2       = $internal['HookDmEmail2'] ?? [];
        $entry          = $data['Entry'] ?? [];

        // Retrieve the image link if available (from the first element in HookYourPicture array)
        $imgLink = null;
        if (isset($generalData['HookYourPicture'][0]['File'])) {
            $imgLink = $generalData['HookYourPicture'][0]['File'];
        }

        // Map the JSON fields to the corresponding table columns
        $employeeData = EmployeesDataModel::create([
            'first_name_english'          => $englishName['First'] ?? null,
            'first_and_last_name_english' => $englishName['FirstAndLast'] ?? null,
            'last_name_english'           => $englishName['Last'] ?? null,
            'first_name_arabic'           => $arabicName['First'] ?? null,
            'first_and_last_name_arabic'  => $arabicName['FirstAndLast'] ?? null,
            'last_name_arabic'            => $arabicName['Last'] ?? null,
            'hiring_date'                 => $generalData['HookHiringDate'] ?? null,
            'pne_email'                   => $generalData['HookPneEmail'] ?? null,
            'personal_email'              => $generalData['HookPersonalEmail'] ?? null,
            'sy_phone'                    => $generalData['HookSYPhone'] ?? null,
            'us_phone'                    => $generalData['HookUSPhone'] ?? null,
            'CLockInOutID'                => $generalData['HookClockInOutID'] ?? null,
            'img_link'                    => $imgLink,
            'about_you'                   => $generalData['HookAboutYou'] ?? null,
            'password2'                   => $generalData['HookPassword2'] ?? null,
            'shift'                       => $workData['HookShift'] ?? null,
            'depatment_id'                => $department['Id'] ?? null,
            'depatment_label'             => $department['Label'] ?? null,
            'position_id'                 => $position['Id'] ?? null,
            'position_label'              => $position['Label'] ?? null,
            'direct_manager1_id'          => $directManager1['Id'] ?? null,
            'direct_manager1_label'       => $directManager1['Label'] ?? null,
            'is_manager'                  => $internal['HookIsManager'] ?? null,
            'second_level_manager_id'     => $secondLevelMgr['Id'] ?? null,
            'second_level_manager_label'  => $secondLevelMgr['Label'] ?? null,
            'dm_email_id'                 => $dmEmail['Id'] ?? null,
            'dm_email_label'              => $dmEmail['Label'] ?? null,
            'second_email_id'             => $secondEmail['Id'] ?? null,
            'second_email_label'          => $secondEmail['Label'] ?? null,
            'offboarded'                  => $internal['HookOffboarded'] ?? null,
            'direct_manager2_id'          => $directManager2['Id'] ?? null,
            'direct_manager2_label'       => $directManager2['Label'] ?? null,
            'dm_email2_id'                => $dmEmail2['Id'] ?? null,
            'dm_email2_label'             => $dmEmail2['Label'] ?? null,
            'level'                       => isset($internal['HookLevel']) ? intval($internal['HookLevel']) : null,
            'tier'                        => isset($internal['HookTier']) ? intval($internal['HookTier']) : null,
            'entry_admin_link'            => $entry['AdminLink'] ?? null,
            'Cognito_ID'                  => $entry['Number'] ?? null,
            'entry_status'                => $entry['Status'] ?? null,
            'entry_public_link'           => $entry['PublicLink'] ?? null,
            'entry_internal_link'         => $entry['InternalLink'] ?? null,
        ]);

        return response()->json([
            'message' => 'Employee data created successfully',
            'data'    => $employeeData
        ], 201);
    }

    public function update(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        // Extract sections from the JSON using the new Hook keys
        $generalData    = $data['HookGeneralData'] ?? [];
        $englishName    = $generalData['HookEnglishName'] ?? [];
        $arabicName     = $generalData['HookArabicName'] ?? [];
        $workData       = $data['HookWorkData'] ?? [];
        $department     = $workData['HookDepatment'] ?? [];
        $position       = $workData['HookPosition'] ?? [];
        $internal       = $data['HookInternal'] ?? [];
        $directManager1 = $internal['HookDirectManagerNameGroupManagerName1'] ?? [];
        $secondLevelMgr = $internal['Hook_2ndLevelManager'] ?? [];
        $dmEmail        = $internal['HookDmEmail'] ?? [];
        $secondEmail    = $internal['Hook_2ndEmail'] ?? [];
        $directManager2 = $internal['HookDirectManagerNameGroupManagerName2'] ?? [];
        $dmEmail2       = $internal['HookDmEmail2'] ?? [];
        $entry          = $data['Entry'] ?? [];

        // Ensure Cognito_ID is provided
        $cognitoId = $entry['Number'] ?? null;
        if (is_null($cognitoId)) {
            return response()->json(['message' => 'Cognito_ID not provided'], 400);
        }

        // Find the employee record using Cognito_ID
        $employeeData = EmployeesDataModel::where('Cognito_ID', $cognitoId)->first();
        if (!$employeeData) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Retrieve the image link if available (from the first element in HookYourPicture array)
        $imgLink = null;
        if (isset($generalData['HookYourPicture'][0]['File'])) {
            $imgLink = $generalData['HookYourPicture'][0]['File'];
        }

        // Prepare update data
        $updateData = [
            'first_name_english'          => $englishName['First'] ?? null,
            'first_and_last_name_english' => $englishName['FirstAndLast'] ?? null,
            'last_name_english'           => $englishName['Last'] ?? null,
            'first_name_arabic'           => $arabicName['First'] ?? null,
            'first_and_last_name_arabic'  => $arabicName['FirstAndLast'] ?? null,
            'last_name_arabic'            => $arabicName['Last'] ?? null,
            'hiring_date'                 => $generalData['HookHiringDate'] ?? null,
            'pne_email'                   => $generalData['HookPneEmail'] ?? null,
            'personal_email'              => $generalData['HookPersonalEmail'] ?? null,
            'sy_phone'                    => $generalData['HookSYPhone'] ?? null,
            'us_phone'                    => $generalData['HookUSPhone'] ?? null,
            'CLockInOutID'                => $generalData['HookClockInOutID'] ?? null,
            'img_link'                    => $imgLink,
            'about_you'                   => $generalData['HookAboutYou'] ?? null,
            'password2'                   => $generalData['HookPassword2'] ?? null,
            'shift'                       => $workData['HookShift'] ?? null,
            'depatment_id'                => $department['Id'] ?? null,
            'depatment_label'             => $department['Label'] ?? null,
            'position_id'                 => $position['Id'] ?? null,
            'position_label'              => $position['Label'] ?? null,
            'direct_manager1_id'          => $directManager1['Id'] ?? null,
            'direct_manager1_label'       => $directManager1['Label'] ?? null,
            'is_manager'                  => $internal['HookIsManager'] ?? null,
            'second_level_manager_id'     => $secondLevelMgr['Id'] ?? null,
            'second_level_manager_label'  => $secondLevelMgr['Label'] ?? null,
            'dm_email_id'                 => $dmEmail['Id'] ?? null,
            'dm_email_label'              => $dmEmail['Label'] ?? null,
            'second_email_id'             => $secondEmail['Id'] ?? null,
            'second_email_label'          => $secondEmail['Label'] ?? null,
            'offboarded'                  => $internal['HookOffboarded'] ?? null,
            'direct_manager2_id'          => $directManager2['Id'] ?? null,
            'direct_manager2_label'       => $directManager2['Label'] ?? null,
            'dm_email2_id'                => $dmEmail2['Id'] ?? null,
            'dm_email2_label'             => $dmEmail2['Label'] ?? null,
            'level'                       => isset($internal['HookLevel']) ? intval($internal['HookLevel']) : null,
            'tier'                        => isset($internal['HookTier']) ? intval($internal['HookTier']) : null,
            'entry_admin_link'            => $entry['AdminLink'] ?? null,
            // Do not update Cognito_ID as it is used for lookup
            'entry_status'                => $entry['Status'] ?? null,
            'entry_public_link'           => $entry['PublicLink'] ?? null,
            'entry_internal_link'         => $entry['InternalLink'] ?? null,
        ];

        // Update the record with new data
        $employeeData->update($updateData);

        return response()->json([
            'message' => 'Employee data updated successfully',
            'data'    => $employeeData
        ], 200);
    }

    public function destroy(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);
        $cognitoId = $data['Entry']['Number'] ?? null;

        if (is_null($cognitoId)) {
            return response()->json(['message' => 'Cognito_ID not provided'], 400);
        }

        // Find the record by Cognito_ID
        $employeeData = EmployeesDataModel::where('Cognito_ID', $cognitoId)->first();
        if (!$employeeData) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Delete the record
        $employeeData->delete();

        return response()->json([
            'message' => 'Employee data deleted successfully'
        ], 200);
    }

    /**
     * Export EmployeesDataModel data as a CSV for Excel.
     */
    public function exportToExcel()
    {
        // Fetch all records from the Employees_Data table
        $data = EmployeesDataModel::all();

        // Define the columns to export (all fields)
        $columns = [
            'id',
            'first_name_english',
            'first_and_last_name_english',
            'last_name_english',
            'first_name_arabic',
            'first_and_last_name_arabic',
            'last_name_arabic',
            'hiring_date',
            'pne_email',
            'personal_email',
            'sy_phone',
            'us_phone',
            'CLockInOutID',
            'img_link',
            'about_you',
            'password2',
            'shift',
            'depatment_id',
            'depatment_label',
            'position_id',
            'position_label',
            'direct_manager1_id',
            'direct_manager1_label',
            'is_manager',
            'second_level_manager_id',
            'second_level_manager_label',
            'dm_email_id',
            'dm_email_label',
            'second_email_id',
            'second_email_label',
            'offboarded',
            'direct_manager2_id',
            'direct_manager2_label',
            'dm_email2_id',
            'dm_email2_label',
            'level',
            'tier',
            'entry_admin_link',
            'Cognito_ID',
            'entry_status',
            'entry_public_link',
            'entry_internal_link',
            'created_at',
            'updated_at'
        ];

        // Open a memory stream
        $handle = fopen('php://memory', 'r+');

        // Write CSV header row
        fputcsv($handle, $columns);

        // Loop through each record and write data to CSV
        foreach ($data as $item) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = $item->{$col};
            }
            fputcsv($handle, $row);
        }

        // Rewind the memory stream and get its contents
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Return the CSV as a response, with headers for Excel compatibility
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'inline; filename="employees_data.csv"');
    }

    /**
     * Return all EmployeesDataModel data as JSON.
     */
    public function getData()
    {
        // Fetch all records from the Employees_Data table
        $data = EmployeesDataModel::all();
        return response()->json($data);
    }

    /**
     * Export all EmployeesDataModel data as a downloadable CSV.
     */
        public function export()
    {
        // Fetch all records from the Employees_Data table
        $data = EmployeesDataModel::all();

        // Define the columns to export (all fields)
        // In both exportToExcel() and export() methods, add 'CLockInOutID' to the $columns array:
        $columns = [
            'id',
            'first_name_english',
            'first_and_last_name_english',
            'last_name_english',
            'first_name_arabic',
            'first_and_last_name_arabic',
            'last_name_arabic',
            'hiring_date',
            'pne_email',
            'personal_email',
            'sy_phone',
            'us_phone',
            'CLockInOutID',
            'img_link',
            'about_you',
            'password2',
            'shift',
            'depatment_id',
            'depatment_label',
            'position_id',
            'position_label',
            'direct_manager1_id',
            'direct_manager1_label',
            'is_manager',
            'second_level_manager_id',
            'second_level_manager_label',
            'dm_email_id',
            'dm_email_label',
            'second_email_id',
            'second_email_label',
            'offboarded',
            'direct_manager2_id',
            'direct_manager2_label',
            'dm_email2_id',
            'dm_email2_label',
            'level',
            'tier',
            'entry_admin_link',
            'Cognito_ID',
            'entry_status',
            'entry_public_link',
            'entry_internal_link',
            'created_at',
            'updated_at'
        ];

        // Define a callback that writes CSV rows directly to the output stream
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            // Write header row
            fputcsv($file, $columns);

            // Write each record as a CSV row
            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $item->{$col};
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        // Return a streaming download response using Laravel's response()->streamDownload method
        return response()->streamDownload($callback, 'employees_data.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
