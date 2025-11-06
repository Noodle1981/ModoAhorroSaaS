<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityEquipment;
use Illuminate\Support\Collection;

class SolarHeaterAnalysisService
{
    /**
     * Analiza si una entidad tiene calefones y calcula el ahorro potencial con solar
     */
    public function analyzeEntity(Entity $entity): array
    {
        $result = [
            'has_heater' => false,
            'heater_type' => null,
            'detected_heaters' => [],
            'annual_consumption_kwh' => 0,
            'annual_cost_ars' => 0,
            'annual_gas_m3' => 0,
            'annual_gas_cost_ars' => 0,
            'annual_garrafas' => 0,
            'annual_wood_m3' => 0,
            'consumption_unit' => null,
            'co2_saved_kg' => 0,
            'comfort_benefit' => false,
            'savings_10_years' => 0,
            'savings_20_years' => 0,
            'calculation_method' => null, // 'electric', 'gas', 'estimated'
            'solar_savings_percent' => 70, // Asumimos 70% de ahorro típico
            'estimated_solar_cost_ars' => 0,
            'payback_years' => 0,
            'recommendation' => null,
        ];

        // Detectar calefones en el inventario
        $heaters = $this->detectHeaters($entity);
        
        if ($heaters->isEmpty()) {
            // Si no detectamos en inventario, verificar si el usuario lo declaró manualmente
            if ($entity->current_heater_type && $entity->current_heater_type !== 'none') {
                $result['has_heater'] = true;
                $result['heater_type'] = $entity->current_heater_type;
                $result['calculation_method'] = 'estimated';
                return $this->calculateEstimatedSavings($entity, $result);
            }
            
            $result['recommendation'] = 'no_heater_detected';
            return $result;
        }

        $result['has_heater'] = true;
        $result['detected_heaters'] = $heaters->map(function($h) {
            return [
                'name' => $h->custom_name ?? $h->equipmentType->name,
                'power_watts' => $h->power_watts_override,
                'daily_minutes' => $h->avg_daily_use_minutes_override,
                'quantity' => $h->quantity,
            ];
        })->toArray();

        // Determinar tipo de calefón detectado
        $result['heater_type'] = $this->inferHeaterType($heaters);

        // Calcular según el tipo
        if ($result['heater_type'] === 'electric') {
            $result['calculation_method'] = 'electric';
            return $this->calculateElectricSavings($entity, $heaters, $result);
        } else {
            $result['calculation_method'] = 'estimated';
            return $this->calculateEstimatedSavings($entity, $result);
        }
    }

    /**
     * Calcula ahorro para calefones eléctricos (basado en inventario real)
     */
    protected function calculateElectricSavings(Entity $entity, Collection $heaters, array $result): array
    {
        // Calcular consumo anual del calefón eléctrico
        $annualKwh = $this->calculateAnnualHeaterConsumption($heaters);
        $result['annual_consumption_kwh'] = round($annualKwh, 2);

        // Obtener costo promedio del kWh
        $avgPricePerKwh = $this->getAveragePricePerKwh($entity);
        $result['annual_cost_ars'] = round($annualKwh * $avgPricePerKwh, 2);

        $climate = $this->inferClimate($entity);
        
        // Calcular ROI para calefón solar
        $solarSystemCost = $this->estimateSolarHeaterCost($entity, $climate);
        $result['estimated_solar_cost_ars'] = $solarSystemCost;

        $annualSavings = $result['annual_cost_ars'] * ($result['solar_savings_percent'] / 100);
        $result['payback_years'] = $annualSavings > 0 ? round($solarSystemCost / $annualSavings, 1) : 0;

        // Calcular ahorro acumulado
        $result['savings_10_years'] = round($annualSavings * 10 - $solarSystemCost, 0);
        $result['savings_20_years'] = round($annualSavings * 20 - $solarSystemCost, 0);
        
        // CO2 ahorrado
        $result['co2_saved_kg'] = round($result['annual_consumption_kwh'] * 0.4 * 0.7, 0); // 0.4 kg CO2/kWh

        $result['recommendation'] = $this->generateRecommendation($result);

        return $result;
    }

    /**
     * Calcula ahorro para calefones a gas/leña/GLP (estimación basada en uso promedio)
     */
    protected function calculateEstimatedSavings(Entity $entity, array $result): array
    {
        $people = $entity->details['people'] ?? 4;
        $climate = $this->inferClimate($entity);
        
        // Ajuste por clima: zonas frías usan más agua caliente
        $climateMultiplier = match($climate) {
            'cold' => 1.3,      // Patagonia, cordillera
            'temperate' => 1.0, // Centro, litoral
            'hot' => 0.8,       // Norte, Cuyo
            default => 1.0,
        };
        
        if ($result['heater_type'] === 'gas') {
            // Consumo promedio: 25-35 m³/mes por persona para agua caliente
            $monthlyM3PerPerson = 30 * $climateMultiplier;
            $result['annual_gas_m3'] = round($monthlyM3PerPerson * $people * 12, 2);
            
            // Precio gas natural residencial Argentina 2025: ~$50-80/m³ (ajustar según tu región)
            $gasPrice = 65; // ARS por m³
            $result['annual_cost_ars'] = round($result['annual_gas_m3'] * $gasPrice, 2);
            $result['annual_gas_cost_ars'] = $result['annual_cost_ars'];
            
            // Info adicional
            $result['consumption_unit'] = 'm³ gas natural';
            $result['co2_saved_kg'] = round($result['annual_gas_m3'] * 2.3 * 0.7, 0); // 2.3 kg CO2/m³
            
        } elseif ($result['heater_type'] === 'glp') {
            // Consumo promedio GLP: 10-15 kg/mes por persona
            $monthlyKgPerPerson = 12 * $climateMultiplier;
            $annualKg = $monthlyKgPerPerson * $people * 12;
            
            // Garrafas de 10kg o 15kg
            $garrafaSize = 10; // kg
            $result['annual_garrafas'] = round($annualKg / $garrafaSize, 1);
            
            // Precio garrafa 10kg Argentina 2025: ~$6000-8000
            $garrafaPrice = 7000;
            $result['annual_cost_ars'] = round($result['annual_garrafas'] * $garrafaPrice, 2);
            
            // Info adicional
            $result['consumption_unit'] = 'garrafas de ' . $garrafaSize . 'kg';
            $result['co2_saved_kg'] = round($annualKg * 3.0 * 0.7, 0); // 3.0 kg CO2/kg GLP
            
        } elseif ($result['heater_type'] === 'wood') {
            // Consumo promedio leña: 2-3 m³/año por persona
            $annualM3Wood = 2.5 * $people * $climateMultiplier;
            
            // Precio leña Argentina 2025: ~$8000-12000/m³
            $woodPrice = 10000;
            $result['annual_cost_ars'] = round($annualM3Wood * $woodPrice, 2);
            $result['annual_wood_m3'] = round($annualM3Wood, 1);
            
            // Info adicional
            $result['consumption_unit'] = 'm³ de leña';
            $result['co2_saved_kg'] = 0; // Leña es neutral en CO2 (renovable)
            $result['comfort_benefit'] = true; // Flag para mensaje especial
            
        } else {
            // Fallback: estimación genérica basada en personas
            // Asumimos costo equivalente a electricidad
            $dailyKwhPerPerson = 3 * $climateMultiplier; // ~3 kWh/día/persona para agua caliente
            $result['annual_consumption_kwh'] = round($dailyKwhPerPerson * $people * 365, 2);
            
            $avgPricePerKwh = $this->getAveragePricePerKwh($entity);
            $result['annual_cost_ars'] = round($result['annual_consumption_kwh'] * $avgPricePerKwh, 2);
            
            $result['consumption_unit'] = 'kWh estimados';
            $result['co2_saved_kg'] = round($result['annual_consumption_kwh'] * 0.4 * 0.7, 0); // 0.4 kg CO2/kWh
        }

        // Calcular ROI para calefón solar
        $solarSystemCost = $this->estimateSolarHeaterCost($entity, $climate);
        $result['estimated_solar_cost_ars'] = $solarSystemCost;

        // Ahorro: el solar reemplaza 70% del consumo
        $annualSavings = $result['annual_cost_ars'] * ($result['solar_savings_percent'] / 100);
        $result['payback_years'] = $annualSavings > 0 ? round($solarSystemCost / $annualSavings, 1) : 0;

        // Calcular ahorro acumulado a 10 y 20 años
        $result['savings_10_years'] = round($annualSavings * 10 - $solarSystemCost, 0);
        $result['savings_20_years'] = round($annualSavings * 20 - $solarSystemCost, 0);

        $result['recommendation'] = $this->generateRecommendation($result);

        return $result;
    }
    
    /**
     * Infiere el clima según la provincia/localidad de la entidad
     */
    protected function inferClimate(Entity $entity): string
    {
        if (!$entity->locality || !$entity->locality->province) {
            return 'temperate';
        }
        
        $province = strtolower($entity->locality->province->name);
        
        // Zonas frías
        $coldProvinces = ['chubut', 'santa cruz', 'tierra del fuego', 'neuquén', 'río negro', 'rio negro', 'mendoza'];
        foreach ($coldProvinces as $cold) {
            if (str_contains($province, $cold)) {
                return 'cold';
            }
        }
        
        // Zonas cálidas
        $hotProvinces = ['jujuy', 'salta', 'formosa', 'chaco', 'santiago del estero', 'catamarca', 'la rioja'];
        foreach ($hotProvinces as $hot) {
            if (str_contains($province, $hot)) {
                return 'hot';
            }
        }
        
        return 'temperate';
    }

    /**
     * Detecta equipos que son calefones en el inventario
     */
    protected function detectHeaters(Entity $entity): Collection
    {
        return $entity->equipments()
            ->with('equipmentType')
            ->get()
            ->filter(function($equipment) {
                $name = strtolower($equipment->equipmentType->name ?? '');
                $customName = strtolower($equipment->custom_name ?? '');
                
                // Palabras clave para detectar calefones
                $keywords = ['calefon', 'calefón', 'termotanque', 'boiler', 'calentador de agua', 'water heater'];
                
                foreach ($keywords as $keyword) {
                    if (str_contains($name, $keyword) || str_contains($customName, $keyword)) {
                        return true;
                    }
                }
                
                return false;
            });
    }

    /**
     * Calcula consumo anual de los calefones detectados
     */
    protected function calculateAnnualHeaterConsumption(Collection $heaters): float
    {
        $totalKwh = 0;

        foreach ($heaters as $heater) {
            $powerKw = $heater->power_watts_override / 1000;
            $dailyHours = $heater->avg_daily_use_minutes_override / 60;
            $quantity = $heater->quantity;

            // Consumo diario * 365 días * cantidad
            $totalKwh += ($powerKw * $dailyHours * 365 * $quantity);
        }

        return $totalKwh;
    }

    /**
     * Infiere el tipo de calefón por la potencia
     */
    protected function inferHeaterType(Collection $heaters): string
    {
        $avgPower = $heaters->avg('power_watts_override');

        // Eléctrico típicamente > 1500W
        if ($avgPower >= 1500) {
            return 'electric';
        }

        // Si tiene potencia baja o cero, probablemente sea gas/leña
        return 'gas_or_other';
    }

    /**
     * Obtiene precio promedio del kWh de facturas recientes
     */
    protected function getAveragePricePerKwh(Entity $entity): float
    {
        $recentInvoices = $entity->invoices()
            ->where('period_start', '>=', now()->subMonths(6))
            ->get();

        if ($recentInvoices->isEmpty()) {
            return 150; // Default: $150/kWh (ajustar según tu país)
        }

        $totalCost = $recentInvoices->sum('total_amount');
        $totalKwh = $recentInvoices->sum('consumption_kwh');

        return $totalKwh > 0 ? ($totalCost / $totalKwh) : 150;
    }

    /**
     * Estima costo de instalación de calefón solar
     */
    protected function estimateSolarHeaterCost(Entity $entity, string $climate = 'temperate'): float
    {
        // Factores: tipo de vivienda, cantidad de personas, clima
        $people = $entity->details['people'] ?? 4;
        
        // Costo base por persona (ajustar a tu mercado)
        // En Argentina 2025: ~$500.000 a $800.000 sistema completo
        $costPerPerson = 150000;
        
        // Ajuste por clima: zonas frías requieren sistemas más robustos
        $climateMultiplier = match($climate) {
            'cold' => 1.2,      // Requiere mejor aislamiento y tubos de vacío
            'temperate' => 1.0,
            'hot' => 0.9,       // Menor demanda, sistemas más simples
            default => 1.0,
        };
        
        $baseCost = $costPerPerson * $people * $climateMultiplier;
        
        // Mínimo y máximo razonables
        return max(400000, min($baseCost, 1500000));
    }

    /**
     * Genera recomendación según análisis
     */
    protected function generateRecommendation(array $analysis): string
    {
        if (!$analysis['has_heater']) {
            return 'no_heater_detected';
        }

        // Para calefones eléctricos: ROI es el factor clave
        if ($analysis['heater_type'] === 'electric') {
            if ($analysis['payback_years'] <= 5) {
                return 'highly_recommended';
            } elseif ($analysis['payback_years'] <= 8) {
                return 'recommended';
            } else {
                return 'consider';
            }
        }

        // Para gas natural: considerar costo anual y ROI
        if ($analysis['heater_type'] === 'gas') {
            if ($analysis['annual_cost_ars'] > 200000 || $analysis['payback_years'] <= 6) {
                return 'highly_recommended';
            } elseif ($analysis['payback_years'] <= 9) {
                return 'recommended';
            } else {
                return 'consider';
            }
        }

        // Para GLP/garrafa: suele ser muy rentable
        if ($analysis['heater_type'] === 'glp') {
            if ($analysis['payback_years'] <= 5) {
                return 'highly_recommended';
            } elseif ($analysis['payback_years'] <= 8) {
                return 'recommended';
            } else {
                return 'consider';
            }
        }

        // Para leña: principalmente beneficio ecológico y comodidad
        if ($analysis['heater_type'] === 'wood') {
            if ($analysis['annual_cost_ars'] > 150000 || $analysis['payback_years'] <= 6) {
                return 'recommended';
            } else {
                return 'consider_comfort'; // Más por comodidad que por ahorro
            }
        }

        // Fallback genérico
        if ($analysis['payback_years'] <= 8) {
            return 'recommended';
        }

        return 'consider';
    }
}
