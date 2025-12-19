<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItemUnit extends Model
{
    protected $table = 'inventory_item_units';

    protected $fillable = [
        'item_id',
        'unit_key',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
