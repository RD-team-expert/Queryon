<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewHireException extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'new_hire_exceptions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cognito_id',
        'entry_number',
        'store_manager_full_name',
        'store_label',
        'week',
        'submitted_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'entry_number' => 'integer',
        'week' => 'integer',
        'submitted_date' => 'datetime',
    ];

    /**
     * Get the new hire rows submitted with this entry.
     */
    public function items(): HasMany
    {
        return $this->hasMany(NewHireExceptionItem::class, 'new_hire_exception_id');
    }

    /**
     * Scope a query to only include entries for a specific store.
     */
    public function scopeForStore($query, string $storeLabel)
    {
        return $query->where('store_label', $storeLabel);
    }

    /**
     * Scope a query to filter by submission date range.
     */
    public function scopeDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('submitted_date', [$startDate, $endDate]);
        }

        if ($startDate) {
            return $query->where('submitted_date', '>=', $startDate);
        }

        if ($endDate) {
            return $query->where('submitted_date', '<=', $endDate);
        }

        return $query;
    }
}
