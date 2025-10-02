<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice; // Importar Invoice
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;

class InventoryAnalysisService
{
    protected $replacementService;

    public function __construct(ReplacementAnalysisService $replacementService)
    {
        $this->replacementService = $replacementService;
    }

    /**
     * Orquestador principal para encontrar todo tipo de oportunidades de ahorro.
     *
     * @param Entity $entity
     * @return array
     */
    public function findAllOpportunities(Entity $entity): array
    {
        $opportunities = [
            'equipment' => [],
            'behavior' => [],
            'maintenance' => [],
        ];

        // --- 1. Oportunidades de REEMPLAZO DE EQUIPOS ---
        // TODO: Obtener costo de kWh del contrato activo.
        $costoUnitarioKwh = 0.18; // Usamos un valor hardcodeado por ahora.
        $annualProfile = $this->getAnnualEnergyProfile($entity);
        $replacementOpportunities = $this->replacementService->findReplacementOpportunities($annualProfile, $costoUnitarioKwh);
        
        // Formateamos para que coincida con la estructura de salida
        foreach ($replacementOpportunities as $opp) {
            $opportunities['equipment'][] = $opp;
        }

        // --- 2. Oportunidades de CAMBIO DE HÁBITOS (Optimización Tarifaria) ---
        // TODO: Implementar lógica para sugerir mover consumos a horarios más baratos.

        // --- 3. Oportunidades de MANTENIMIENTO PREVENTIVO ---
        // TODO: Implementar lógica para sugerir mantenimientos basados en historial.


        return $opportunities;
    }

    /**
     * Calcula el perfil energético ANUALIZADO del inventario.
     * Útil para comparaciones a largo plazo y ROI.
     *
     * @param Entity $entity
     * @return Collection
     */
    public function getAnnualEnergyProfile(Entity $entity): Collection
    {
        // Para el anual, no tenemos una factura, así que usamos los valores por defecto.
        $inventory = $entity->entityEquipments()->withTrashed()
            ->with(['equipmentType.equipmentCategory.calculationFactor'])
            ->get();

        return $this->calculateProfileFromInventory($inventory, 365);
    }

    /**
     * Calcula el perfil energético del inventario para un período de factura específico,
     * utilizando los snapshots de uso de esa factura.
     *
     * @param Entity $entity
     * @param Invoice $invoice
     * @return Collection
     */
    public function calculateEnergyProfileForPeriod(Entity $entity, Invoice $invoice): Collection
    {
        $numberOfDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

        $inventory = $entity->entityEquipments()->withTrashed()
            ->with(['equipmentType.equipmentCategory.calculationFactor'])
            ->get();

        // Obtenemos todos los snapshots de esa factura y los mapeamos por ID de equipo para un acceso rápido.
        $snapshots = $invoice->snapshots->keyBy('entity_equipment_id');

        return $this->calculateProfileFromInventory($inventory, $numberOfDays, $snapshots);
    }

    /**
     * Calcula el consumo anual de un único equipo basado en su uso por defecto.
     *
     * @param \App\Models\EntityEquipment $equipment
     * @return array
     */
    public function getAnnualConsumptionForEquipment(\App\Models\EntityEquipment $equipment): array
    {
        // Creamos una colección que solo contenga este equipo para pasarlo al método de cálculo.
        $inventory = new Collection([$equipment]);
        $annualProfile = $this->calculateProfileFromInventory($inventory, 365, null);
        
        // El método devuelve una colección, así que tomamos el primer (y único) resultado.
        $result = $annualProfile->first();

        return [
            'consumo_kwh_activo_periodo' => $result->consumo_kwh_activo_periodo,
            'consumo_kwh_standby_periodo' => $result->consumo_kwh_standby_periodo,
            'consumo_kwh_total_periodo' => $result->consumo_kwh_total_periodo,
        ];
    }

    /**
     * Lógica de cálculo principal, ahora separada para ser reutilizable.
     *
     * @param Collection $inventory
     * @param int $numberOfDays
     * @param Collection|null $snapshots
     * @return Collection
     */
    private function calculateProfileFromInventory(Collection $inventory, int $numberOfDays, ?Collection $snapshots = null): Collection
    {
        return $inventory->map(function ($equipment) use ($numberOfDays, $snapshots) {
            
            // --- LÓGICA MEJORADA PARA OBTENER EL USO CORRECTO ---
            $avgDailyUseMinutes = 0;
            if ($snapshots && $snapshotForEquipment = $snapshots->get($equipment->id)) {
                // 1. Prioridad: Usar el valor del snapshot de la factura.
                $avgDailyUseMinutes = $snapshotForEquipment->avg_daily_use_minutes;
            } else {
                // 2. Fallback: Usar el override del equipo o el default del tipo.
                $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
            }

            // --- CÁLCULO ACTIVO ---
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
            $consumoNominalKW = $powerWatts / 1000;
            $horasDeUsoDiario = $avgDailyUseMinutes / 60;
            $horasDeUsoPeriodo = $horasDeUsoDiario * $numberOfDays;

            $calculationFactor = $equipment->equipmentType->equipmentCategory->calculationFactor;
            $factorCarga = $calculationFactor->load_factor ?? 1;
            $eficiencia = $calculationFactor->efficiency_factor ?? 1;

            if ($eficiencia == 0) $eficiencia = 1;

            $energiaSecundariaConsumida = $horasDeUsoPeriodo * $factorCarga * $equipment->quantity * $consumoNominalKW / $eficiencia;
            
            // --- CÁLCULO STAND BY ---
            $consumoStandByKwh = 0; // Default to 0
            if ($equipment->has_standby_mode) {
                $standbyPowerWatts = $equipment->equipmentType->standby_power_watts ?? 0;
                $horasStandByPeriodo = (24 * $numberOfDays) - $horasDeUsoPeriodo;
                if ($horasStandByPeriodo < 0) $horasStandByPeriodo = 0;

                $consumoStandByKwh = ($standbyPowerWatts / 1000) * $horasStandByPeriodo * $equipment->quantity;
            }
            
            // --- RESULTADOS ---
            $equipment->consumo_kwh_activo_periodo = round($energiaSecundariaConsumida, 2);
            $equipment->consumo_kwh_standby_periodo = round($consumoStandByKwh, 2);
            $equipment->consumo_kwh_total_periodo = $equipment->consumo_kwh_activo_periodo + $equipment->consumo_kwh_standby_periodo;

            return $equipment;
        });
    }

    /**
     * Calcula el consumo de un único equipo para un período y uso determinados.
     *
     * @param \App\Models\EntityEquipment $equipment
     * @param int $numberOfDays
     * @param int $avgDailyUseMinutes
     * @return array
     */
    public function calculateConsumptionForEquipment(\App\Models\EntityEquipment $equipment, int $numberOfDays, int $avgDailyUseMinutes): array
    {
        // --- CÁLCULO ACTIVO ---
        $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
        $consumoNominalKW = $powerWatts / 1000;
        $horasDeUsoDiario = $avgDailyUseMinutes / 60;
        $horasDeUsoPeriodo = $horasDeUsoDiario * $numberOfDays;

        $calculationFactor = $equipment->equipmentType->equipmentCategory->calculationFactor;
        $factorCarga = $calculationFactor->load_factor ?? 1;
        $eficiencia = $calculationFactor->efficiency_factor ?? 1;

        if ($eficiencia == 0) $eficiencia = 1;

        $energiaSecundariaConsumida = $horasDeUsoPeriodo * $factorCarga * $equipment->quantity * $consumoNominalKW / $eficiencia;

        // --- CÁLCULO STAND BY ---
        $consumoStandByKwh = 0; // Default to 0
        if ($equipment->has_standby_mode) {
            $standbyPowerWatts = $equipment->equipmentType->standby_power_watts ?? 0;
            $horasStandByPeriodo = (24 * $numberOfDays) - $horasDeUsoPeriodo;
            if ($horasStandByPeriodo < 0) $horasStandByPeriodo = 0;

            $consumoStandByKwh = ($standbyPowerWatts / 1000) * $horasStandByPeriodo * $equipment->quantity;
        }

        // --- RESULTADOS ---
        $consumo_kwh_activo_periodo = round($energiaSecundariaConsumida, 2);
        $consumo_kwh_standby_periodo = round($consumoStandByKwh, 2);
        $consumo_kwh_total_periodo = $consumo_kwh_activo_periodo + $consumo_kwh_standby_periodo;

        return [
            'consumo_kwh_activo_periodo' => $consumo_kwh_activo_periodo,
            'consumo_kwh_standby_periodo' => $consumo_kwh_standby_periodo,
            'consumo_kwh_total_periodo' => $consumo_kwh_total_periodo,
        ];
    }
}