<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryType extends Model
{
    protected $table = 'inventory_types';
    protected $fillable = ['name'];

    public function inventoriesItems()
    {
        return $this->hasMany(InventoriesItem::class);
    }
}
