<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyPizzaMissionsHour extends Model
{
    use HasFactory;

    /**
     * Days in the order the form lists them (the week starts on Tuesday).
     */
    public const DAYS = ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Monday'];

    /**
     * The table associated with the model.
     */
    protected $table = 'weekly_pizza_missions_hours';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cognito_id',
        'entry_number',
        'name_full',
        'week_number',
        'days_filled',
        'hourly_pay',
        'tuesday_total_hours',
        'wednesday_total_hours',
        'thursday_total_hours',
        'friday_total_hours',
        'saturday_total_hours',
        'sunday_total_hours',
        'monday_total_hours',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'entry_number' => 'integer',
        'week_number' => 'integer',
        'days_filled' => 'array',
        'hourly_pay' => 'decimal:2',
        'tuesday_total_hours' => 'decimal:2',
        'wednesday_total_hours' => 'decimal:2',
        'thursday_total_hours' => 'decimal:2',
        'friday_total_hours' => 'decimal:2',
        'saturday_total_hours' => 'decimal:2',
        'sunday_total_hours' => 'decimal:2',
        'monday_total_hours' => 'decimal:2',
    ];

    /**
     * Get the daily work entries submitted with this entry.
     */
    public function items(): HasMany
    {
        return $this->hasMany(WeeklyPizzaMissionsHourItem::class, 'weekly_pizza_missions_hour_id');
    }

    /**
     * Total hours column for a given day name (e.g. "Tuesday" => tuesday_total_hours).
     */
    public static function totalColumnForDay(string $day): string
    {
        return strtolower($day) . '_total_hours';
    }
}
