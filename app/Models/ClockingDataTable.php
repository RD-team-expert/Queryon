<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockingDataTable extends Model
{
    use HasFactory;

    // Define the table name if it's not plural of the model
    protected $table = 'clocking_data';

    // Specify which columns can be mass assigned (for create() and update())
    protected $fillable = [
        'AC_No',
        'Name',
        'Date',
        'Clock_In',
        'Clock_Out',
        'Entry_Number',
    ];

    // Optionally, you can define custom timestamps if you have specific requirements
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
