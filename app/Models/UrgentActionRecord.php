<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrgentActionRecord extends Model
{
    protected $fillable = [
        'external_entry_number',
        'employee_first_name',
        'employee_last_name',
        'today_date',
        'store_label',
        'action_taken',
        'why',
        'manager_first_name',
        'manager_last_name',
    ];
}
