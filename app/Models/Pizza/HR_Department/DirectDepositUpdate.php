<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class DirectDepositUpdate extends Model
{
    protected $table = 'direct_deposit_updates';
    protected $fillable = [
        'form_request_id', 'ssn', 'birth_date', 'bank_name',
        'account_num', 'routing_number', 'attachment_link',
        'account_type', 'acknowledge'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'acknowledge' => 'boolean',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
