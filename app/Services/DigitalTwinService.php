<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Servicio de Gemelo Digital (Digital Twin)
 * 
 * Objetivos:
 * 1. Simular cambios ANTES de implementarlos (ROI antes de comprar)
 * 2. Ajustar modelo para que refleje la realidad exacta
 * 3. Predecir impacto de decisiones energéticas
 */
class DigitalTwinService
{
    /**
     * Crea el "gemelo digital" = consolidación de todos los datos de la entidad
     */
    public function createDigitalTwin(Entity $entity): array
    {
        $inventoryService = app(InventoryAnalysisService::class);
        
        return [
            'entity_id' => $entity->id,
            'name' => $entity->name,
            'type' => $entity->type,
            
            // Estado actual de equipos
            'equipments' => $this->getEquipmentsSummary($entity),
            
            // Consumo real vs estimado
            'consumption' => [
                'real_last_invoice_kwh' => $this->getLastInvoiceConsumption($entity),
                'estimated_monthly_kwh' => $this->getEstimatedMonthlyConsumption($entity),
                'difference_kwh' => $this->getConsumptionDifference($entity),
                'match_percentage' => $this->getMatchPercentage($entity),
            ],
            
            // Oportunidades de mejora
            'opportunities' => app(ReplacementAnalysisService::class)->findAllOpportunities($entity),
            
            // Metadata
            'last_updated' => now(),
        ];
    }
    
    /**
     * SIMULADOR: ¿Qué pasaría si cambio algo?
     * 
     * Ejemplo:
     * $twin->simulate($entity, [
     *     'replace_equipment' => [5 => ['new_power_watts' => 150]],
     *     'adjust_usage' => [8 => ['new_avg_minutes' => 180]],
     * ]);
     */
    public function simulate(Entity $entity, array $changes): array
    {
        $original = $this->createDigitalTwin($entity);
        
        // Clonar equipos para simular
        $simulatedEquipments = $entity->equipments()->with('equipmentType')->get();
        
        // Aplicar cambios de reemplazo
        if (isset($changes['replace_equipment'])) {
            foreach ($changes['replace_equipment'] as $equipmentId => $newValues) {
                $equipment = $simulatedEquipments->firstWhere('id', $equipmentId);
                if ($equipment) {
                    if (isset($newValues['new_power_watts'])) {
                        $equipment->power_watts_override = $newValues['new_power_watts'];
                    }
                    if (isset($newValues['new_standby_watts'])) {
                        $equipment->standby_watts_override = $newValues['new_standby_watts'];
                    }
                }
            }
        }
        
        // Aplicar cambios de uso
        if (isset($changes['adjust_usage'])) {
            foreach ($changes['adjust_usage'] as $equipmentId => $newValues) {
                $equipment = $simulatedEquipments->firstWhere('id', $equipmentId);
                if ($equipment && isset($newValues['new_avg_minutes'])) {
                    $equipment->avg_daily_use_minutes_override = $newValues['new_avg_minutes'];
                }
            }
        }
        
        // Recalcular consumo con nuevos valores
        $newMonthlyConsumption = $simulatedEquipments->sum(function($equipment) {
            $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
            $minutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;
            $quantity = $equipment->quantity;
            
            // kWh mensual = (watts / 1000) * (minutos / 60) * cantidad * 30 días
            return ($power / 1000) * ($minutes / 60) * $quantity * 30;
        });
        
        $originalConsumption = $original['consumption']['estimated_monthly_kwh'];
        $savingsKwh = $originalConsumption - $newMonthlyConsumption;
        $savingsEuros = $savingsKwh * 0.15; // Asumiendo €0.15/kWh
        
        // Calcular inversión si hay reemplazos
        $totalInvestment = 0;
        if (isset($changes['replace_equipment']) && isset($changes['investment'])) {
            $totalInvestment = array_sum(array_column($changes['investment'], 'price'));
        }
        
        $paybackMonths = $savingsEuros > 0 ? $totalInvestment / $savingsEuros : 0;
        
        return [
            'scenario_name' => $changes['name'] ?? 'Simulación sin nombre',
            'original' => [
                'monthly_kwh' => round($originalConsumption, 2),
                'monthly_cost_eur' => round($originalConsumption * 0.15, 2),
                'annual_cost_eur' => round($originalConsumption * 0.15 * 12, 2),
            ],
            'simulated' => [
                'monthly_kwh' => round($newMonthlyConsumption, 2),
                'monthly_cost_eur' => round($newMonthlyConsumption * 0.15, 2),
                'annual_cost_eur' => round($newMonthlyConsumption * 0.15 * 12, 2),
            ],
            'savings' => [
                'kwh_per_month' => round($savingsKwh, 2),
                'euros_per_month' => round($savingsEuros, 2),
                'euros_per_year' => round($savingsEuros * 12, 2),
                'percentage' => $originalConsumption > 0 ? round(($savingsKwh / $originalConsumption) * 100, 1) : 0,
            ],
            'investment' => [
                'total_eur' => $totalInvestment,
                'payback_months' => round($paybackMonths, 1),
                'payback_years' => round($paybackMonths / 12, 1),
                'roi_5years' => $totalInvestment > 0 ? round((($savingsEuros * 12 * 5) / $totalInvestment - 1) * 100, 1) : 0,
            ],
            'recommendation' => $this->generateRecommendation($savingsEuros, $paybackMonths),
        ];
    }
    
    /**
     * Comparar múltiples escenarios
     */
    public function compareScenarios(Entity $entity, array $scenarios): array
    {
        $results = [];
        
        foreach ($scenarios as $scenario) {
            $results[] = $this->simulate($entity, $scenario);
        }
        
        // Ordenar por mejor ahorro
        usort($results, function($a, $b) {
            return $b['savings']['euros_per_year'] <=> $a['savings']['euros_per_year'];
        });
        
        return [
            'scenarios' => $results,
            'best_scenario' => $results[0] ?? null,
            'best_free_scenario' => collect($results)->firstWhere('investment.total_eur', 0),
        ];
    }
    
    // ========== MÉTODOS AUXILIARES ==========
    
    private function getEquipmentsSummary(Entity $entity): array
    {
        return $entity->equipments()->with('equipmentType.equipmentCategory')->get()->map(function($eq) {
            $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
            $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
            
            return [
                'id' => $eq->id,
                'name' => $eq->custom_name ?? $eq->equipmentType->name,
                'category' => $eq->equipmentType->equipmentCategory->name,
                'power_watts' => $power,
                'daily_minutes' => $minutes,
                'quantity' => $eq->quantity,
                'monthly_kwh' => ($power / 1000) * ($minutes / 60) * $eq->quantity * 30,
            ];
        })->toArray();
    }
    
    private function getLastInvoiceConsumption(Entity $entity): float
    {
        $supplyIds = $entity->supplies->pluck('id');
        
        $lastInvoice = Invoice::whereHas('contract', function($q) use ($supplyIds) {
            $q->whereIn('supply_id', $supplyIds);
        })->orderBy('end_date', 'desc')->first();
        
        if (!$lastInvoice) {
            return 0;
        }
        
        // Convertir a mensual si el periodo es diferente
        $days = $lastInvoice->start_date->diffInDays($lastInvoice->end_date);
        $monthlyEquivalent = ($lastInvoice->total_energy_consumed_kwh / $days) * 30;
        
        return $monthlyEquivalent;
    }
    
    private function getEstimatedMonthlyConsumption(Entity $entity): float
    {
        return $entity->equipments()->with('equipmentType')->get()->sum(function($eq) {
            $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
            $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
            return ($power / 1000) * ($minutes / 60) * $eq->quantity * 30;
        });
    }
    
    private function getConsumptionDifference(Entity $entity): float
    {
        return $this->getLastInvoiceConsumption($entity) - $this->getEstimatedMonthlyConsumption($entity);
    }
    
    private function getMatchPercentage(Entity $entity): float
    {
        $real = $this->getLastInvoiceConsumption($entity);
        $estimated = $this->getEstimatedMonthlyConsumption($entity);
        
        if ($real == 0) {
            return 0;
        }
        
        return round(($estimated / $real) * 100, 1);
    }
    
    private function generateRecommendation(float $savingsEuros, float $paybackMonths): string
    {
        if ($paybackMonths == 0) {
            if ($savingsEuros > 0) {
                return '🟢 ¡EXCELENTE! Ahorro sin inversión. Implementar inmediatamente.';
            }
            return '🔴 No hay ahorro con estos cambios.';
        }
        
        if ($paybackMonths < 12) {
            return '🟢 ROI EXCELENTE. Recuperas inversión en menos de 1 año.';
        }
        
        if ($paybackMonths < 24) {
            return '🟡 ROI BUENO. Recuperas inversión en ' . round($paybackMonths) . ' meses.';
        }
        
        if ($paybackMonths < 36) {
            return '🟡 ROI ACEPTABLE. Considera si puedes esperar ' . round($paybackMonths / 12, 1) . ' años.';
        }
        
        return '🔴 ROI BAJO. Payback muy largo (' . round($paybackMonths / 12, 1) . ' años). Busca alternativas.';
    }
}
