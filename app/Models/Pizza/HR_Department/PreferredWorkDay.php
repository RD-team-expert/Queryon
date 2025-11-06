<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class PreferredWorkDay extends Model
{
    protected $table = 'preferred_work_days';
    protected $fillable = ['update_availability_id', 'day'];

    public function updateAvailability()
    {
        return $this->belongsTo(UpdateAvailability::class);
    }
}
