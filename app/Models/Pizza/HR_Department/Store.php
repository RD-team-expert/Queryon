<?php

namespace Pizza\HR_Department\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'id',
        'name', 'franchise', 'store_email',
        'first_manager_email', 'second_manager_email'
    ];

    public $incrementing = false;
    protected $keyType = 'int';
    public function formRequests()
    {
        return $this->hasMany(FormRequest::class);
    }
}
