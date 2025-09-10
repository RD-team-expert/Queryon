<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSchedule extends Model
{
    use HasFactory;

    protected $table = 'attendance_schedule';

    protected $fillable = [
        'store', 'schedule_date', 'name', 'emp_id',
        // Tuesday
        'tue_vci', 'tue_in', 'tue_out', 'tue_status', 'tue_total_hrs', 'tue_op', 'tue_m',
        'tue_l', 'tue_c', 'tue_status_f', 'tue_hours_cost', 'tue_hours', 'tue_sales', 'tue_4hrs',
        // Wednesday
        'wed_vc', 'wed_in', 'wed_out', 'wed_status', 'wed_total_hrs', 'wed_op', 'wed_m',
        'wed_l', 'wed_c', 'wed_status2', 'wed_hours_cost', 'wed_hours', 'wed_sales', 'wed_hrs',
        // Thursday
        'thu_vci', 'thu_in', 'thu_out', 'thu_status', 'thu_total_hrs', 'thu_op', 'thu_m',
        'thu_l', 'thu_c', 'thu_status_formula', 'thu_hours_cost', 'thu_hours', 'thu_sales', 'thu_4hrs',
        // Friday
        'fri_vci30', 'fri_in', 'fri_out', 'fri_status', 'fri_total_hrs', 'fri_op', 'fri_m',
        'fri_l', 'fri_c', 'fri_status_formula', 'fri_hours_cost', 'fri_hours', 'fri_sales', 'fri_4hrs',
        // Saturday
        'sat_vci44', 'sat_in', 'sat_out', 'sat_status', 'sat_total_hrs', 'sat_op', 'sat_m',
        'sat_l', 'sat_c', 'sat_status_formula', 'sat_hours_cost', 'sat_hours', 'sat_sales', 'sat_4hrs',
        // Sunday
        'sun_vci', 'sun_in', 'sun_out', 'sun_status', 'sun_total_hrs', 'sun_op', 'sun_m',
        'sun_l', 'sun_c', 'sun_status_formula', 'sun_hours_cost', 'sun_hours', 'sun_sales', 'sun_4hrs',
        // Monday
        'mon_vci', 'mon_in', 'mon_out', 'mon_status', 'mon_total_hrs', 'mon_op', 'mon_m',
        'mon_l', 'mon_c', 'mon_status_formula', 'mon_hours_cost', 'mon_hours', 'mon_sales', 'mon_4hrs'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        // Time fields
        'tue_in' => 'string', 'tue_out' => 'string',
        'wed_in' => 'string', 'wed_out' => 'string',
        'thu_in' => 'string', 'thu_out' => 'string',
        'fri_in' => 'string', 'fri_out' => 'string',
        'sat_in' => 'string', 'sat_out' => 'string',
        'sun_in' => 'string', 'sun_out' => 'string',
        'mon_in' => 'string', 'mon_out' => 'string',
        // Decimal fields
        'tue_total_hrs' => 'decimal:2', 'tue_hours_cost' => 'decimal:2', 'tue_hours' => 'decimal:2', 'tue_sales' => 'decimal:2',
        'wed_total_hrs' => 'decimal:2', 'wed_hours_cost' => 'decimal:2', 'wed_hours' => 'decimal:2', 'wed_sales' => 'decimal:2',
        'thu_total_hrs' => 'decimal:2', 'thu_hours_cost' => 'decimal:2', 'thu_hours' => 'decimal:2', 'thu_sales' => 'decimal:2',
        'fri_total_hrs' => 'decimal:2', 'fri_hours_cost' => 'decimal:2', 'fri_hours' => 'decimal:2', 'fri_sales' => 'decimal:2',
        'sat_total_hrs' => 'decimal:2', 'sat_hours_cost' => 'decimal:2', 'sat_hours' => 'decimal:2', 'sat_sales' => 'decimal:2',
        'sun_total_hrs' => 'decimal:2', 'sun_hours_cost' => 'decimal:2', 'sun_hours' => 'decimal:2', 'sun_sales' => 'decimal:2',
        'mon_total_hrs' => 'decimal:2', 'mon_hours_cost' => 'decimal:2', 'mon_hours' => 'decimal:2', 'mon_sales' => 'decimal:2',
    ];

    // Relationship
    public function empInfo()
    {
        return $this->belongsTo(EmpInfo::class, 'emp_id', 'emp_id')
                    ->where('schedule_date', $this->schedule_date);
    }
}
