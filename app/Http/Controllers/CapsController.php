<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapsData;

class CapsController extends Controller
{
    /**
     * Store a new Caps data entry.
     */
    public function store(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        // Extract sections from the JSON payload
        $capSection         = $data['HookCAPSection']      ?? [];
        $namePersonDoingCAP = $capSection['HookNamePersonDoingTheCAP'] ?? [];
        $emailPersonDoingCAP= $capSection['HookEmailPersonDoingTheCAP'] ?? [];
        $employeeName       = $capSection['HookEmployeeName']      ?? [];
        $employeeEmail      = $capSection['HookEmployeeEmail']     ?? [];
        $empSection         = $data['HookEMPSection']        ?? [];
        $entry              = $data['Entry']                 ?? [];

        // Create a new CapsData record by mapping JSON fields to table columns
        $capsData = CapsData::create([
            'hook_date_of_incident'            => $capSection['HookDateOfIncident']      ?? null,
            'hook_cap_type'                    => $capSection['HookCAPType']             ?? null,
            'hook_managements_statement'       => $capSection['HookManagementsStatement'] ?? null,
            'hook_name_person_doing_the_cap_id'=> $namePersonDoingCAP['Id']              ?? null,
            'hook_name_person_doing_the_cap_label' => $namePersonDoingCAP['Label']        ?? null,
            'hook_email_person_doing_the_cap_label' => $emailPersonDoingCAP['Label']       ?? null,
            'hook_employee_name_id'            => $employeeName['Id']                    ?? null,
            'hook_employee_name_label'         => $employeeName['Label']                 ?? null,
            'hook_employee_email_label'        => $employeeEmail['Label']                ?? null,
            'hook_title_person_doing_the_cap'  => $capSection['HookTitlePersonDoingTheCAP']    ?? null,
            'hook_the_email_person_doing_the_cap' => $capSection['HookTheEmailPersonDoingTheCAP'] ?? null,
            'hook_the_email_employee_email'    => $capSection['HookTheEmailEmployeeEmail']     ?? null,
            'hook_depatmant_name_filter'       => $capSection['HookDepatmantNameFilter']       ?? null,

            'hook_employees_statement'         => $empSection['HookEmployeesStatement']  ?? null,
            'admin_link'                       => $entry['AdminLink']                      ?? null,
            'number'                           => isset($entry['Number']) ? (int)$entry['Number'] : null,
            'timestamp'                        => $entry['Timestamp']                      ?? null,
            'cap_link'                         => $entry['CAPLink']                        ?? null,
            'emp_link'                         => $entry['EmpLink']                        ?? null,
            'view1_link'                       => $entry['View1Link']                      ?? null,
            'upper_management_link'            => $entry['UpperManagementLink']            ?? null,
            'the_next_manager_above_link'      => $entry['TheNextManagerAboveLink']        ?? null,
            'upper_manager_in_the_department_link' => $entry['UpperManagerInTheDepartmentLink'] ?? null,
            'witness1_link'                    => $entry['Witness1Link']                   ?? null,
            'witness2_link'                    => $entry['Witness2Link']                   ?? null,
        ]);

        return response()->json([
            'message' => 'Caps data created successfully',
            'data'    => $capsData
        ], 201);
    }

    /**
     * Update an existing Caps data entry based on the entry number.
     */
    public function update(Request $request)
    {
        // Log the start of the update process
        \Illuminate\Support\Facades\Log::info('CAPS update process started');

        // Decode the JSON payload into an associative array
        $data = json_decode($request->getContent(), true);
        \Illuminate\Support\Facades\Log::info('Update request data:', ['data' => $data]);

        $entry = $data['Entry'] ?? [];
        $entryNumber = isset($entry['Number']) ? (int)$entry['Number'] : null;
        \Illuminate\Support\Facades\Log::info('Entry number:', ['number' => $entryNumber]);

        if (!$entryNumber) {
            \Illuminate\Support\Facades\Log::error('Entry number not provided');
            return response()->json(['message' => 'Entry number not provided'], 400);
        }

        // Find the record by the entry number
        $capsData = CapsData::where('number', $entryNumber)->first();

        if (!$capsData) {
            \Illuminate\Support\Facades\Log::error('Caps data not found for entry number:', ['number' => $entryNumber]);
            return response()->json(['message' => 'Caps data not found'], 404);
        }

        \Illuminate\Support\Facades\Log::info('Found CAPS data to update:', ['id' => $capsData->id, 'number' => $capsData->number]);

        try {
            // Extract sections from the JSON payload
            $capSection         = $data['HookCAPSection']      ?? [];
            $namePersonDoingCAP = $capSection['HookNamePersonDoingTheCAP'] ?? [];
            $emailPersonDoingCAP= $capSection['HookEmailPersonDoingTheCAP'] ?? [];
            $employeeName       = $capSection['HookEmployeeName']      ?? [];
            $employeeEmail      = $capSection['HookEmployeeEmail']     ?? [];
            $empSection         = $data['HookEMPSection']        ?? [];

            // Update the record by mapping JSON fields to table columns
            $updateData = [
                'hook_date_of_incident'            => $capSection['HookDateOfIncident']      ?? $capsData->hook_date_of_incident,
                'hook_cap_type'                    => $capSection['HookCAPType']             ?? $capsData->hook_cap_type,
                'hook_managements_statement'       => $capSection['HookManagementsStatement'] ?? $capsData->hook_managements_statement,
                'hook_name_person_doing_the_cap_id'=> $namePersonDoingCAP['Id']              ?? $capsData->hook_name_person_doing_the_cap_id,
                'hook_name_person_doing_the_cap_label' => $namePersonDoingCAP['Label']        ?? $capsData->hook_name_person_doing_the_cap_label,
                'hook_email_person_doing_the_cap_label' => $emailPersonDoingCAP['Label']       ?? $capsData->hook_email_person_doing_the_cap_label,
                'hook_employee_name_id'            => $employeeName['Id']                    ?? $capsData->hook_employee_name_id,
                'hook_employee_name_label'         => $employeeName['Label']                 ?? $capsData->hook_employee_name_label,
                'hook_employee_email_label'        => $employeeEmail['Label']                ?? $capsData->hook_employee_email_label,
                'hook_title_person_doing_the_cap'  => $capSection['HookTitlePersonDoingTheCAP']    ?? $capsData->hook_title_person_doing_the_cap,
                'hook_the_email_person_doing_the_cap' => $capSection['HookTheEmailPersonDoingTheCAP'] ?? $capsData->hook_the_email_person_doing_the_cap,
                'hook_the_email_employee_email'    => $capSection['HookTheEmailEmployeeEmail']     ?? $capsData->hook_the_email_employee_email,
                'hook_depatmant_name_filter'       => $capSection['HookDepatmantNameFilter']       ?? $capsData->hook_depatmant_name_filter,

                'hook_employees_statement'         => $empSection['HookEmployeesStatement']  ?? $capsData->hook_employees_statement,
                'admin_link'                       => $entry['AdminLink']                      ?? $capsData->admin_link,
                'number'                           => $entryNumber,
                'timestamp'                        => $entry['Timestamp']                      ?? $capsData->timestamp,
                'cap_link'                         => $entry['CAPLink']                        ?? $capsData->cap_link,
                'emp_link'                         => $entry['EmpLink']                        ?? $capsData->emp_link,
                'view1_link'                       => $entry['View1Link']                      ?? $capsData->view1_link,
                'upper_management_link'            => $entry['UpperManagementLink']            ?? $capsData->upper_management_link,
                'the_next_manager_above_link'      => $entry['TheNextManagerAboveLink']        ?? $capsData->the_next_manager_above_link,
                'upper_manager_in_the_department_link' => $entry['UpperManagerInTheDepartmentLink'] ?? $capsData->upper_manager_in_the_department_link,
                'witness1_link'                    => $entry['Witness1Link']                   ?? $capsData->witness1_link,
                'witness2_link'                    => $entry['Witness2Link']                   ?? $capsData->witness2_link,
            ];

            \Illuminate\Support\Facades\Log::info('Update data prepared:', ['updateData' => $updateData]);

            $capsData->update($updateData);

            \Illuminate\Support\Facades\Log::info('CAPS data updated successfully', ['id' => $capsData->id]);

            return response()->json([
                'message' => 'Caps data updated successfully',
                'data'    => $capsData
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating CAPS data:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update CAPS data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an existing Caps data entry based on the entry number.
     */
    public function destroy(Request $request)
    {
        // Decode the JSON payload into an associative array
        $data  = json_decode($request->getContent(), true);
        $entry = $data['Entry'] ?? [];
        $entryNumber = isset($entry['Number']) ? (int)$entry['Number'] : null;

        if (!$entryNumber) {
            return response()->json(['message' => 'Entry number not provided'], 400);
        }

        // Find the record by the entry number
        $capsData = CapsData::where('number', $entryNumber)->first();
        if (!$capsData) {
            return response()->json(['message' => 'Caps data not found'], 404);
        }

        // Delete the record
        $capsData->delete();

        return response()->json([
            'message' => 'Caps data deleted successfully'
        ], 200);
    }
}
