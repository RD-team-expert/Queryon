<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'approvals';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Cognito Form Info
        'cognito_id',
        'form_id',
        'form_internal_name',
        'form_name',
        
        // Details Section
        'approval_reason',
        'why',
        'requester_first_name',
        'requester_last_name',
        'request_date',
        'store_id',
        'store_label',
        'consulted_manager_first_name',
        'consulted_manager_last_name',
        
        // The Final Decision Section
        'decision',
        'decision_notes',
        
        // Entry Metadata
        'entry_number',
        'entry_admin_link',
        'entry_date_created',
        'entry_date_submitted',
        'entry_date_updated',
        'entry_public_link',
        'entry_final_view_link',
        'document_1_link',
        'document_2_link',
        
        // Entry Origin Info
        'origin_ip_address',
        'origin_city',
        'origin_country_code',
        'origin_region',
        'origin_timezone',
        'origin_user_agent',
        'origin_is_imported',
        
        // Entry User Info
        'user_email',
        'user_name',
        
        // Entry Status
        'entry_action',
        'entry_role',
        'entry_status',
        'entry_version',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'request_date' => 'date',
        'entry_date_created' => 'datetime',
        'entry_date_submitted' => 'datetime',
        'entry_date_updated' => 'datetime',
        'origin_is_imported' => 'boolean',
        'entry_number' => 'integer',
        'entry_version' => 'integer',
    ];

    /**
     * Get the requester's full name.
     */
    public function getRequesterFullNameAttribute(): string
    {
        return trim("{$this->requester_first_name} {$this->requester_last_name}");
    }

    /**
     * Get the consulted manager's full name.
     */
    public function getConsultedManagerFullNameAttribute(): string
    {
        return trim("{$this->consulted_manager_first_name} {$this->consulted_manager_last_name}");
    }

    /**
     * Scope a query to only include approvals with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('entry_status', $status);
    }

    /**
     * Scope a query to only include approvals for a specific store.
     */
    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('request_date', [$startDate, $endDate]);
        }
        
        if ($startDate) {
            return $query->where('request_date', '>=', $startDate);
        }
        
        if ($endDate) {
            return $query->where('request_date', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Scope a query to filter by decision.
     */
    public function scopeWithDecision($query, ?string $decision)
    {
        if ($decision) {
            return $query->where('decision', $decision);
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
            'form_id',
            'form_name',
            'approval_reason',
            'why',
            'requester_first_name',
            'requester_last_name',
            'request_date',
            'store_id',
            'store_label',
            'consulted_manager_first_name',
            'consulted_manager_last_name',
            'decision',
            'decision_notes',
            'entry_number',
            'entry_admin_link',
            'entry_date_created',
            'entry_date_submitted',
            'entry_date_updated',
            'entry_public_link',
            'entry_final_view_link',
            'document_1_link',
            'document_2_link',
            'origin_ip_address',
            'origin_city',
            'origin_country_code',
            'user_email',
            'user_name',
            'entry_action',
            'entry_role',
            'entry_status',
            'entry_version',
            'created_at',
            'updated_at',
        ];
    }
}
