<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'max_entities',
        'allowed_entity_types',
    ];

    protected $casts = [
        'allowed_entity_types' => 'array', // ¡Muy importante para el campo JSON!
    ];
}