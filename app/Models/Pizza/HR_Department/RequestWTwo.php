<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class RequestWTwo extends Model
{
    protected $table = 'request_w_twos';
    protected $fillable = [
        'form_request_id', 'address_line_one', 'address_line_two',
        'city', 'state', 'zip_code'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
