<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class HotNReady extends Model
{
    protected $table = 'hot_n_ready';
    protected $fillable = [
        'form_request_id', 'cleaning_task', 'attachment_link'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
