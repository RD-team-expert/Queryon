<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpInfo extends Model
{
    use HasFactory;

    protected $table = 'schedule_emp_info';

    protected $fillable = [
        'store', 'schedule_date', 'hired_date', 'hourly_base_pay', 'hourly_performance_pay',
        'totally_pay', 'position', 'is_1099', 'uniform', 'num_of_shirts', 'emp_id',
        'formula_emp_not_getting_hours_wanted', 'at_only', 'family', 'car', 'dob', 'name',
        'red_in_schedule', 'reads_in_schedule', 'emp_id_alt', 'new_team_member',
        'da_safety_score', 'attendance', 'score', 'notes', 'num_of_days',
        'reads_in_schedule_2', 'cross_trained', 'preference', 'pt_ft', 'name_alt',
        'rating', 'maximum_hours', 'hours_given'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'hired_date' => 'date',
        'dob' => 'date',
        'hourly_base_pay' => 'decimal:2',
        'hourly_performance_pay' => 'decimal:2',
        'totally_pay' => 'decimal:2',
        'maximum_hours' => 'decimal:2',
        'hours_given' => 'decimal:2',
    ];

    /**
     * One EmpInfo record can have many AttendanceSchedule records
     */
    public function attendanceSchedules(): HasMany
    {
        return $this->hasMany(AttendanceSchedule::class, 'schedule_emp_info_id');
    }

    /**
     * One EmpInfo record can have many WeeklyScheduleSummary records
     */
    public function weeklyScheduleSummaries(): HasMany
    {
        return $this->hasMany(WeeklyScheduleSummary::class, 'schedule_emp_info_id');
    }
}
