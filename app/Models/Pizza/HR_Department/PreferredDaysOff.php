<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class PreferredDaysOff extends Model
{
    protected $table = 'preferred_days_offs';
    protected $fillable = ['update_availability_id', 'day'];

    public function updateAvailability()
    {
        return $this->belongsTo(UpdateAvailability::class);
    }
}
