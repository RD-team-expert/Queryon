<?php

namespace App\Services\HR_Department\Helpers;

use App\Models\Pizza\HR_Department\Language;
use App\Models\Pizza\HR_Department\RequestType;
use App\Models\Pizza\HR_Department\Store;

class MappingService
{
    public function formMap($data){

        $formId   = $data['Entry']['Number'] ?? null;

        $storeName= $data['HookMain']['store_name']['Label'] ?? null;
        $storeId = Store::where('name', $storeName)->first()->value('id');

        $language  = $data['HookMain']['language'] ?? null;
        $languageId = Language::where('name', $language)->first()->value('id');

        if($languageId == 1){
            $requestType   = $data['HookMain']['english']['en_RequestType'] ?? null;
            $firstName  = $data['HookMain']['english']['en_Name']['First'] ?? null;
            $lastName   = $data['HookMain']['english']['en_Name']['Last'] ?? null;
            $phone      = $data['HookMain']['english']['en_Phone'] ?? null;
            $email      = $data['HookMain']['english']['en_Email'] ?? null;
            $date       = $data['HookMain']['english']['en_Date'] ?? null;

        }elseif($languageId == 2){
            $requestType   = $data['HookMain']['arabic']['ar_RequestType'] ?? null;
            $firstName  = $data['HookMain']['arabic']['ar_Name']['First'] ?? null;
            $lastName   = $data['HookMain']['arabic']['ar_Name']['Last'] ?? null;
            $phone      = $data['HookMain']['arabic']['ar_Phone'] ?? null;
            $email      = $data['HookMain']['arabic']['ar_Email'] ?? null;
            $date       = $data['HookMain']['arabic']['ar_Date'] ?? null;

        }elseif($languageId == 3){
            $requestType   = $data['HookMain']['spanish']['es_RequestType'] ?? null;
            $firstName  = $data['HookMain']['spanish']['es_Name']['First'] ?? null;
            $lastName   = $data['HookMain']['spanish']['es_Name']['Last'] ?? null;
            $phone      = $data['HookMain']['spanish']['es_Phone'] ?? null;
            $email      = $data['HookMain']['spanish']['es_Email'] ?? null;
            $date       = $data['HookMain']['spanish']['es_Date'] ?? null;

        }

        $requestTypeId= RequestType::where('name',$requestType )->first()->value('id');

        //management
        $managerFirstName       =$data['section_FeedbackorComplaints']['ManagementSection']['ManagerName']['First'];
        $managerLastName        =$data['section_FeedbackorComplaints']['ManagementSection']['ManagerName']['Last'];
        $managerTitle           =$data['section_FeedbackorComplaints']['ManagementSection']['ManagerName']['Prefix'];
        $managerNote            =$data['section_FeedbackorComplaints']['ManagementSection']['manager_note'];

        $managerIssueIsSolved   =$data['section_FeedbackorComplaints']['ManagementSection']['is_solved'];
        $managerIssueIsSolved = strtolower($managerIssueIsSolved) === 'yes';

        $mapped = [

            'id' =>$formId,
            'language_id' => $languageId,
            'store_id' => $storeId,
            'first_name' =>$firstName,
            'last_name' =>$lastName,
            'phone' => $phone,
            'email' => $email,
            'request_date' => $date,
            'request_type_id' => $requestTypeId,
            'manager_first_name' => $managerFirstName,
            'manager_last_name' => $managerLastName,
            'manager_title' => $managerTitle,
            'manager_note' =>$managerNote,
            'manager_issue_is_solved' =>$managerIssueIsSolved,

        ];

        return $mapped;
    }


}
