<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class TimeOffDate extends Model
{
    protected $table = 'time_off_dates';
    protected $fillable = ['request_time_off_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function requestTimeOff()
    {
        return $this->belongsTo(RequestTimeOff::class);
    }
}
