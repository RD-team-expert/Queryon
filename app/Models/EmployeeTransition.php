<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransition extends Model
{
    protected $fillable = [
        'external_entry_number',
        'store_manager_name',
        'employee_full_name',
        'from_store',
        'to_store',
        'hours',
        'transition_date',
    ];

    protected $casts = [
        'transition_date' => 'date',
    ];
}
