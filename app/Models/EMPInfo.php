<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMPInfo extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's naming conventions.
    protected $table = 'EMP_Info';

    // Define the fillable fields.
    protected $fillable = [
        'First_Name_English',
        'Last_Name_English',
        'First_And_Last_Name_English',
        'First_Name_Arabic',
        'Last_Name_Arabic',
        'First_And_Last_Name_Arabic',
        'HiringDate',
        'PneEmail',
        'PersonalEmail',
        'SYPhone',
        'USPhone',
        'YourPicture',
        'AboutYou',
        'Password',
        'Shift',
        'Depatment_Name',
        'Position_Name',
        'Offboarded',
        'Level',
        'Tier',
        'Entry_Number',
    ];
}
