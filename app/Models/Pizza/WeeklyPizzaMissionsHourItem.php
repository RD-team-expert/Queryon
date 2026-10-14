<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyPizzaMissionsHourItem extends Model
{
    protected $table = 'weekly_pizza_missions_hours_items';
    public $timestamps = false;

    protected $fillable = [
        'weekly_pizza_missions_hour_id',
        'day',
        'work_type',
        'what_work',
        'note',
        'store_label',
        'hours',
        'hourly_pay',
        'gas',
        'invoice',
        'proof',
        'money_owed',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'hourly_pay' => 'decimal:2',
        'gas' => 'decimal:2',
        'invoice' => 'decimal:2',
        'proof' => 'array',
        'money_owed' => 'decimal:2',
    ];

    /**
     * Get the weekly hours entry that owns this row.
     */
    public function weeklyPizzaMissionsHour(): BelongsTo
    {
        return $this->belongsTo(WeeklyPizzaMissionsHour::class, 'weekly_pizza_missions_hour_id');
    }
}
