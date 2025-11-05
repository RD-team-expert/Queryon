<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class HealthInsurance extends Model
{
    protected $table = 'health_insurances';
    protected $fillable = [
        'form_request_id', 'the_incident', 'is_incident_in_same_day',
        'date', 'emp_work_kind', 'witnesses', 'img_link'
    ];

    protected $casts = [
        'is_incident_in_same_day' => 'boolean',
        'date' => 'date',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
