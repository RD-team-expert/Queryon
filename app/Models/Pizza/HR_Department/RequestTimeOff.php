<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class RequestTimeOff extends Model
{
    protected $table = 'request_time_offs';
    protected $fillable = [
        'form_request_id', 'num_of_days_needed',
        'reason_for_days_off', 'is_willing_to_work_outside_availability'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function timeOffDates()
    {
        return $this->hasMany(TimeOffDate::class);
    }
}
