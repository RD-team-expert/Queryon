<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PizzaCoachingActionPlan extends Model
{
    protected $table = 'pizza_coaching_action_plans';

    // Define cognito_id as primary key (non-auto-increment)
    protected $primaryKey = 'cognito_id';

    // Tell Laravel it's not an auto-incrementing key
    public $incrementing = false;

    // Define the key type as string
    protected $keyType = 'string';

    protected $fillable = [
        'cognito_id',
        'manager_first_name',
        'manager_last_name',
        'store',
        'emp_first_name',
        'emp_last_name',
        'description_of_the_incident',
        'coaching_plan',
        'date',
        'cap_type',
        're_evaluation_after',
        'director_first_name',
        'director_last_name',
        'director_is_accepted',
        'director_rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',

    ];

    /**
     * Get the actions for the action plan.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(PizzaCoachingAction::class, 'cognito_id', 'cognito_id');
    }
}
