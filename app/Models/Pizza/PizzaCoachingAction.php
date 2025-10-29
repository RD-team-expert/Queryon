<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PizzaCoachingAction extends Model
{
    protected $table = 'pizza_coaching_actions';

    protected $fillable = [
        'cognito_id',
        'action_name',
    ];

    /**
     * Get the action plan that owns the action.
     */
    public function actionPlan(): BelongsTo
    {
        return $this->belongsTo(PizzaCoachingActionPlan::class, 'cognito_id', 'cognito_id');
    }
}
