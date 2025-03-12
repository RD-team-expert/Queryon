<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectiveActionPlan extends Model
{
    use HasFactory;

    protected $table = 'corrective_action_plans';

    protected $fillable = [
        'hook_date_of_incident',
        'hook_cap_type',
        'hook_managements_statement',
        'hook_name_person_doing_the_cap_id',
        'hook_name_person_doing_the_cap_label',
        'hook_email_person_doing_the_cap_label',
        'hook_employee_name_id',
        'hook_employee_name_label',
        'hook_employee_email_label',
        'hook_title_person_doing_the_cap',
        'hook_the_email_person_doing_the_cap',
        'hook_the_email_employee_email',
        'hook_depatmant_name_filter',
        'hook_employees_statement',
        'admin_link',
        'number',
        'timestamp',
        'cap_link',
        'emp_link',
        'view1_link',
        'upper_management_link',
        'the_next_manager_above_link',
        'upper_manager_in_the_department_link',
        'witness1_link',
        'witness2_link',
    ];
}
