<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockInOutData extends Model
{
    use HasFactory;

    // Specify the table name (optional, Laravel will use the plural of the model name by default)
    protected $table = 'clock_in_out_data';

    // Define fillable columns (optional, but recommended for mass assignment)
    protected $fillable = [
        'AC_No',
        'Name',
        'Date',
        'On_duty',
        'Off_duty',
        'Clock_In',
        'Clock_Out',
        'Late',
        'Early',
        'Work_Time',
        'Department',
    ];

    // Disable timestamps if you don't need them (optional)
    public $timestamps = true;
}