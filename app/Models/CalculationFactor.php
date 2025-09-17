<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculationFactor extends Model
{
    // No necesita factory, es un catálogo
    protected $fillable = ['method_name', 'load_factor', 'efficiency_factor'];
}