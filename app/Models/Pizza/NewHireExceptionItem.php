<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewHireExceptionItem extends Model
{
    protected $table = 'new_hire_exception_items';
    public $timestamps = false;

    protected $fillable = [
        'new_hire_exception_id',
        'name_full',
        'start_date',
        'shifts_worked',
        'hours_worked',
        'feedback',
        'hours_exception',
    ];

    protected $casts = [
        'start_date' => 'date',
        'shifts_worked' => 'integer',
        'hours_worked' => 'decimal:2',
        'hours_exception' => 'decimal:2',
    ];

    /**
     * Get the new hire exception entry that owns this row.
     */
    public function newHireException(): BelongsTo
    {
        return $this->belongsTo(NewHireException::class, 'new_hire_exception_id');
    }
}
