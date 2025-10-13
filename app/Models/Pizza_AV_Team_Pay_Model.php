<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pizza_AV_Team_Pay_Model extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pizza_av_team_pay';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store',
        'date',
        'emp_id',
        'name',
        'position',
        'hourly_pay',
        'total_hours',
        'total_tips',
        'positive',
        'money_owed',
        'amazon_wm_others',
        'base_pay',
        'performance_bonus',
        'gross_pay',
        'team_profit_sharing',
        'bread_boost_bonus',
        'extra_pay',
        'total_deduction',
        'tax_allowans',
        'rent_pmt',
        'phone_pmt',
        'utilities',
        'others',
        'company_loan',
        'legal',
        'car',
        'labor',
        'lc_audit',
        'customer_service',
        'upselling',
        'inventory',
        'pne_audit_fail',
        'sales',
        'final_score',
        'total_tax',
        'tax_dif',
        'at',
        'apt_cost',
        'apt_cost_per_store',
        'utilities_cost',
        'phone_cost',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'at' => 'boolean',
        'hourly_pay' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'total_tips' => 'decimal:2',
        'positive' => 'decimal:2',
        'money_owed' => 'decimal:2',
        'amazon_wm_others' => 'decimal:2',
        'base_pay' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'team_profit_sharing' => 'decimal:2',
        'bread_boost_bonus' => 'decimal:2',
        'extra_pay' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'tax_allowans' => 'decimal:2',
        'rent_pmt' => 'decimal:2',
        'phone_pmt' => 'decimal:2',
        'utilities' => 'decimal:2',
        'others' => 'decimal:2',
        'company_loan' => 'decimal:2',
        'legal' => 'decimal:2',
        'car' => 'decimal:2',
        'labor' => 'decimal:2',
        'customer_service' => 'decimal:2',
        'upselling' => 'decimal:2',
        'inventory' => 'decimal:2',
        'pne_audit_fail' => 'decimal:2',
        'sales' => 'decimal:2',
        'final_score' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'tax_dif' => 'decimal:2',
        'apt_cost' => 'decimal:2',
        'apt_cost_per_store' => 'decimal:2',
        'utilities_cost' => 'decimal:2',
        'phone_cost' => 'decimal:2',
    ];
}
