<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EquipmentUsagePattern;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceTask;
use App\Models\MarketEquipmentCatalog;
use App\Models\RatePrice;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class InventoryAnalysisService
{
    /**
     * =====================================================================
     * MÉTODO ORQUESTADOR PRINCIPAL (El que faltaba)
     * =====================================================================
     * Llama a todos los análisis y devuelve un array unificado de oportunidades.
     *
     * @param Entity $entity
     * @return array
     */
    public function findAllOpportunities(Entity $entity): array
    {
        // 1. Obtenemos un costo de referencia del kWh de forma segura.
        $costoUnitarioKwh = $this->getAverageKwhCost($entity);

        // Si el costo es 0 (porque no hay facturas), no podemos calcular ahorros en pesos.
        // Por simplicidad, si no hay costo, no hay recomendaciones de ahorro.
        if ($costoUnitarioKwh === 0.0) {
            return [];
        }

        // 2. Calculamos el perfil de inventario actual
        $inventory = $this->calculateEnergyProfile($entity);

        if ($inventory->isEmpty()) {
            return [];
        }

        // 3. Llamamos a cada especialista (estos métodos ahora recibirán un costo válido)
        $replacementOps = $this->findReplacementOpportunities($inventory, $costoUnitarioKwh);
        $timeShiftOps = $this->findTimeShiftOpportunities($entity, $inventory);
        $maintenanceOps = $this->findMaintenanceOpportunities($inventory, $costoUnitarioKwh);
        
        // 4. Unimos y ordenamos los resultados
        $allOpportunities = array_merge($replacementOps, $timeShiftOps, $maintenanceOps);
        
        usort($allOpportunities, fn($a, $b) => ($b['ahorro_anual_pesos'] ?? 0) <=> ($a['ahorro_anual_pesos'] ?? 0));
        
        return $allOpportunities;
    }

    /**
     * =====================================================================
     * CÁLCULO DE INVENTARIO (El motor base)
     * =====================================================================
     */
 // ...
public function calculateEnergyProfile(Entity $entity): Collection
{
        // 1. Obtenemos el inventario real de la base de datos
        $inventory = $entity->entityEquipment()
            ->with(['equipmentType.equipmentCategory.calculationFactor'])
            ->get();
        
        // 2. Mapeamos el inventario real para calcular sus consumos
        $calculatedInventory = $inventory->map(function ($equipment) {
            
            // --- CÁLCULO DE CONSUMO ACTIVO ---
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
            $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
            
            $consumoNominalKW = $powerWatts / 1000;
            $horasDeUsoAnual = ($avgDailyUseMinutes / 60) * 365;

            $calculationFactor = $equipment->equipmentType->equipmentCategory->calculationFactor;
            $factorCarga = $calculationFactor->load_factor ?? 1;
            $eficiencia = $calculationFactor->efficiency_factor ?? 1;

            if ($eficiencia == 0) $eficiencia = 1;

            $energiaUtilConsumida = $horasDeUsoAnual * $factorCarga * $equipment->quantity * $consumoNominalKW;
            $energiaSecundariaConsumida = $energiaUtilConsumida / $eficiencia;

            $equipment->consumo_nominal_kw = round($consumoNominalKW, 4);
            $equipment->horas_uso_anual = round($horasDeUsoAnual, 2);
            $equipment->energia_util_kwh = round($energiaUtilConsumida, 2);
            $equipment->energia_secundaria_kwh = round($energiaSecundariaConsumida, 2);
            
            // --- CÁLCULO DE CONSUMO EN STAND BY ---
            $consumoStandByAnualKwh = 0; // Por defecto es 0
            // Solo calculamos si el usuario lo marcó explícitamente
            if ($equipment->has_standby_mode) {
                $standbyPowerWatts = $equipment->equipmentType->standby_power_watts ?? 0;
                if ($standbyPowerWatts > 0) {
                    $horasStandByAnual = (24 * 365) - $horasDeUsoAnual;
                    if ($horasStandByAnual < 0) $horasStandByAnual = 0;

                    $consumoStandByAnualKwh = ($standbyPowerWatts / 1000) * $horasStandByAnual * $equipment->quantity;
                }
            }
            
            // Añadimos el nuevo resultado al objeto
            $equipment->standby_kwh = round($consumoStandByAnualKwh, 2);
            
            // Creamos un total para este equipo
            $equipment->energia_total_anual_kwh = $equipment->energia_secundaria_kwh + $equipment->standby_kwh;

            return $equipment;
        });

        // --- 3. INYECCIÓN VIRTUAL DE CARGA ELECTRÓNICA ---
        $electronicDevicesCount = $entity->details['electronic_devices_count'] ?? 0;
        
        if ($electronicDevicesCount > 0) {
            // Buscamos los datos de catálogo para esta categoría especial
            $electronicDeviceType = EquipmentType::where('name', 'Dispositivos Electrónicos (celulares, tablets, notebooks)')->first();
            $calculationFactor = CalculationFactor::where('method_name', 'consumo_agregado')->first();
            
            if ($electronicDeviceType && $calculationFactor) {
                $quantity = $electronicDevicesCount;
                $powerWatts = $electronicDeviceType->default_power_watts;
                
                $horasDeUsoAnual = 24 * 365;
                
                $consumoNominalKW = $powerWatts / 1000;
                $factorCarga = $calculationFactor->load_factor;
                $eficiencia = $calculationFactor->efficiency_factor;

                $energiaUtilConsumida = $horasDeUsoAnual * $factorCarga * $quantity * $consumoNominalKW;
                $energiaSecundariaConsumida = $energiaUtilConsumida / ($eficiencia ?: 1);

                // Creamos un "falso" objeto de equipo para añadir al informe
                $virtualEquipment = new EntityEquipment([
                    'custom_name' => 'Carga Electrónica Agregada',
                    'quantity' => $quantity,
                ]);
                $virtualEquipment->consumo_nominal_kw = round($consumoNominalKW, 4);
                $virtualEquipment->horas_uso_anual = round($horasDeUsoAnual, 2);
                $virtualEquipment->energia_util_kwh = round($energiaUtilConsumida, 2);
                $virtualEquipment->energia_secundaria_kwh = round($energiaSecundariaConsumida, 2);
                $virtualEquipment->standby_kwh = 0; // No aplica stand by a esta categoría agregada
                $virtualEquipment->energia_total_anual_kwh = round($energiaSecundariaConsumida, 2);
                $virtualEquipment->equipmentType = $electronicDeviceType;

                // Lo añadimos a la colección final
                $calculatedInventory->push($virtualEquipment);
            }
        }

        // 4. Devolvemos la colección completa
        return $calculatedInventory;
    }

    /**
     * =====================================================================
     * ESPECIALISTA 1: MEJORA POR REEMPLAZO DE EQUIPO (ROI)
     * =====================================================================
     */
    public function findReplacementOpportunities(Collection $inventory, float $costoKwh): array
    {
        $opportunities = [];
        foreach ($inventory as $userEquipment) {
            $bestReplacement = MarketEquipmentCatalog::where('equipment_type_id', $userEquipment->equipment_type_id)
                ->where('power_watts', '<', $userEquipment->consumo_nominal_kw * 1000)
                ->orderBy('average_price', 'asc')
                ->first();

            if (!$bestReplacement) continue;

            $calculationFactor = $userEquipment->equipmentType->equipmentCategory->calculationFactor;
            if (!$calculationFactor) continue;

            $energiaActualKwh = $userEquipment->energia_secundaria_kwh;
            $consumoNominalNuevoKW = $bestReplacement->power_watts / 1000;
            $energiaUtilNuevaKwh = $userEquipment->horas_uso_anual * $calculationFactor->load_factor * $userEquipment->quantity * $consumoNominalNuevoKW;
            $energiaNuevaKwh = $energiaUtilNuevaKwh / ($calculationFactor->efficiency_factor ?: 1);
            
            $ahorroAnualKwh = $energiaActualKwh - $energiaNuevaKwh;
            $ahorroAnualPesos = $ahorroAnualKwh * $costoKwh;

            if ($ahorroAnualPesos > 0) {
                $costoInversion = $bestReplacement->average_price;
                $retornoInversionAnios = $costoInversion / $ahorroAnualPesos;

                $opportunities[] = [
                    'type' => 'Reemplazo de Equipo',
                    'user_equipment' => $userEquipment->custom_name ?? $userEquipment->equipmentType->name,
                    'suggestion' => "Reemplazar por {$bestReplacement->brand} {$bestReplacement->model_name}",
                    'ahorro_anual_pesos' => round($ahorroAnualPesos, 2),
                    'retorno_inversion_anios' => round($retornoInversionAnios, 2),
                ];
            }
        }
        return $opportunities;
    }

    /**
     * =====================================================================
     * ESPECIALISTA 2: MEJORA POR CAMBIO DE HORARIO
     * =====================================================================
     */
    public function findTimeShiftOpportunities(Entity $entity, Collection $inventory): array
    {
        // Esta lógica es un placeholder y necesita refinamiento con las tarifas reales
        return []; 
    }

    /**
     * =====================================================================
     * ESPECIALISTA 3: MEJORA POR MANTENIMIENTO
     * =====================================================================
     */
    public function findMaintenanceOpportunities(Collection $inventory, float $costoKwh): array
    {
        $opportunities = [];
        $PENALTY_FACTOR = 0.15; // 15% de consumo extra por falta de mantenimiento

        $tasks = MaintenanceTask::whereIn('equipment_type_id', $inventory->pluck('equipment_type_id'))->get();
        
        foreach ($inventory as $equipment) {
            $applicableTasks = $tasks->where('equipment_type_id', $equipment->equipment_type_id);
            foreach ($applicableTasks as $task) {
                $lastLog = MaintenanceLog::where('entity_equipment_id', $equipment->id)
                                          ->where('maintenance_task_id', $task->id)
                                          ->latest('performed_on_date')
                                          ->first();
                
                $daysSinceLast = $lastLog ? $lastLog->performed_on_date->diffInDays(now()) : 9999;

                if ($daysSinceLast > $task->recommended_frequency_days) {
                    $energiaDesperdiciadaKwh = $equipment->energia_secundaria_kwh * $PENALTY_FACTOR;
                    $ahorroAnualPesos = $energiaDesperdiciadaKwh * $costoKwh;

                    $opportunities[] = [
                        'type' => 'Mantenimiento',
                        'user_equipment' => $equipment->custom_name ?? $equipment->equipmentType->name,
                        'suggestion' => "Realizar tarea: '{$task->name}'. Última vez: ".($lastLog ? $lastLog->performed_on_date->format('d/m/Y') : 'Nunca'),
                        'ahorro_anual_pesos' => round($ahorroAnualPesos, 2),
                    ];
                }
            }
        }
        return $opportunities;
    }


    /**
     * =====================================================================
     * MÉTODO DE AYUDA (HELPER)
     * =====================================================================
     */
    private function getAverageKwhCost(Entity $entity): float
    {
        $lastInvoice = $entity->supplies->flatMap(function ($supply) {
            return $supply->contracts;
        })->flatMap(function ($contract) {
            return $contract->invoices;
        })->sortByDesc('end_date')->first();

        // Si no hay factura O el consumo es cero, devolvemos 0.0 para indicar que no hay costo.
        if (!$lastInvoice || $lastInvoice->total_energy_consumed_kwh == 0) {
            // En lugar de un valor por defecto, devolvemos 0 para que el método que llama sepa que no hay datos.
            return 0.0; 
        }

        return $lastInvoice->total_amount / $lastInvoice->total_energy_consumed_kwh;
    }

    /**
     * =====================================================================
     * CÁLCULO DE STANDBY 
     * =====================================================================
     */



}