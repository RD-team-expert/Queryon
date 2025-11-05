<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class FlexShift extends Model
{
    protected $table = 'flex_shifts';
    protected $fillable = ['shift_name'];

    public function confirmsShifts()
    {
        return $this->hasMany(ConfirmsShift::class);
    }
}
