<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'calculation_method',
        'description',
        'supports_standby',
        'is_portable',
    ];

    protected $casts = [
        'supports_standby' => 'boolean',
        'is_portable' => 'boolean',
    ];

    /**
     * Define la relación: Una categoría TIENE MUCHOS tipos de equipo.
     * El nombre 'equipmentTypes' debe coincidir con el usado en with().
     */
    public function equipmentTypes(): HasMany
    {
        return $this->hasMany(EquipmentType::class, 'category_id');
    }

    /**
     * Define la relación con los factores de cálculo.
     */
    public function calculationFactor(): HasOne
    {
        return $this->hasOne(CalculationFactor::class, 'method_name', 'calculation_method');
    }
}