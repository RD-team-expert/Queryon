<?php

namespace App\Models\Pizza\HR_Department;

use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    protected $fillable = ['name', 'language_id'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function formRequests()
    {
        return $this->hasMany(FormRequest::class);
    }
}
