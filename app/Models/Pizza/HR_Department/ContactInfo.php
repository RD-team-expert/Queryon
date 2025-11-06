<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $table = 'contact_infos';
    protected $fillable = [
        'form_request_id', 'first_name', 'middle_name', 'last_name',
        'suffix', 'phone', 'email', 'address_line_one', 'address_line_two',
        'city', 'state', 'zip_code'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
