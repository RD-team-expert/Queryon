<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class InventoriesItem extends Model
{
    protected $table = 'inventories_items';
    protected $fillable = [
        'form_request_id', 'inventory_type_id', 'unit_id',
        'name_of_item', 'value'
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function inventoryType()
    {
        return $this->belongsTo(InventoryType::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
