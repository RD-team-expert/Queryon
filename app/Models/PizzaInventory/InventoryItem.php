<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'submission_id',
        'item_key',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(InventorySubmission::class, 'submission_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(InventoryItemUnit::class, 'item_id');
    }

    public function catalog(): HasOne
    {
        return $this->hasOne(InventoryCatalogItem::class, 'item_key', 'item_key');
    }
}
