<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $table = 'submissions';
    protected $primaryKey = 'submission_id';
    public $incrementing = false;

    protected $fillable = [
        'submission_id',
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
        return $this->hasMany(Item::class, 'submission_id', 'submission_id');
    }
}
