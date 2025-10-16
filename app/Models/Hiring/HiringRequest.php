<?php

namespace App\Models\Hiring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HiringRequest extends Model
{
    protected $table = 'hiring_requests';
    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'store',
        'date_of_request',
        'num_of_emp_needed',
        'desired_start_date',
        'additional_notes',
        'supervisors_first_name',
        'supervisors_last_name',
        'supervisors_accept',
        'supervisors_notes',
        'hr_first_name',
        'hr_last_name',
        'hr_num_of_hires',
        'cognito_id'
    ];

    protected $casts = [
        'desired_start_date' => 'date',
        'date_of_request' => 'date',
        'supervisors_accept' => 'boolean',
        'cognito_id' => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Get all hires associated with this hiring request.
     * Specify the foreign key and table relationship correctly
     */
    public function hires(): HasMany
    {
        return $this->hasMany(Hire::class, 'request_id'); // Specify the foreign key
    }
    /**
     * Get the full name of the requester.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the full name of the supervisor.
     */
    public function getSupervisorFullNameAttribute(): ?string
    {
        if (!$this->supervisors_first_name || !$this->supervisors_last_name) {
            return null;
        }

        return $this->supervisors_first_name . ' ' . $this->supervisors_last_name;
    }

    /**
     * Get the full name of the HR representative.
     */
    public function getHrFullNameAttribute(): ?string
    {
        if (!$this->hr_first_name || !$this->hr_last_name) {
            return null;
        }

        return $this->hr_first_name . ' ' . $this->hr_last_name;
    }
}
