<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSickHoursTransition extends Model
{
     use HasFactory;

    protected $table = 'employee_sick_hours';

    protected $fillable = [
        'external_entry_number',  
        'store_label',
        'store_manager_name',
        'employee_name',
        'date',
        'amount_of_sick_hours',
    ];
}
