<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function inventoriesItems()
    {
        return $this->hasMany(InventoriesItem::class);
    }
}
