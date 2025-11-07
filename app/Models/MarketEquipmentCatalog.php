<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketEquipmentCatalog extends Model
{
    use HasFactory;

    protected $table = 'market_equipment_catalog';

    protected $fillable = [
        'equipment_type_id', 
        'brand', 
        'model_name', 
        'power_watts', 
        'annual_consumption_kwh',
        'energy_label',
        'efficiency_rating', 
        'estimated_price_ars', 
        'purchase_link', 
        'is_active',
        'is_recommended',
        'features',
    ];

    protected $casts = [
        'power_watts' => 'integer',
        'annual_consumption_kwh' => 'decimal:2',
        'estimated_price_ars' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    /**
     * Calcula el costo mensual estimado de operación
     * @param float $kwh_price Precio del kWh en ARS (default: promedio nacional)
     */
    public function calculateMonthlyCost(float $kwh_price = 150): float
    {
        if (!$this->annual_consumption_kwh) {
            return 0;
        }
        return ($this->annual_consumption_kwh / 12) * $kwh_price;
    }

    /**
     * Calcula ahorro anual comparando con otro equipo
     * @param MarketEquipmentCatalog $current_equipment Equipo actual
     * @param float $kwh_price Precio del kWh
     */
    public function calculateAnnualSavings(MarketEquipmentCatalog $current_equipment, float $kwh_price = 150): array
    {
        $kwh_saved = $current_equipment->annual_consumption_kwh - $this->annual_consumption_kwh;
        $money_saved = $kwh_saved * $kwh_price;
        $investment = $this->estimated_price_ars;
        
        $roi_months = $money_saved > 0 ? ($investment / ($money_saved / 12)) : null;

        return [
            'kwh_saved_per_year' => round($kwh_saved, 2),
            'money_saved_per_year' => round($money_saved, 2),
            'money_saved_per_month' => round($money_saved / 12, 2),
            'investment_required' => round($investment, 2),
            'roi_months' => $roi_months ? round($roi_months, 1) : null,
            'roi_years' => $roi_months ? round($roi_months / 12, 1) : null,
        ];
    }

    /**
     * Scope para equipos recomendados
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true)->where('is_active', true);
    }

    /**
     * Scope para buscar equipos por tipo
     */
    public function scopeByType($query, int $equipment_type_id)
    {
        return $query->where('equipment_type_id', $equipment_type_id);
    }
}