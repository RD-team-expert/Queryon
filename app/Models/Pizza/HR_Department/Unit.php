<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function inventoriesItems()
    {
        return $this->hasMany(InventoriesItem::class);
    }
}
