<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class VoluntaryResignation extends Model
{
    protected $table = 'voluntary_resignations';
    protected $fillable = [
        'form_request_id', 'reason_for_resigning', 'last_work_day_date'
    ];

    protected $casts = [
        'last_work_day_date' => 'date',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
