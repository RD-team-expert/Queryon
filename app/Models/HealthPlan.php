<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthPlan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (By default Laravel expects `health_plans`, so we override here.)
     *
     * @var string
     */
    protected $table = 'health_plan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'store',
        'onboarding_offboarding',
        'working_start_date',
        'working_end_date',
        'reason',
        'form_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'working_start_date' => 'date',
        'working_end_date'   => 'date',
    ];

    // timestamps (created_at, updated_at) are enabled by default
}
