<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name'];

    public function requestTypes()
    {
        return $this->hasMany(RequestType::class);
    }

    public function formRequests()
    {
        return $this->hasMany(FormRequest::class);
    }
}
