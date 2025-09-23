<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyScheduleSummary extends Model
{
    use HasFactory;

    protected $table = 'weekly_schedule_summary';

    protected $fillable = [
        'store', 'schedule_date', 'name', 'emp_id', 'schedule_emp_info_id', 'shift_count',
        'x', 'oje', 'off_both_we', 'status_not_filled', 'hours_not_given', 'dh_not_scheduled',
        'headcount', 'weekend_not_filling_status', 'weekly_hours', 'ot_calc', 'both_weekends',
        'px', 't', 're', 'vci87', 'excused_absence', 'unexcused_absence', 'late',
        'tenure_in_months', 'hourly_base_pay_alt', 'hourly_performance_pay_alt',
        'totally_pay_alt', 'position_alt', 'is_1099_alt', 'total_pay'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'weekly_hours' => 'decimal:2',
        'ot_calc' => 'decimal:2',
        'tenure_in_months' => 'decimal:2',
        'hourly_base_pay_alt' => 'decimal:2',
        'hourly_performance_pay_alt' => 'decimal:2',
        'totally_pay_alt' => 'decimal:2',
        'total_pay' => 'decimal:2',
    ];

    /**
     * Each WeeklyScheduleSummary belongs to one EmpInfo record
     */
    public function empInfo(): BelongsTo
    {
        return $this->belongsTo(EmpInfo::class, 'schedule_emp_info_id');
    }
}
