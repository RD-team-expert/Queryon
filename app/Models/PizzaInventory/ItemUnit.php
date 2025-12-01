<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnit extends Model
{
    protected $table = 'item_units';

    protected $fillable = [
        'item_id',
        'name',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
