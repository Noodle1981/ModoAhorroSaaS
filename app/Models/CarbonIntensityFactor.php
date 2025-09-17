<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonIntensityFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'region',
        'energy_type',
        'factor',
        'unit',
        'valid_from',
        'valid_to',
        'source',
    ];
    
    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];
}