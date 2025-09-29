<?php

namespace App\Models\HealthPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DependentInfo extends Model
{
    use HasFactory;

    protected $table = 'dependents_info';

    protected $fillable = [
        'application_id', 'count', 'dependent_first_name',
        'dependent_middle_initial', 'dependent_last_name',
        'ssn', 'gender', 'dob', 'dependent_type' ,'cognito_id'
    ];

    protected $casts = [
        'dob' => 'date'
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ApplicationInfo::class, 'application_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->dependent_first_name . ' ' . $this->dependent_middle_initial . ' ' . $this->dependent_last_name);
    }
}
