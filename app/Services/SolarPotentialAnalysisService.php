<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Collection;

class SolarPotentialAnalysisService
{
    /**
     * Radiación solar promedio por provincia en Argentina (kWh/m²/día)
     * Fuente: Atlas Solar Argentino - Secretaría de Energía
     */
    protected const SOLAR_RADIATION = [
        // Patagonia (excelente)
        'chubut' => ['summer' => 7.2, 'winter' => 3.5, 'annual' => 5.4],
        'santa cruz' => ['summer' => 7.0, 'winter' => 3.3, 'annual' => 5.2],
        'tierra del fuego' => ['summer' => 6.0, 'winter' => 2.0, 'annual' => 4.0],
        'neuquén' => ['summer' => 7.0, 'winter' => 3.8, 'annual' => 5.4],
        'río negro' => ['summer' => 7.2, 'winter' => 3.9, 'annual' => 5.6],
        'rio negro' => ['summer' => 7.2, 'winter' => 3.9, 'annual' => 5.6],
        
        // Cuyo (muy buena)
        'mendoza' => ['summer' => 7.5, 'winter' => 4.2, 'annual' => 5.9],
        'san juan' => ['summer' => 7.8, 'winter' => 4.5, 'annual' => 6.2],
        'san luis' => ['summer' => 7.3, 'winter' => 4.0, 'annual' => 5.7],
        'la rioja' => ['summer' => 7.6, 'winter' => 4.3, 'annual' => 6.0],
        
        // NOA (excelente)
        'jujuy' => ['summer' => 7.4, 'winter' => 5.0, 'annual' => 6.2],
        'salta' => ['summer' => 7.5, 'winter' => 5.1, 'annual' => 6.3],
        'catamarca' => ['summer' => 7.7, 'winter' => 4.8, 'annual' => 6.3],
        'tucumán' => ['summer' => 7.0, 'winter' => 4.5, 'annual' => 5.8],
        'santiago del estero' => ['summer' => 7.2, 'winter' => 4.4, 'annual' => 5.8],
        
        // NEA (buena)
        'formosa' => ['summer' => 6.8, 'winter' => 4.2, 'annual' => 5.5],
        'chaco' => ['summer' => 6.7, 'winter' => 4.1, 'annual' => 5.4],
        'corrientes' => ['summer' => 6.5, 'winter' => 3.8, 'annual' => 5.2],
        'misiones' => ['summer' => 6.3, 'winter' => 3.7, 'annual' => 5.0],
        
        // Centro (buena)
        'córdoba' => ['summer' => 7.0, 'winter' => 4.0, 'annual' => 5.5],
        'santa fe' => ['summer' => 6.8, 'winter' => 3.8, 'annual' => 5.3],
        'entre ríos' => ['summer' => 6.6, 'winter' => 3.6, 'annual' => 5.1],
        'entre rios' => ['summer' => 6.6, 'winter' => 3.6, 'annual' => 5.1],
        'la pampa' => ['summer' => 7.1, 'winter' => 3.7, 'annual' => 5.4],
        
        // Buenos Aires (moderada)
        'buenos aires' => ['summer' => 6.5, 'winter' => 3.2, 'annual' => 4.9],
        'ciudad autónoma de buenos aires' => ['summer' => 6.5, 'winter' => 3.2, 'annual' => 4.9],
        'caba' => ['summer' => 6.5, 'winter' => 3.2, 'annual' => 4.9],
    ];

    /**
     * Generación distribuida permitida por provincia
     */
    protected const DISTRIBUTED_GENERATION = [
        'buenos aires' => ['allowed' => true, 'law' => 'Ley 14.814', 'max_kwp' => 100],
        'caba' => ['allowed' => true, 'law' => 'Ley 6.352', 'max_kwp' => 100],
        'córdoba' => ['allowed' => true, 'law' => 'Ley 10.604', 'max_kwp' => 150],
        'santa fe' => ['allowed' => true, 'law' => 'Ley 13.903', 'max_kwp' => 300],
        'mendoza' => ['allowed' => true, 'law' => 'Ley 9.042', 'max_kwp' => 150],
        'san juan' => ['allowed' => true, 'law' => 'Ley 1.642-A', 'max_kwp' => 300],
        'salta' => ['allowed' => true, 'law' => 'Ley 7.824', 'max_kwp' => 50],
        'tucumán' => ['allowed' => true, 'law' => 'Ley 9.311', 'max_kwp' => 100],
        'neuquén' => ['allowed' => true, 'law' => 'Ley 3.182', 'max_kwp' => 150],
        // Agregar más provincias según avanza la legislación
    ];

    /**
     * Analiza el potencial solar de una entidad
     */
    public function analyzePotential(Entity $entity): array
    {
        $result = [
            'has_data' => false,
            'roof_area_m2' => $entity->roof_area_m2,
            'ground_area_m2' => $entity->ground_area_m2,
            'roof_usable_m2' => 0,
            'ground_usable_m2' => 0,
            'total_usable_m2' => 0,
            'roof_kwp' => 0,
            'ground_kwp' => 0,
            'total_kwp' => 0,
            'solar_radiation' => null,
            'generation_summer_kwh_month' => 0,
            'generation_winter_kwh_month' => 0,
            'generation_annual_kwh' => 0,
            'consumption_annual_kwh' => 0,
            'coverage_percent' => 0,
            'surplus_kwh' => 0,
            'distributed_gen_allowed' => false,
            'distributed_gen_info' => null,
            'investment_ars' => 0,
            'payback_years' => 0,
            'savings_annual_ars' => 0,
            'co2_avoided_kg' => 0,
            'recommendation' => null,
        ];

        // Verificar si tiene datos de techo o terreno
        $hasRoofData = $entity->roof_area_m2 && $entity->roof_area_m2 > 0;
        $hasGroundData = $entity->ground_area_m2 && $entity->ground_area_m2 > 0;

        if (!$hasRoofData && !$hasGroundData) {
            $result['recommendation'] = 'need_space_data';
            return $result;
        }

        $result['has_data'] = true;

        // 1. Calcular área útil de techo
        if ($hasRoofData) {
            $result['roof_usable_m2'] = $this->calculateRoofUsableArea($entity);
            $result['roof_kwp'] = $this->calculateRoofInstallableKwp($result['roof_usable_m2'], $entity);
        }

        // 2. Calcular área útil de terreno
        if ($hasGroundData) {
            $result['ground_usable_m2'] = $this->calculateGroundUsableArea($entity);
            $result['ground_kwp'] = $this->calculateGroundInstallableKwp($result['ground_usable_m2'], $entity);
        }

        // 3. Totales
        $result['total_usable_m2'] = $result['roof_usable_m2'] + $result['ground_usable_m2'];
        $result['total_kwp'] = $result['roof_kwp'] + $result['ground_kwp'];

        if ($result['total_usable_m2'] < 10) {
            $result['recommendation'] = 'insufficient_space';
            return $result;
        }

        // 2. Obtener radiación solar por provincia
        $result['solar_radiation'] = $this->getSolarRadiation($entity);

        // 3. Calcular generación estimada (usando total_kwp)
        $generation = $this->calculateGeneration(
            $result['total_kwp'],
            $result['solar_radiation'],
            $entity
        );
        $result['generation_summer_kwh_month'] = $generation['summer_month'];
        $result['generation_winter_kwh_month'] = $generation['winter_month'];
        $result['generation_annual_kwh'] = $generation['annual'];

        // 4. Obtener consumo actual de la entidad
        $result['consumption_annual_kwh'] = $this->getAnnualConsumption($entity);

        // 5. Calcular cobertura y excedentes
        if ($result['consumption_annual_kwh'] > 0) {
            $result['coverage_percent'] = round(($result['generation_annual_kwh'] / $result['consumption_annual_kwh']) * 100, 1);
            $result['surplus_kwh'] = max(0, $result['generation_annual_kwh'] - $result['consumption_annual_kwh']);
        }

        // 6. Verificar generación distribuida
        $distributedGen = $this->getDistributedGenerationInfo($entity);
        $result['distributed_gen_allowed'] = $distributedGen['allowed'];
        $result['distributed_gen_info'] = $distributedGen;

        // 7. Calcular inversión y ROI (usando desglose)
        $result['investment_ars'] = $this->estimateTotalInvestment($result['roof_kwp'], $result['ground_kwp']);
        $result['savings_annual_ars'] = $this->calculateAnnualSavings($entity, $result);
        $result['payback_years'] = $result['savings_annual_ars'] > 0 
            ? round($result['investment_ars'] / $result['savings_annual_ars'], 1) 
            : 0;

        // 8. CO2 evitado
        $result['co2_avoided_kg'] = round($result['generation_annual_kwh'] * 0.4, 0);

        // 9. Generar recomendación
        $result['recommendation'] = $this->generateRecommendation($result);

        return $result;
    }

    /**
     * Calcula el área útil disponible en TECHO
     */
    protected function calculateRoofUsableArea(Entity $entity): float
    {
        $totalArea = $entity->roof_area_m2;

        // Descontar obstáculos reportados por usuario
        $obstaclesPercent = $entity->roof_obstacles_percent ?? 0;
        $usableArea = $totalArea * (1 - $obstaclesPercent / 100);

        // Si tiene calefón solar sugerido/instalado, descontar ~4m²
        if ($entity->solar_heater_interest || $entity->current_heater_type === 'solar') {
            $usableArea -= 4;
        }

        return max(0, $usableArea);
    }

    /**
     * Calcula el área útil disponible en TERRENO
     */
    protected function calculateGroundUsableArea(Entity $entity): float
    {
        $totalArea = $entity->ground_area_m2 ?? 0;

        if ($totalArea <= 0) {
            return 0;
        }

        // Descontar por sombras de árboles
        $shadePercent = $entity->ground_shade_percent ?? 0;
        $usableArea = $totalArea * (1 - $shadePercent / 100);

        return max(0, $usableArea);
    }

    /**
     * Calcula kWp instalables en TECHO
     */
    protected function calculateRoofInstallableKwp(float $usableAreaM2, Entity $entity): float
    {
        if ($usableAreaM2 <= 0) {
            return 0;
        }

        // Panel típico: 7 m² por kWp (incluye espacios de mantenimiento)
        $m2PerKwp = 7;
        $maxKwp = $usableAreaM2 / $m2PerKwp;

        // Ajuste por orientación
        $orientation = $entity->roof_orientation ?? 'N';
        $orientationFactor = $this->getOrientationFactor($orientation);

        // Ajuste por inclinación
        $slope = $entity->roof_slope_degrees ?? 30;
        $slopeFactor = $this->getSlopeFactor($slope, $entity);

        return round($maxKwp * $orientationFactor * $slopeFactor, 2);
    }

    /**
     * Calcula kWp instalables en TERRENO
     */
    protected function calculateGroundInstallableKwp(float $usableAreaM2, Entity $entity): float
    {
        if ($usableAreaM2 <= 0) {
            return 0;
        }

        // En terreno se necesita más espacio por la estructura (8 m² por kWp)
        $m2PerKwp = 8;
        $maxKwp = $usableAreaM2 / $m2PerKwp;

        // Los paneles en suelo siempre se orientan óptimamente (factor 1.0)
        // Pero pueden tener más sombras
        $shadeFactor = 1.0;
        if ($entity->ground_has_trees) {
            $shadeFactor = 0.85; // 15% menos eficiente por sombras variables
        }

        return round($maxKwp * $shadeFactor, 2);
    }

    /**
     * Obtiene radiación solar según provincia
     */
    protected function getSolarRadiation(Entity $entity): array
    {
        if (!$entity->locality || !$entity->locality->province) {
            // Default nacional moderado
            return ['summer' => 6.5, 'winter' => 3.5, 'annual' => 5.0];
        }

        $province = strtolower($entity->locality->province->name);

        foreach (self::SOLAR_RADIATION as $key => $data) {
            if (str_contains($province, $key)) {
                return $data;
            }
        }

        return ['summer' => 6.0, 'winter' => 3.5, 'annual' => 4.8];
    }

    /**
     * DEPRECATED: Mantener por compatibilidad, ahora usar calculateRoofInstallableKwp
     */
    protected function calculateInstallableKwp(float $usableAreaM2, Entity $entity): float
    {
        return $this->calculateRoofInstallableKwp($usableAreaM2, $entity);
    }

    /**
     * Factor de ajuste por orientación del techo
     */
    protected function getOrientationFactor(string $orientation): float
    {
        return match(strtoupper($orientation)) {
            'N' => 1.0,    // Óptimo en Argentina (hemisferio sur)
            'NE', 'NO' => 0.95,
            'E', 'O' => 0.85,
            'SE', 'SO' => 0.75,
            'S' => 0.60,   // Menos eficiente
            default => 0.90,
        };
    }

    /**
     * Factor de ajuste por inclinación
     */
    protected function getSlopeFactor(int $degrees, Entity $entity): float
    {
        $latitude = $this->getLatitude($entity);

        // Inclinación óptima = latitud ± 10°
        $optimalSlope = abs($latitude);

        $diff = abs($degrees - $optimalSlope);

        if ($diff <= 10) return 1.0;
        if ($diff <= 20) return 0.95;
        if ($diff <= 30) return 0.90;
        return 0.85;
    }

    /**
     * Estima latitud según provincia
     */
    protected function getLatitude(Entity $entity): float
    {
        if (!$entity->locality || !$entity->locality->province) {
            return -34.0; // CABA default
        }

        $province = strtolower($entity->locality->province->name);

        $latitudes = [
            'jujuy' => -24, 'salta' => -25, 'formosa' => -26,
            'chaco' => -27, 'corrientes' => -28, 'misiones' => -27,
            'tucumán' => -27, 'catamarca' => -28, 'santiago del estero' => -28,
            'santa fe' => -31, 'entre rios' => -31, 'córdoba' => -31,
            'san juan' => -31, 'la rioja' => -29, 'mendoza' => -33,
            'san luis' => -33, 'buenos aires' => -35, 'caba' => -34,
            'la pampa' => -37, 'neuquén' => -39, 'río negro' => -41,
            'chubut' => -43, 'santa cruz' => -50, 'tierra del fuego' => -54,
        ];

        foreach ($latitudes as $key => $lat) {
            if (str_contains($province, $key)) {
                return $lat;
            }
        }

        return -34.0;
    }

    /**
     * Calcula generación estimada en kWh
     */
    protected function calculateGeneration(float $kwp, array $radiation, Entity $entity): array
    {
        // HSP (Horas Sol Pico) = radiación diaria
        $hspSummer = $radiation['summer'];
        $hspWinter = $radiation['winter'];
        $hspAnnual = $radiation['annual'];

        // Performance Ratio: típico 0.75-0.85 (pérdidas por temperatura, cableado, inversor)
        $pr = 0.80;

        // Ajuste por sombras
        if ($entity->has_shading && $entity->shading_hours_daily) {
            $shadingLoss = min($entity->shading_hours_daily / 8, 0.4); // Max 40% pérdida
            $pr -= $shadingLoss;
        }

        // Generación diaria = kWp × HSP × PR
        $dailySummer = $kwp * $hspSummer * $pr;
        $dailyWinter = $kwp * $hspWinter * $pr;
        $dailyAnnual = $kwp * $hspAnnual * $pr;

        return [
            'summer_month' => round($dailySummer * 30, 0),
            'winter_month' => round($dailyWinter * 30, 0),
            'annual' => round($dailyAnnual * 365, 0),
        ];
    }

    /**
     * Obtiene consumo anual de la entidad
     */
    protected function getAnnualConsumption(Entity $entity): float
    {
        // Desde facturas reales
        $invoices = $entity->invoices()
            ->where('period_start', '>=', now()->subYear())
            ->get();

        if ($invoices->isNotEmpty()) {
            return $invoices->sum('consumption_kwh');
        }

        // Desde inventario de equipos
        $equipments = $entity->equipments()->with('equipmentType')->get();
        $totalKwh = 0;

        foreach ($equipments as $eq) {
            $powerKw = $eq->power_watts_override / 1000;
            $dailyHours = $eq->avg_daily_use_minutes_override / 60;
            $quantity = $eq->quantity;

            $totalKwh += $powerKw * $dailyHours * 365 * $quantity;
        }

        return round($totalKwh, 0);
    }

    /**
     * Info sobre generación distribuida en la provincia
     */
    protected function getDistributedGenerationInfo(Entity $entity): array
    {
        if (!$entity->locality || !$entity->locality->province) {
            return ['allowed' => false, 'law' => null, 'max_kwp' => 0];
        }

        $province = strtolower($entity->locality->province->name);

        foreach (self::DISTRIBUTED_GENERATION as $key => $data) {
            if (str_contains($province, $key)) {
                return $data;
            }
        }

        return ['allowed' => false, 'law' => 'Consultar legislación provincial', 'max_kwp' => 0];
    }

    /**
     * Estima inversión total (techo + terreno)
     */
    protected function estimateTotalInvestment(float $roofKwp, float $groundKwp): float
    {
        // Techo: $1.200.000/kWp instalado (incluye paneles, inversor, estructura, instalación)
        $costPerKwpRoof = 1200000;
        
        // Terreno: +25% por estructura adicional
        $costPerKwpGround = 1500000;

        $roofInvestment = $roofKwp * $costPerKwpRoof;
        $groundInvestment = $groundKwp * $costPerKwpGround;

        return round($roofInvestment + $groundInvestment, 0);
    }

    /**
     * DEPRECATED: Mantener por compatibilidad
     */
    protected function estimateInvestment(float $kwp): float
    {
        return round($kwp * 1200000, 0);
    }

    /**
     * Calcula ahorro anual considerando venta de excedentes
     */
    protected function calculateAnnualSavings(Entity $entity, array $analysis): float
    {
        $generation = $analysis['generation_annual_kwh'];
        $consumption = $analysis['consumption_annual_kwh'];

        // Precio promedio del kWh
        $priceKwh = $this->getAveragePricePerKwh($entity);

        // Ahorro por autoconsumo
        $selfConsumption = min($generation, $consumption);
        $savings = $selfConsumption * $priceKwh;

        // Si hay generación distribuida, vender excedentes
        if ($analysis['distributed_gen_allowed'] && $analysis['surplus_kwh'] > 0) {
            // Típicamente se vende a 70-80% del precio de compra
            $sellPrice = $priceKwh * 0.75;
            $savings += $analysis['surplus_kwh'] * $sellPrice;
        }

        return round($savings, 0);
    }

    /**
     * Precio promedio del kWh
     */
    protected function getAveragePricePerKwh(Entity $entity): float
    {
        $recentInvoices = $entity->invoices()
            ->where('period_start', '>=', now()->subMonths(6))
            ->get();

        if ($recentInvoices->isEmpty()) {
            return 150; // Default ARS/kWh
        }

        $totalCost = $recentInvoices->sum('total_amount');
        $totalKwh = $recentInvoices->sum('consumption_kwh');

        return $totalKwh > 0 ? ($totalCost / $totalKwh) : 150;
    }

    /**
     * Genera recomendación
     */
    protected function generateRecommendation(array $analysis): string
    {
        if (!isset($analysis['has_data']) || !$analysis['has_data']) {
            return 'need_roof_data';
        }

        if ($analysis['usable_area_m2'] < 10) {
            return 'insufficient_space';
        }

        if ($analysis['coverage_percent'] >= 80 && $analysis['payback_years'] <= 7) {
            return 'highly_recommended';
        }

        if ($analysis['coverage_percent'] >= 50 && $analysis['payback_years'] <= 10) {
            return 'recommended';
        }

        if ($analysis['coverage_percent'] >= 30) {
            return 'consider';
        }

        return 'partial_coverage';
    }
}
