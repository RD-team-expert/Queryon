<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'submission_id',
        'item',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'submission_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ItemUnit::class, 'item_id');
    }
}
