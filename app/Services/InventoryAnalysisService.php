<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;


class InventoryAnalysisService
{
    protected $replacementService;
    protected $calculationService;

    public function __construct(
        ReplacementAnalysisService $replacementService,
        EquipmentCalculationService $calculationService
    ) {
        $this->replacementService = $replacementService;
        $this->calculationService = $calculationService;
    }

    /**
     * Orquestador principal para encontrar todas las oportunidades de mejora.
     *
     * @param Entity $entity
     * @return array
     */
    public function findAllOpportunities(Entity $entity): array
    {
        // 1. Calcular el costo unitario de la energía a partir de la última factura
        $supplyIds = $entity->supplies()->pluck('id');
        
        $lastInvoice = \App\Models\Invoice::whereHas('contract', function($query) use ($supplyIds) {
                $query->whereIn('supply_id', $supplyIds);
            })
            ->orderBy('end_date', 'desc')
            ->first();

        if (!$lastInvoice || $lastInvoice->total_energy_consumed_kwh == 0) {
            return []; // No se pueden generar recomendaciones sin datos de facturación
        }

        $costoUnitarioKwh = $lastInvoice->total_amount / $lastInvoice->total_energy_consumed_kwh;

        // 2. Obtener el perfil de consumo anual del inventario
        $annualProfile = $this->getAnnualEnergyProfile($entity);

        // 3. Buscar oportunidades de reemplazo
        // Renombramos la propiedad 'consumo_kwh_total_periodo' a 'energia_secundaria_kwh' para que coincida
        // con lo que espera el servicio de reemplazo.
        $annualProfileForReplacement = $annualProfile->map(function ($item) {
            $item->energia_secundaria_kwh = $item->consumo_kwh_total_periodo;
            $item->consumo_nominal_kw = ($item->power_watts_override ?? $item->equipmentType->default_power_watts ?? 0) / 1000;
            $item->horas_uso_anual = ($item->avg_daily_use_minutes_override ?? $item->equipmentType->default_avg_daily_use_minutes ?? 0) / 60 * 365;
            return $item;
        });
        
        $replacementOpps = $this->replacementService->findReplacementOpportunities($annualProfileForReplacement, $costoUnitarioKwh);

        // Aquí se podrían añadir otros tipos de oportunidades (ej: de hábitos, de configuración, etc.)
        $allOpportunities = array_merge($replacementOpps);

        return $allOpportunities;
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
        // El número de días para el cálculo es 365
        return $this->calculateEnergyProfileForPeriod($entity, 365);
    }

    /**
     * Calcula el perfil energético del inventario para un período específico (ej: un mes).
     * REFACTORIZADO: Ahora delega cálculos a EquipmentCalculationService para consistencia.
     *
     * @param Entity $entity
     * @param int $numberOfDays
     * @return Collection
     */
    public function calculateEnergyProfileForPeriod(Entity $entity, int $numberOfDays): Collection
    {
        $inventory = $entity->equipments()
            ->with(['equipmentType.equipmentCategory.calculationFactor'])
            ->get();

        // Obtener tarifa promedio de última factura
        $supplyIds = $entity->supplies()->pluck('id');
        $lastInvoice = \App\Models\Invoice::whereHas('contract', function($query) use ($supplyIds) {
                $query->whereIn('supply_id', $supplyIds);
            })
            ->orderBy('end_date', 'desc')
            ->first();

        $tariff = $lastInvoice ? $this->calculationService->calculateAverageTariff($lastInvoice) : 0.2;

        return $inventory->map(function ($equipment) use ($numberOfDays, $tariff) {
            // DELEGAMOS TODO EL CÁLCULO al servicio unificado
            $calc = $this->calculationService->calculateEquipmentConsumption($equipment, $numberOfDays, $tariff);
            
            // Asignamos resultados al objeto equipment (para compatibilidad con código legacy)
            $equipment->consumo_kwh_activo_periodo = $calc['kwh_activo'];
            $equipment->consumo_kwh_standby_periodo = $calc['kwh_standby'];
            $equipment->consumo_kwh_total_periodo = $calc['kwh_total'];
            $equipment->costo_periodo = $calc['costo'];
            $equipment->horas_uso_periodo = $calc['horas_uso'];
            $equipment->horas_standby_periodo = $calc['horas_standby'];

            return $equipment;
        });
    }

    // Nota: Los métodos para buscar oportunidades (findReplacementOpportunities, etc.)
    // seguirán funcionando bien porque se basan en el perfil ANUAL.
    // Podríamos crear un nuevo método orquestador para ellos.
}