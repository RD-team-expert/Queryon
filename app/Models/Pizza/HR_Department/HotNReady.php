<?php

namespace Pizza\HR_Department\Models;

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
