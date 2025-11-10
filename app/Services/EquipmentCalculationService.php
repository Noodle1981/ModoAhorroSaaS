<?php

namespace App\Services;

use App\Models\EntityEquipment;
use App\Models\Invoice;
use Carbon\Carbon;

/**
 * Servicio centralizado para cálculos de consumo energético y costos.
 * Implementa la lógica de Python usando tipo_de_proceso, factor_carga y eficiencia.
 */
class EquipmentCalculationService
{
    /**
     * Calcula el consumo de energía y costo de un equipo para un período dado.
     *
     * @param EntityEquipment $equipment
     * @param int $days Días del período
     * @param float $tariff Tarifa en $/kWh
     * @return array ['kwh_activo', 'kwh_standby', 'kwh_total', 'costo', 'horas_uso', 'horas_standby']
     */
    public function calculateEquipmentConsumption(EntityEquipment $equipment, int $days, float $tariff): array
    {
        // 1. Obtener parámetros del equipo
        $quantity = max(1, (int)($equipment->quantity ?? 1));
        $powerWatts = (int)($equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0);
        $powerKw = $powerWatts / 1000.0;
        
        // Horas de uso diario
        $dailyMinutes = $equipment->avg_daily_use_minutes_override 
            ?? (($equipment->equipmentType->default_avg_daily_use_hours ?? 0) * 60);
        $hoursPerDay = $dailyMinutes / 60.0;

        // Factores de carga y eficiencia
        // Preferimos los del equipo; si faltan, intentamos ProcessFactor por tipo_de_proceso
        $loadFactor = $equipment->factor_carga;
        $efficiency = $equipment->eficiencia;

        if (($loadFactor === null || $efficiency === null) && !empty($equipment->tipo_de_proceso)) {
            $pf = \App\Models\ProcessFactor::where('tipo_de_proceso', $equipment->tipo_de_proceso)->first();
            if ($pf) {
                $loadFactor = $loadFactor ?? (float)$pf->factor_carga;
                $efficiency = $efficiency ?? (float)$pf->eficiencia;
            }
        }

        // Defaults conservadores si aún no hay valores
        $loadFactor = $loadFactor !== null ? (float)$loadFactor : 1.0;
        $efficiency = $efficiency !== null ? (float)$efficiency : 1.0;

        // Interpretación de eficiencia según tipo de potencia declarada
        // Heurística: solo aplicamos división por eficiencia para procesos donde la potencia suele ser "de salida" (p.ej., Magnetrón)
        $process = strtolower((string)($equipment->tipo_de_proceso ?? ''));
        $applyEfficiency = in_array($process, ['magnetrón', 'magnetron']);
        $efficiencyTerm = $applyEfficiency ? max(0.01, $efficiency) : 1.0;

        // Ajustes de factor de carga por procesos típicos (si vienen muy altos/bajos, respetamos valores provistos)
        if ($equipment->factor_carga === null) {
            if (in_array($process, ['resistencia'])) {
                $loadFactor = 1.0; // Resistencia usa 100% cuando está encendido
            } elseif (in_array($process, ['magnetrón', 'magnetron'])) {
                $loadFactor = max($loadFactor, 1.0); // Microondas usa potencia plena en ciclo
            }
        }
        
        // 2. Cálculo de energía ACTIVA (uso)
        // Fórmula Python: energia_consumida_wh = (horas_por_dia * factor_carga * cantidad * potencia_watts) / eficiencia
        $hoursPerPeriod = $hoursPerDay * $days;
        $activeKwhPeriod = ($hoursPerPeriod * $loadFactor * $quantity * $powerWatts) / ($efficiencyTerm * 1000.0);
        
        // 3. Cálculo de energía STANDBY
        $standbyKwhPeriod = 0.0;
        $standbyHoursPeriod = 0.0;
        
        if ($equipment->has_standby_mode ?? false) {
            // Standby watts: 3% de la potencia, entre 0.5W y 8W
            $standbyWatts = max(0.5, min(8.0, $powerWatts * 0.03));
            
            // Horas standby = horas que NO está en uso
            $idleHoursPerDay = max(0, 24.0 - $hoursPerDay);
            $standbyHoursPeriod = $idleHoursPerDay * $days;
            
            // Energía standby sin factores (siempre consume 100%)
            $standbyKwhPeriod = ($standbyHoursPeriod * $standbyWatts * $quantity) / 1000.0;
        }
        
        // 4. Totales
        $totalKwhPeriod = $activeKwhPeriod + $standbyKwhPeriod;
        $totalCost = $totalKwhPeriod * $tariff;
        
        return [
            'kwh_activo' => round($activeKwhPeriod, 2),
            'kwh_standby' => round($standbyKwhPeriod, 2),
            'kwh_total' => round($totalKwhPeriod, 2),
            'costo' => round($totalCost, 2),
            'horas_uso' => round($hoursPerPeriod, 2),
            'horas_standby' => round($standbyHoursPeriod, 2),
            'tarifa' => $tariff,
            'dias' => $days,
        ];
    }
    
    /**
     * Calcula consumo y costo para un equipo basado en una factura.
     *
     * @param EntityEquipment $equipment
     * @param Invoice $invoice
     * @return array Mismo formato que calculateEquipmentConsumption
     */
    public function calculateFromInvoice(EntityEquipment $equipment, Invoice $invoice): array
    {
        // Usar días inclusivos del período (incluye el día final)
        $days = max(1, $invoice->start_date->diffInDays($invoice->end_date) + 1);
        $tariff = $this->calculateAverageTariff($invoice);
        
        return $this->calculateEquipmentConsumption($equipment, $days, $tariff);
    }
    
    /**
     * Calcula la tarifa promedio de una factura ($/kWh).
     *
     * @param Invoice $invoice
     * @return float
     */
    public function calculateAverageTariff(Invoice $invoice): float
    {
        if (($invoice->total_energy_consumed_kwh ?? 0) <= 0) {
            return 0.2; // Fallback genérico
        }
        
        return $invoice->total_amount / $invoice->total_energy_consumed_kwh;
    }
    
    /**
     * Calcula métricas agregadas para un conjunto de equipos.
     *
     * @param \Illuminate\Support\Collection $equipments
     * @param int $days
     * @param float $tariff
     * @return array ['total_kwh', 'total_cost', 'kwh_activo_total', 'kwh_standby_total', 'equipments_detail']
     */
    public function calculateBulkConsumption($equipments, int $days, float $tariff): array
    {
        $totalKwh = 0;
        $totalCost = 0;
        $totalKwhActivo = 0;
        $totalKwhStandby = 0;
        $details = [];
        
        foreach ($equipments as $equipment) {
            $calc = $this->calculateEquipmentConsumption($equipment, $days, $tariff);
            
            $totalKwh += $calc['kwh_total'];
            $totalCost += $calc['costo'];
            $totalKwhActivo += $calc['kwh_activo'];
            $totalKwhStandby += $calc['kwh_standby'];
            
            $details[] = [
                'equipment_id' => $equipment->id,
                'nombre' => $equipment->custom_name ?? $equipment->equipmentType->name ?? 'Sin nombre',
                'tipo_de_proceso' => $equipment->tipo_de_proceso,
                'calculation' => $calc,
            ];
        }
        
        return [
            'total_kwh' => round($totalKwh, 2),
            'total_cost' => round($totalCost, 2),
            'kwh_activo_total' => round($totalKwhActivo, 2),
            'kwh_standby_total' => round($totalKwhStandby, 2),
            'tarifa' => $tariff,
            'dias' => $days,
            'equipments_count' => count($details),
            'equipments_detail' => $details,
        ];
    }
    
    /**
     * Calcula potencial de ahorro eliminando consumo standby.
     *
     * @param \Illuminate\Support\Collection $equipments
     * @param int $days
     * @param float $tariff
     * @return array ['standby_kwh', 'standby_cost', 'savings_percentage', 'equipment_details']
     */
    public function calculateStandbySavingsPotential($equipments, int $days, float $tariff): array
    {
        $totalStandbyKwh = 0;
        $totalKwh = 0;
        $totalCost = 0;
        $equipmentDetails = [];
        
        foreach ($equipments as $equipment) {
            $calc = $this->calculateEquipmentConsumption($equipment, $days, $tariff);
            
            if ($calc['kwh_standby'] > 0) {
                $totalStandbyKwh += $calc['kwh_standby'];
                
                $equipmentDetails[] = [
                    'equipment_id' => $equipment->id,
                    'nombre' => $equipment->custom_name ?? $equipment->equipmentType->name ?? 'Sin nombre',
                    'tipo_de_proceso' => $equipment->tipo_de_proceso,
                    'potencia_watts' => $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0,
                    'cantidad' => $equipment->quantity ?? 1,
                    'standby_watts' => round(max(0.5, min(8.0, ($equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0) * 0.03)), 2),
                    'horas_standby_periodo' => round($calc['horas_standby'], 2),
                    'kwh_standby_periodo' => $calc['kwh_standby'],
                    'costo_standby_periodo' => round($calc['kwh_standby'] * $tariff, 2),
                    'ahorro_anual_estimado' => round(($calc['kwh_standby'] / $days) * 365 * $tariff, 2),
                ];
            }
            
            $totalKwh += $calc['kwh_total'];
            $totalCost += $calc['costo'];
        }
        
        $standbyCost = $totalStandbyKwh * $tariff;
        $standbyPercentage = $totalKwh > 0 ? ($totalStandbyKwh / $totalKwh) * 100 : 0;
        
        return [
            'standby_kwh' => round($totalStandbyKwh, 2),
            'standby_cost' => round($standbyCost, 2),
            'savings_percentage' => round($standbyPercentage, 2),
            'total_kwh' => round($totalKwh, 2),
            'total_cost' => round($totalCost, 2),
            'equipment_count' => count($equipmentDetails),
            'equipment_details' => $equipmentDetails,
            'dias' => $days,
            'tarifa' => $tariff,
        ];
    }
    
    /**
     * Analiza el potencial de ahorro reemplazando equipos antiguos por versiones más eficientes.
     * Compara consumo actual vs consumo con equipos nuevos sugeridos.
     *
     * @param \Illuminate\Support\Collection $currentEquipments Equipos actuales
     * @param array $replacementSuggestions Array de sugerencias [['current_equipment_id' => X, 'new_power_watts' => Y, 'new_tipo_de_proceso' => Z, 'investment_cost' => W]]
     * @param int $days Período de análisis
     * @param float $tariff Tarifa $/kWh
     * @return array Análisis de ROI y ahorro
     */
    public function calculateReplacementAnalysis($currentEquipments, array $replacementSuggestions, int $days, float $tariff): array
    {
        $comparisons = [];
        $totalCurrentCost = 0;
        $totalNewCost = 0;
        $totalSavings = 0;
        $totalInvestment = 0;
        
        foreach ($replacementSuggestions as $suggestion) {
            $equipmentId = $suggestion['current_equipment_id'];
            $currentEquipment = $currentEquipments->firstWhere('id', $equipmentId);
            
            if (!$currentEquipment) {
                continue;
            }
            
            // Calcular consumo y costo ACTUAL
            $currentCalc = $this->calculateEquipmentConsumption($currentEquipment, $days, $tariff);
            
            // Simular equipo NUEVO con mejores especificaciones
            $newEquipmentData = clone $currentEquipment;
            $newEquipmentData->power_watts_override = $suggestion['new_power_watts'] ?? $currentEquipment->power_watts_override;
            $newEquipmentData->tipo_de_proceso = $suggestion['new_tipo_de_proceso'] ?? $currentEquipment->tipo_de_proceso;
            
            // Obtener factores del nuevo tipo de proceso
            if (isset($suggestion['new_tipo_de_proceso'])) {
                $newProcessFactor = \App\Models\ProcessFactor::where('tipo_de_proceso', $suggestion['new_tipo_de_proceso'])->first();
                if ($newProcessFactor) {
                    $newEquipmentData->factor_carga = $newProcessFactor->factor_carga;
                    $newEquipmentData->eficiencia = $newProcessFactor->eficiencia;
                }
            }
            
            // Calcular consumo y costo NUEVO
            $newCalc = $this->calculateEquipmentConsumption($newEquipmentData, $days, $tariff);
            
            // Calcular ahorro
            $periodSavings = $currentCalc['costo'] - $newCalc['costo'];
            $annualSavings = ($periodSavings / $days) * 365;
            $investmentCost = $suggestion['investment_cost'] ?? 0;
            
            // Calcular ROI y payback
            $paybackMonths = $annualSavings > 0 ? ($investmentCost / ($annualSavings / 12)) : null;
            $roi = $investmentCost > 0 ? (($annualSavings * 5) / $investmentCost) * 100 : null; // ROI a 5 años
            
            $comparison = [
                'equipment_id' => $equipmentId,
                'nombre' => $currentEquipment->custom_name ?? $currentEquipment->equipmentType->name ?? 'Sin nombre',
                'categoria' => $currentEquipment->equipmentType->equipmentCategory->name ?? 'Sin categoría',
                'ubicacion' => $currentEquipment->location,
                'cantidad' => $currentEquipment->quantity ?? 1,
                
                // Actual
                'actual_potencia_watts' => $currentEquipment->power_watts_override ?? $currentEquipment->equipmentType->default_power_watts ?? 0,
                'actual_tipo_de_proceso' => $currentEquipment->tipo_de_proceso,
                'actual_kwh_periodo' => $currentCalc['kwh_total'],
                'actual_costo_periodo' => $currentCalc['costo'],
                'actual_costo_anual_estimado' => round(($currentCalc['costo'] / $days) * 365, 2),
                
                // Nuevo
                'nuevo_potencia_watts' => $suggestion['new_power_watts'] ?? ($currentEquipment->power_watts_override ?? $currentEquipment->equipmentType->default_power_watts ?? 0),
                'nuevo_tipo_de_proceso' => $suggestion['new_tipo_de_proceso'] ?? $currentEquipment->tipo_de_proceso,
                'nuevo_kwh_periodo' => $newCalc['kwh_total'],
                'nuevo_costo_periodo' => $newCalc['costo'],
                'nuevo_costo_anual_estimado' => round(($newCalc['costo'] / $days) * 365, 2),
                
                // Ahorro
                'ahorro_kwh_periodo' => round($currentCalc['kwh_total'] - $newCalc['kwh_total'], 2),
                'ahorro_costo_periodo' => round($periodSavings, 2),
                'ahorro_anual_estimado' => round($annualSavings, 2),
                'ahorro_porcentaje' => $currentCalc['costo'] > 0 ? round(($periodSavings / $currentCalc['costo']) * 100, 2) : 0,
                
                // ROI
                'costo_inversion' => $investmentCost,
                'payback_meses' => $paybackMonths !== null ? round($paybackMonths, 1) : null,
                'payback_años' => $paybackMonths !== null ? round($paybackMonths / 12, 1) : null,
                'roi_5_años_porcentaje' => $roi !== null ? round($roi, 2) : null,
                'viable' => $paybackMonths !== null && $paybackMonths <= 36, // Viable si payback <= 3 años
            ];
            
            $comparisons[] = $comparison;
            
            $totalCurrentCost += $currentCalc['costo'];
            $totalNewCost += $newCalc['costo'];
            $totalSavings += $periodSavings;
            $totalInvestment += $investmentCost;
        }
        
        // Ordenar por ahorro (mayor a menor)
        usort($comparisons, function($a, $b) {
            return $b['ahorro_costo_periodo'] <=> $a['ahorro_costo_periodo'];
        });
        
        $totalAnnualSavings = ($totalSavings / $days) * 365;
        $totalPaybackMonths = $totalAnnualSavings > 0 ? ($totalInvestment / ($totalAnnualSavings / 12)) : null;
        
        return [
            'total_actual_costo_periodo' => round($totalCurrentCost, 2),
            'total_nuevo_costo_periodo' => round($totalNewCost, 2),
            'total_ahorro_periodo' => round($totalSavings, 2),
            'total_ahorro_anual_estimado' => round($totalAnnualSavings, 2),
            'total_ahorro_porcentaje' => $totalCurrentCost > 0 ? round(($totalSavings / $totalCurrentCost) * 100, 2) : 0,
            'total_inversion' => round($totalInvestment, 2),
            'total_payback_meses' => $totalPaybackMonths !== null ? round($totalPaybackMonths, 1) : null,
            'total_payback_años' => $totalPaybackMonths !== null ? round($totalPaybackMonths / 12, 1) : null,
            'comparisons' => $comparisons,
            'equipment_count' => count($comparisons),
            'viable_count' => count(array_filter($comparisons, fn($c) => $c['viable'])),
            'dias' => $days,
            'tarifa' => $tariff,
        ];
    }
    
    /**
     * Genera sugerencias automáticas de reemplazo basadas en edad, consumo y eficiencia.
     * Identifica equipos candidatos para reemplazo y sugiere alternativas.
     *
     * @param \Illuminate\Support\Collection $equipments
     * @param int $days
     * @param float $tariff
     * @return array Sugerencias de reemplazo
     */
    public function generateReplacementSuggestions($equipments, int $days, float $tariff): array
    {
        $suggestions = [];
        
        // Mapeo de mejoras típicas por categoría (basado en tecnología moderna)
        $improvementMap = [
            'Climatización' => [
                'new_tipo_de_proceso' => 'Motor',
                'power_reduction_percentage' => 30, // Aires nuevos consumen 30% menos
                'typical_investment' => 150000, // Precio promedio equipo nuevo
            ],
            'Refrigeración' => [
                'new_tipo_de_proceso' => 'Motor',
                'power_reduction_percentage' => 40, // Heladeras A++ consumen 40% menos
                'typical_investment' => 200000,
            ],
            'Iluminación' => [
                'new_tipo_de_proceso' => 'Electroluminiscencia',
                'power_reduction_percentage' => 80, // LED vs halógena/incandescente
                'typical_investment' => 5000, // Precio lámpara LED
            ],
            'Lavado' => [
                'new_tipo_de_proceso' => 'Motor & Resistencia',
                'power_reduction_percentage' => 25,
                'typical_investment' => 250000,
            ],
            'Entretenimiento' => [
                'new_tipo_de_proceso' => 'Electrónico',
                'power_reduction_percentage' => 20,
                'typical_investment' => 100000,
            ],
        ];
        
        foreach ($equipments as $equipment) {
            $type = $equipment->equipmentType;
            $category = $type?->equipmentCategory;
            $categoryName = $category?->name;
            
            if (!isset($improvementMap[$categoryName])) {
                continue; // No hay sugerencia para esta categoría
            }
            
            $powerWatts = $equipment->power_watts_override ?? $type->default_power_watts ?? 0;
            
            // Calcular consumo actual
            $currentCalc = $this->calculateEquipmentConsumption($equipment, $days, $tariff);
            
            // Criterios para sugerir reemplazo:
            // 1. Consumo alto (>100 kWh/período)
            // 2. Equipos de tecnología antigua (halógenas, incandescentes, equipos sin inverter)
            // 3. Equipos con bajo factor de eficiencia (<0.8)
            
            $shouldSuggest = false;
            $reason = [];
            
            if ($currentCalc['kwh_total'] > 100) {
                $shouldSuggest = true;
                $reason[] = 'Alto consumo energético';
            }
            
            if (($equipment->eficiencia ?? 1.0) < 0.8) {
                $shouldSuggest = true;
                $reason[] = 'Baja eficiencia';
            }
            
            // Detectar tecnologías antiguas
            if (in_array($equipment->tipo_de_proceso, ['Resistencia', 'Magnetrón']) && $categoryName === 'Iluminación') {
                $shouldSuggest = true;
                $reason[] = 'Tecnología obsoleta';
            }
            
            if (!$shouldSuggest) {
                continue;
            }
            
            $improvement = $improvementMap[$categoryName];
            $newPowerWatts = $powerWatts * (1 - ($improvement['power_reduction_percentage'] / 100));
            
            $suggestions[] = [
                'current_equipment_id' => $equipment->id,
                'new_power_watts' => (int)round($newPowerWatts),
                'new_tipo_de_proceso' => $improvement['new_tipo_de_proceso'],
                'investment_cost' => $improvement['typical_investment'] * ($equipment->quantity ?? 1),
                'reason' => implode(', ', $reason),
            ];
        }
        
        return $suggestions;
    }
}
