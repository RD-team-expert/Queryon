<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class FlexConfirm extends Model
{
    protected $table = 'flex_confirms';
    protected $fillable = ['form_request_id', 'is_available_for_shift'];

    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function confirmsShifts()
    {
        return $this->hasMany(ConfirmsShift::class);
    }
}
