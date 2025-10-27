<?php

namespace App\Models\Hiring;

use Illuminate\Database\Eloquent\Model;

class HiringSeparation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hiring_separations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_manager_first_name',
        'store_manager_last_name',
        'franchisee_store',
        'date_of_request',
        'pizza_emp_first_name',
        'pizza_emp_last_name',
        'pizza_emp_paychex_id',
        'separation_type',
        'final_w_date',
        'supervisor_first_name',
        'supervisor_last_name',
        'supervisor_accepted',
        'hiring_specialist_first_name',
        'hiring_specialist_last_name',
        'hiring_completed_separation',
        'hiring_date_finished',
        'cognito_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_request' => 'date',
            'final_w_date' => 'date',
            'hiring_date_finished' => 'date',
            'supervisor_accepted' => 'boolean',
            'hiring_completed_separation' => 'boolean',
            'cognito_id' => 'integer',
        ];
    }
}
