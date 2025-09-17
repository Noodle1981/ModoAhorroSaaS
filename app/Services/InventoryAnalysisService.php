<?php

// app/Services/InventoryAnalysisService.php
namespace App\Services;

use App\Models\Entity;
use App\Models\RatePrice; // Para obtener los precios de la tarifa
use App\Models\EquipmentUsagePattern; // Para los patrones de uso de equipos
use App\Models\MaintenanceTask; // Para las tareas de mantenimiento
use App\Models\MaintenanceLog; // Para los registros de mantenimiento
use Illuminate\Support\Collection; // Para tipar colecciones




class InventoryAnalysisService
{
    public function calculateForEntity(Entity $entity)
    {
        // 1. Obtenemos el inventario completo de la entidad con TODA la info que necesitamos
        $inventory = $entity->entityEquipment()
            ->with(['equipmentType.equipmentCategory.calculationFactor']) // ¡La magia de las relaciones!
            ->get();

        // 2. Iteramos sobre cada equipo del inventario y calculamos los nuevos valores
        $inventoryWithCalculations = $inventory->map(function ($equipment) {
            
            // Lógica para obtener los valores correctos (override o default)
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
            $avgDailyUseHours = $equipment->avg_daily_use_hours_override ?? $equipment->equipmentType->default_avg_daily_use_hours;
            
            // Convertir a las unidades de tu cálculo (kW y horas/año)
            $consumoNominalKW = $powerWatts / 1000;
            $horasDeUsoAnual = $avgDailyUseHours * 365;

            // Obtener los factores de la tabla que trajimos con la consulta
            $factorCarga = $equipment->equipmentType->equipmentCategory->calculationFactor->load_factor ?? 1;
            $eficiencia = $equipment->equipmentType->equipmentCategory->calculationFactor->efficiency_factor ?? 1;

            // --- ¡AQUÍ ESTÁN TUS CÁLCULOS DE PYTHON! ---
            $energiaUtilConsumida = $horasDeUsoAnual * $factorCarga * $equipment->quantity * $consumoNominalKW;
            $energiaSecundariaConsumida = $energiaUtilConsumida / $eficiencia;

            // Añadimos los resultados al objeto del equipo
            $equipment->consumo_nominal_kw = round($consumoNominalKW, 4);
            $equipment->horas_uso_anual = round($horasDeUsoAnual, 2);
            $equipment->energia_util_kwh = round($energiaUtilConsumida, 2);
            $equipment->energia_secundaria_kwh = round($energiaSecundariaConsumida, 2);

            return $equipment;
        });

        return $inventoryWithCalculations;
    }

    public function findTimeShiftOpportunities(Entity $entity, Collection $inventoryWithCalculations)
{
    $opportunities = [];
    $contract = $entity->supplies()->first()->contracts()->where('is_active', true)->first();
    
    // Suponemos que tenemos los precios de la tarifa (P1=Punta, P2=Llano, P3=Valle)
    $precios = RatePrice::where('rate_id', $contract->rate->id)->first(); // Simplificado
    $precioPunta = $precios->price_energy_p1;
    $precioValle = $precios->price_energy_p3;

    // Buscamos los hábitos de uso registrados
    $usagePatterns = EquipmentUsagePattern::whereIn('entity_equipment_id', $inventoryWithCalculations->pluck('id'))->get();

    foreach ($usagePatterns as $pattern) {
        $equipment = $inventoryWithCalculations->find($pattern->entity_equipment_id);
        
        // Lógica simplificada para determinar si el uso es en horario caro
        // En la vida real, necesitarías una función que mapee hora y día a P1, P2, P3.
        $esHorarioCaro = ($pattern->start_time > '08:00:00' && $pattern->start_time < '22:00:00');

        if ($esHorarioCaro) {
            // Calculamos el consumo de UNA SOLA VEZ que se usa el equipo
            $consumoPorUsoKwh = ($equipment->consumo_nominal_kw * $equipment->equipmentType->equipmentCategory->calculationFactor->load_factor) * ($pattern->duration_minutes / 60);

            // Calculamos el costo actual vs el costo en horario valle
            $costoActual = $consumoPorUsoKwh * $precioPunta;
            $costoMejorado = $consumoPorUsoKwh * $precioValle;
            $ahorroPorVez = $costoActual - $costoMejorado;
            
            // Estimamos el ahorro anual
            $vecesPorAnio = 52; // Suponemos que se usa una vez por semana
            $ahorroAnualPesos = $ahorroPorVez * $vecesPorAnio;

            if ($ahorroAnualPesos > 0) {
                 $opportunities[] = [
                    'type' => 'cambio_horario',
                    'user_equipment' => $equipment->custom_name,
                    'suggestion' => "Mover el uso de las {$pattern->start_time} al horario valle (después de las 22:00hs o fines de semana)",
                    'ahorro_anual_pesos' => round($ahorroAnualPesos, 2),
                ];
            }
        }
    }

    return $opportunities;
}
    public function findMaintenanceOpportunities(Collection $inventoryWithCalculations, $costoUnitarioKwh)
{
    $opportunities = [];
    $PENALTY_FACTOR = 0.15; // ¡Factor clave! Significa que un equipo sin mantenimiento consume un 15% más.

    // Buscamos tareas de mantenimiento recomendadas para los equipos del inventario
    $tasks = MaintenanceTask::whereIn('equipment_type_id', $inventoryWithCalculations->pluck('equipment_type_id'))->get();

    foreach ($tasks as $task) {
        $equipment = $inventoryWithCalculations->where('equipment_type_id', $task->equipment_type_id)->first();
        
        // Vemos cuándo fue el último mantenimiento de ESTA tarea para ESTE equipo
        $lastLog = MaintenanceLog::where('entity_equipment_id', $equipment->id)
                                  ->where('maintenance_task_id', $task->id)
                                  ->latest('performed_on_date')
                                  ->first();

        $diasDesdeUltimoMantenimiento = $lastLog ? $lastLog->performed_on_date->diffInDays(now()) : 9999;

        // Si pasó más tiempo del recomendado, es una oportunidad de mejora
        if ($diasDesdeUltimoMantenimiento > $task->recommended_frequency_days) {
            
            $energiaActualKwh = $equipment->energia_secundaria_kwh;
            
            // Estimamos cuánta energía se está "desperdiciando"
            $energiaDesperdiciadaKwh = $energiaActualKwh * $PENALTY_FACTOR;
            $ahorroAnualPesos = $energiaDesperdiciadaKwh * $costoUnitarioKwh;

            $opportunities[] = [
                'type' => 'mantenimiento',
                'user_equipment' => $equipment->custom_name,
                'suggestion' => "Realizar la tarea: '{$task->name}'. Se recomienda cada {$task->recommended_frequency_days} días y la última fue hace {$diasDesdeUltimoMantenimiento}.",
                'ahorro_anual_pesos' => round($ahorroAnualPesos, 2),
            ];
        }
    }

    return $opportunities;
}
   public function findReplacementOpportunities(Collection $inventoryWithCalculations, $costoUnitarioKwh){
    $opportunities = [];
    $PENALTY_FACTOR = 0.15; // Factor clave! Significa que un equipo sin mantenimiento consume un 15% más.
   }
 
}