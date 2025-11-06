<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class RequestCheckStub extends Model
{
    protected $table = 'request_check_stubs';
    protected $fillable = [
        'form_request_id', 'request_purpose',
        'pay_stub_start_date', 'pay_stub_end_date'
    ];

    protected $casts = [
        'pay_stub_start_date' => 'date',
        'pay_stub_end_date' => 'date',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
