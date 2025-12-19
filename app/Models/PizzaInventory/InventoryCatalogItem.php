<?php

namespace App\Models\PizzaInventory;

use Illuminate\Database\Eloquent\Model;

class InventoryCatalogItem extends Model
{
    protected $table = 'inventory_catalog_items';

    protected $fillable = [
        'item_key',
        'item_name',
    ];
}
