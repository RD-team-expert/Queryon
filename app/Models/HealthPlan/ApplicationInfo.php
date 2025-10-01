<?php

namespace App\Models\HealthPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationInfo extends Model
{
    use HasFactory;

    protected $table = 'applications_info';

    protected $fillable = [
        'store',
        'add_term_or_change', 'plan_choice', 'last_name', 'first_name',
        'middle_initial', 'dob', 'street_address', 'street_address_2',
        'city', 'state_abbreviation', 'zip', 'phone', 'email_address',
        'date_of_hire', 'gender', 'ssn', 'location', 'occupation',
        'average_hours_worked_per_week', 'marital_status', 'coverage_tier', 'cognito_id'
    ];

    protected $casts = [
        'dob' => 'date',
        'date_of_hire' => 'date',
        'average_hours_worked_per_week' => 'decimal:2'
    ];

    public function dependents(): HasMany
    {
        return $this->hasMany(DependentInfo::class, 'application_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_initial . ' ' . $this->last_name);
    }
}
