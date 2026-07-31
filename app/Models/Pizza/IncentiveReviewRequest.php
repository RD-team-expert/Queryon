<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveReviewRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'incentive_review_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Cognito Entry Identity
        'cognito_id',
        'entry_number',

        // Store Manager Section
        'store_manager_first_name',
        'store_manager_last_name',
        'todays_date',
        'shift',
        'store_label',
        'issue_details',
        'review_aspects',
        'week_start_date',
        'week_end_date',

        // Management Section
        'manager_first_name',
        'manager_last_name',
        'manager_approval',
        'decision_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'entry_number' => 'integer',
        'todays_date' => 'date',
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'review_aspects' => 'array',
    ];

    /**
     * Get the store manager's full name.
     */
    public function getStoreManagerFullNameAttribute(): string
    {
        return trim("{$this->store_manager_first_name} {$this->store_manager_last_name}");
    }

    /**
     * Get the manager's full name.
     */
    public function getManagerFullNameAttribute(): string
    {
        return trim("{$this->manager_first_name} {$this->manager_last_name}");
    }

    /**
     * Scope a query to only include requests for a specific store.
     */
    public function scopeForStore($query, string $storeLabel)
    {
        return $query->where('store_label', $storeLabel);
    }

    /**
     * Scope a query to filter by review week (week_start_date range).
     */
    public function scopeDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('week_start_date', [$startDate, $endDate]);
        }

        if ($startDate) {
            return $query->where('week_start_date', '>=', $startDate);
        }

        if ($endDate) {
            return $query->where('week_start_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to filter by manager approval decision.
     */
    public function scopeWithApproval($query, ?string $approval)
    {
        if ($approval) {
            return $query->where('manager_approval', $approval);
        }

        return $query;
    }

    /**
     * Get CSV exportable columns.
     */
    public static function getCsvColumns(): array
    {
        return [
            'id',
            'cognito_id',
            'entry_number',
            'store_manager_first_name',
            'store_manager_last_name',
            'todays_date',
            'shift',
            'store_label',
            'issue_details',
            'review_aspects',
            'week_start_date',
            'week_end_date',
            'manager_first_name',
            'manager_last_name',
            'manager_approval',
            'decision_notes',
            'created_at',
            'updated_at',
        ];
    }
}
