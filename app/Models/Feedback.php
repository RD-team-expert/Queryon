<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'external_entry_number',
        'improvement_feedback',
        'first_name',
        'last_name',
        'valued_respected_appreciated_rating',
        'work_schedule_satisfaction_rating',

    ];
}
