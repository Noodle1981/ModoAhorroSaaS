<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';      // Indicamos a Laravel cuál es la PK.
    public $incrementing = false;       // Le decimos que no es un número que se autoincrementa.
    protected $keyType = 'string';      // Le decimos que es de tipo string.
    public $timestamps = false;         // No usa timestamps.

    protected $fillable = [
        'key',
        'value',
        'description',
    ];
}