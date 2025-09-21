<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;

class InventoryAnalysisService
{
    /**
     * Calcula el perfil energético ANUALIZADO del inventario.
     * Útil para comparaciones a largo plazo y ROI.
     *
     * @param Entity $entity
     * @return Collection
     */
    public function getAnnualEnergyProfile(Entity $entity): Collection
    {
        // El número de días para el cálculo es 365
        return $this->calculateEnergyProfileForPeriod($entity, 365);
    }

    /**
     * Calcula el perfil energético del inventario para un período específico (ej: un mes).
     *
     * @param Entity $entity
     * @param int $numberOfDays
     * @return Collection
     */
    public function calculateEnergyProfileForPeriod(Entity $entity, int $numberOfDays): Collection
    {
        $inventory = $entity->entityEquipment()
            ->with(['equipmentType.equipmentCategory.calculationFactor'])
            ->get();

        return $inventory->map(function ($equipment) use ($numberOfDays) {
            
            // --- CÁLCULO ACTIVO ---
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
            $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
            
            $consumoNominalKW = $powerWatts / 1000;
            $horasDeUsoDiario = $avgDailyUseMinutes / 60;
            $horasDeUsoPeriodo = $horasDeUsoDiario * $numberOfDays;

            $calculationFactor = $equipment->equipmentType->equipmentCategory->calculationFactor;
            $factorCarga = $calculationFactor->load_factor ?? 1;
            $eficiencia = $calculationFactor->efficiency_factor ?? 1;

            if ($eficiencia == 0) $eficiencia = 1;

            $energiaSecundariaConsumida = $horasDeUsoPeriodo * $factorCarga * $equipment->quantity * $consumoNominalKW / $eficiencia;
            
            // --- CÁLCULO STAND BY ---
            $standbyPowerWatts = $equipment->equipmentType->standby_power_watts ?? 0;
            $horasStandByPeriodo = (24 * $numberOfDays) - $horasDeUsoPeriodo;
            if ($horasStandByPeriodo < 0) $horasStandByPeriodo = 0;

            $consumoStandByKwh = ($standbyPowerWatts / 1000) * $horasStandByPeriodo * $equipment->quantity;
            
            // --- RESULTADOS ---
            $equipment->consumo_kwh_activo_periodo = round($energiaSecundariaConsumida, 2);
            $equipment->consumo_kwh_standby_periodo = round($consumoStandByKwh, 2);
            $equipment->consumo_kwh_total_periodo = $equipment->consumo_kwh_activo_periodo + $equipment->consumo_kwh_standby_periodo;

            return $equipment;
        });
    }

    // Nota: Los métodos para buscar oportunidades (findReplacementOpportunities, etc.)
    // seguirán funcionando bien porque se basan en el perfil ANUAL.
    // Podríamos crear un nuevo método orquestador para ellos.
}