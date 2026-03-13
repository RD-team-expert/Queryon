<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'external_entry_number',

        'form_id',
        'form_internal_name',
        'form_name',
        'form_url_name',

        'improvement_feedback',

        'first_name',
        'last_name',
        'full_name',

        'store_label',

        'valued_respected_appreciated',
        'valued_respected_appreciated_rating',

        'work_schedule_satisfaction',
        'work_schedule_satisfaction_rating',

        'gm_email',
        'director_email',

        'entry_status',
        'submitted_at',
        'updated_at_external',

        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'updated_at_external' => 'datetime',
    ];
}