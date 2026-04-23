<?php

namespace App\Models\FieldMissionModels;

use Illuminate\Database\Eloquent\Model;

class FieldMission extends Model
{
    protected $fillable = [
        'entry_id',
        'team',
        'finance_name',
        'payment_for_week',
        'employee_name',
        'total_hour',
        'hour_pay',
        'mony_owed',
        'total_pay',
        'total_deduction',
        'net_pay',
        'miles2',
        'fuel',
        'submitted_at',
    ];

    public function invoices()
    {
        return $this->hasMany(MaintenanceInvoice::class);
    }

    protected $casts = [
        'submitted_at' => 'datetime',
    ];
}
