<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RDO_Data_Model extends Model
{
    use HasFactory;

    // Define the table name since it doesn't follow the default plural naming convention.
    protected $table = 'rdo_data_table';

    // Define fillable fields for mass assignment.
    protected $fillable = [
        'Name_Lable',
        'Name_ID',
        'HookTodaysDate',
        'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
        'HookStartDate',
        'HookEndDate',
        'HookDepartment_ID',
        'HookDepartment_Lable',
        'HookHowManyDaysDoYouNeed2',
        'HookType',
        'HookAreYouAbleToProvideAProof',
        'HookHowManyDaysDoYouNeed2_IncrementBy',
        'HookAreYouAbleToProvideMoreSpecificPlease_IsRequired',
        'DirectManagerName_ID',
        'DirectManagerName_Lable',
        'HookApprove',
        'Note',
        'AdminLink',
        'Status',
        'PublicLink',
        'InternalLink',
        'Entry_Number'
    ];

    // Optionally, cast the date fields to Carbon instances.
    protected $casts = [
        'HookTodaysDate' => 'date',
        'HookStartDate' => 'date',
        'HookEndDate'   => 'date',
    ];
}

