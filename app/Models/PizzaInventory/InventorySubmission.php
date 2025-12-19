<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySubmission extends Model
{
    protected $table = 'inventory_submissions';

    protected $fillable = [
        'external_submission_number',
        'emp_name',
        'store_manager_name',
        'store',
        'email',
        'phone',
        'date',
        'inventory_type',
        'is_accepted',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_accepted' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'submission_id');
    }
}
