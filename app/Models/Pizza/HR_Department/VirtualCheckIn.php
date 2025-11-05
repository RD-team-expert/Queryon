<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualCheckIn extends Model
{
    protected $table = 'virtual_check_ins';
    protected $fillable = [
        'form_request_id', 'will_show_up',
        'unable_to_show_up_reason', 'vto'
    ];

    protected $casts = [
        'will_show_up' => 'boolean',
        'vto' => 'boolean',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
