<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LittleCaesarsHrDepartmentData extends Model
{
    use HasFactory;

    // Specify the table name explicitly since it doesn't follow Laravel naming conventions
    protected $table = 'LITTLECAESARSHRDEPARTMENT_Data';

    // Define fillable columns for mass assignment
    protected $fillable = [
        'HookLanguage',
        'HookStore',
        'Hookالمتجر',
        'HookAlmacenar',
        'HookSelectYourRequestType',
        'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات',
        'HookSeleccioneSuTipoDeSolicitud',
        'EntryNum',
        'DateSubmitted',
    ];

    // Define date fields to be cast as Carbon instances
    protected $dates = [
        'DateSubmitted',
        'created_at',
        'updated_at',
    ];

    // Define casts for specific fields
    protected $casts = [
        'EntryNum' => 'integer',
        'DateSubmitted' => 'date',
    ];
}