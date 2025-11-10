<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_de_proceso',
        'factor_carga',
        'eficiencia',
    ];

    public function entityEquipments()
    {
        return $this->hasMany(EntityEquipment::class, 'tipo_de_proceso', 'tipo_de_proceso');
    }
}
