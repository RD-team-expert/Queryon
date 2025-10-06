<?php

namespace App\Models\Hiring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hire extends Model
{
    protected $table = 'hiring_hires_info';
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'emp_first_name',
        'emp_middle_name',
        'emp_last_name',
        'date_of_birth',
        'gender',
        'available_shifts',
        'work_days',
        'alta_clock_in_out_img',
        'paychex_profile_img',
        'paychex_direct_deposit_img',
        'signed_contract_img',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the hiring request that owns this hire.
     */
    public function hiringRequest(): BelongsTo
    {
        return $this->belongsTo(HiringRequest::class, 'request_id'); // Specify the foreign key
    }
}
