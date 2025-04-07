<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeesDataModel extends Model
{
    use HasFactory;

    protected $table = 'Employees_Data';

    protected $fillable = [
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
        'entry_internal_link'
    ];
}
