<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [

        'external_entry_number',

        'store_label',

        'issue',
        'suggestion',

        'first_name',
        'last_name',

        'phone',
        'email',

        'complaint_date',

        'manager_informed',

    ];

    protected $casts = [
        'complaint_date' => 'date',
    ];
}
