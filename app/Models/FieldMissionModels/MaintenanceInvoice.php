<?php

namespace App\Models\FieldMissionModels;

use Illuminate\Database\Eloquent\Model;

class MaintenanceInvoice extends Model
{
    protected $fillable = [
        'field_mission_id',
        'file_name',
        'file_url',
    ];

    public function fieldMission()
    {
        return $this->belongsTo(FieldMission::class);
    }
}
