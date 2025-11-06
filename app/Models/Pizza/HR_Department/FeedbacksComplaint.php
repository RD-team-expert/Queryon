<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class FeedbacksComplaint extends Model
{
    protected $table = 'feedbacks_complaints';
    protected $fillable = [
        'form_request_id', 'the_issue_or_concern', 'suggestions'
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }
}
