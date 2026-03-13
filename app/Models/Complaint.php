<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [

        'external_entry_number',

        'issue',
        'suggestion',

        'first_name',
        'last_name',
        'full_name',

        'phone',
        'email',

        'complaint_date',

        'store_label',

        'manager_informed',

        'status',
        'action',

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
