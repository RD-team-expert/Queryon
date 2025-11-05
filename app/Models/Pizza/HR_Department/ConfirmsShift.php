<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmsShift extends Model
{
    protected $table = 'confirms_shifts';
    protected $fillable = ['flex_confirm_id', 'flex_shift_id'];

    public function flexConfirm()
    {
        return $this->belongsTo(FlexConfirm::class);
    }

    public function flexShift()
    {
        return $this->belongsTo(FlexShift::class);
    }
}
