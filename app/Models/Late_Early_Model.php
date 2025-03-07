<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Late_Early_Model extends Model
{
    use HasFactory;

    // Define the table name (optional if it follows convention)
    protected $table = 'late_early';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'HookDirectManagerName_ID',
        'HookDirectManagerName_Lable',
        'HookApprove',
        'HookNote',
        'HookName_ID',
        'HookName_Lable',
        'HookTodaysDate',
        'HookPleaseProvideAReasonForYourRequest',
        'HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA',
        'HookComingLateLeavingEarlier',
        'HookDepartment_ID',
        'HookDepartment_Lable',
        'HookComingHour',
        'HookLeavingHour',
        'HookShift2',
        'HookChangeSift',
        'HookStartAt',
        'HookEndAt',
        'AdminLink',
        'DateCreated',
        'DateSubmitted',
        'DateUpdated',
        'EntryNumber',
        'PublicLink',
        'InternalLink'
    ];
}
