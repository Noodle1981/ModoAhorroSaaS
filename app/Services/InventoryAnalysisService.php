<?php

namespace App\Services;

use App\Models\Entity;
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

        return $inventory->map(function ($equipment) use ($numberOfDays) {
            
            // --- CÁLCULO ACTIVO ---
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
            $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;

            // Derivar minutos diarios según frecuencia (si existen campos nuevos)
            if (isset($equipment->is_daily_use) || isset($equipment->usage_days_per_week)) {
                if ($equipment->is_daily_use) {
                    // Si es diario y existe minutes_per_session, usar eso; sino el avg existente
                    if (!empty($equipment->minutes_per_session)) {
                        $avgDailyUseMinutes = $equipment->minutes_per_session;
                    }
                } else {
                    $days = (int)($equipment->usage_days_per_week ?? 0);
                    if ($days > 0) {
                        if (!empty($equipment->minutes_per_session)) {
                            // Derivar promedio diario: (días * minutos_sesión) / 7
                            $avgDailyUseMinutes = (int)round(($equipment->minutes_per_session * $days) / 7);
                        } else {
                            // Si no hay minutes_per_session pero sí avg_daily ya lo usa, mantenerlo
                            // (el usuario quizás lo ajustó manualmente sin setear el patrón)
                        }
                    } else {
                        // Sin días declarados => 0 consumo activo
                        $avgDailyUseMinutes = 0;
                    }
                }
            }
            
            $consumoNominalKW = $powerWatts / 1000;
            $horasDeUsoDiario = $avgDailyUseMinutes / 60;
            $horasDeUsoPeriodo = $horasDeUsoDiario * $numberOfDays;

            $calculationFactor = $equipment->equipmentType->equipmentCategory->calculationFactor;
            $factorCarga = $calculationFactor->load_factor ?? 1;
            $eficiencia = $calculationFactor->efficiency_factor ?? 1;

            if ($eficiencia == 0) $eficiencia = 1;

            $energiaSecundariaConsumida = $horasDeUsoPeriodo * $factorCarga * $equipment->quantity * $consumoNominalKW / $eficiencia;
            
            // --- CÁLCULO STAND BY ---
            // Solo considerar standby si el equipo tiene modo standby habilitado explícitamente
            $standbyPowerWatts = 0;
            if (($equipment->has_standby_mode ?? false) === true) {
                $standbyPowerWatts = $equipment->equipmentType->standby_power_watts ?? 0;
            }
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