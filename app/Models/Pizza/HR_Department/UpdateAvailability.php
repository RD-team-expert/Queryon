<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateAvailability extends Model
{
    protected $table = 'update_availabilities';
    protected $fillable = [
        'form_request_id', 'preferred_employment_type',
        'preferred_weekend', 'preferred_shift',
        'preferred_shift_start_hour', 'note'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function preferredDaysOffs()
    {
        return $this->hasMany(PreferredDaysOff::class);
    }

    public function preferredWorkDays()
    {
        return $this->hasMany(PreferredWorkDay::class);
    }
}
