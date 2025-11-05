<?php

namespace Pizza\HR_Department\Models;

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
