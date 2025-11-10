<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\EquipmentUsageSnapshot;
use App\Services\WeatherService;
use App\Services\EquipmentCalculationService;
use App\Services\ClimateCorrelationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsageSnapshotController extends Controller
{
    protected $calculationService;
    protected $climateService;

    public function __construct(
        EquipmentCalculationService $calculationService,
        ClimateCorrelationService $climateService
    ) {
        $this->calculationService = $calculationService;
        $this->climateService = $climateService;
    }

    /**
     * Muestra el formulario para ajustar el uso de equipos para un período específico (factura).
     */
    public function create(Invoice $invoice)
    {
        // Gating: confirmar Gestión de Uso antes de ajustar
        if (!session()->has('usage_confirmed_at')) {
            return redirect()->route('usage.index', ['invoice' => $invoice->id])
                ->with('warning', 'Antes de ajustar un período, confirmá la Gestión de Uso (días/semana)');
        }
        // Obtenemos la entidad a través del suministro del contrato de la factura
        $entity = $invoice->contract->supply->entity;
        
        // Verificamos que el usuario tenga permiso para ver esta entidad
        $this->authorize('view', $entity);
        
        // Obtenemos todos los equipos de la entidad
        $equipments = $entity->equipments()->with(['equipmentType', 'equipmentType.equipmentCategory'])->get();
        
        // Si no hay equipos, redirigimos a agregar equipos
        if ($equipments->isEmpty()) {
            return redirect()
                ->route('entities.equipment.index', $entity)
                ->with('warning', 'Primero debes agregar equipos a tu inventario antes de ajustar el consumo.');
        }
        
        // Obtenemos los snapshots existentes para esta factura (si ya se ajustaron antes)
        $existingSnapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
            ->get()
            ->keyBy('entity_equipment_id');
        
        // Calculamos los días del período
        $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
        
        // Obtener Climate Snapshot del período
        $climateSnapshot = $invoice->climateSnapshot;
        
        // Si no existe, intentar crearlo
        if (!$climateSnapshot && $entity->locality_id) {
            $weatherService = new WeatherService();
            try {
                $climateSnapshot = $weatherService->createClimateSnapshot(
                    $entity,
                    $invoice->start_date,
                    $invoice->end_date
                );
                $invoice->climate_snapshot_id = $climateSnapshot->id;
                $invoice->saveQuietly();
            } catch (\Exception $e) {
                \Log::warning("No se pudo crear ClimateSnapshot para Invoice #{$invoice->id}: {$e->getMessage()}");
            }
        }
        
        // Buscar períodos similares para comparación
        $similarPeriods = collect();
        if ($climateSnapshot) {
            $weatherService = $weatherService ?? new WeatherService();
            $similarPeriods = $weatherService->findSimilarPeriods($climateSnapshot, 5);
        }
        
        // PRE-CALCULAR consumos con el Service (lógica centralizada)
        $tariff = $this->calculationService->calculateAverageTariff($invoice);
        
        // Categorías que merecen ajuste por clima y margen de días
        $categoriesWithClimateAdjustment = ['Climatización', 'Calefón Eléctrico', 'Calefacción'];
        $daysDiscountRatio = 0.75; // 25% de descuento
        $effectiveDaysCache = [];
        
        $equipmentsWithCalculations = $equipments->map(function($equipment) use ($periodDays, $tariff, $categoriesWithClimateAdjustment, $daysDiscountRatio, $invoice, &$effectiveDaysCache) {
            $categoryName = $equipment->equipmentType?->equipmentCategory?->name ?? '';
            if (in_array($categoryName, $categoriesWithClimateAdjustment)) {
                // Obtener días efectivos por clima
                if (!isset($effectiveDaysCache[$categoryName])) {
                    $climateData = $this->climateService->getEffectiveDaysForClimateEquipment($invoice, $categoryName);
                    $effectiveDaysCache[$categoryName] = $climateData;
                }
                $daysByClimate = $effectiveDaysCache[$categoryName]['effective_days'];
                $daysByMargin = (int) max(1, round($periodDays * $daysDiscountRatio));
                // Usar el menor entre clima y margen
                $effectiveDays = min($daysByClimate, $daysByMargin);
                $equipment->climate_adjusted = true;
                $equipment->climate_data_source = $effectiveDaysCache[$categoryName]['data_source'] . ' + margen 25%';
            } else {
                $effectiveDays = $periodDays;
                $equipment->climate_adjusted = false;
            }
            // Pre-cálculo usa días efectivos definitivos
            $calc = $this->calculationService->calculateEquipmentConsumption($equipment, $effectiveDays, $tariff);
            $equipment->precalc_kwh_activo = $calc['kwh_activo'];
            $equipment->precalc_kwh_standby = $calc['kwh_standby'];
            $equipment->precalc_kwh_total = $calc['kwh_total'];
            $equipment->precalc_costo = $calc['costo'];
            $equipment->precalc_horas_uso = $calc['horas_uso'];
            $equipment->effective_days = $effectiveDays;
            return $equipment;
        });
        
        return view('snapshots.create', [
            'invoice' => $invoice,
            'entity' => $entity,
            'equipments' => $equipmentsWithCalculations,
            'existingSnapshots' => $existingSnapshots,
            'periodDays' => $periodDays,
            'climateSnapshot' => $climateSnapshot,
            'similarPeriods' => $similarPeriods,
            'tariff' => $tariff,
            'effectiveDaysCache' => $effectiveDaysCache, // Datos de ajuste climático
        ]);
    }

    /**
     * Guarda los ajustes de uso de equipos para el período de la factura.
     * RECALCULA TODO con EquipmentCalculationService (ignora valores del frontend).
     */
    public function store(Request $request, Invoice $invoice)
    {
        $entity = $invoice->contract->supply->entity;
        $this->authorize('view', $entity);
        
        // Validamos los datos
        $request->validate([
            'equipments' => 'required|array',
            'equipments.*.entity_equipment_id' => 'required|exists:entity_equipment,id',
            'equipments.*.avg_daily_use_minutes' => 'required|numeric|min:0|max:1440',
        ]);
        
        DB::transaction(function () use ($request, $invoice) {
            // Soft-delete snapshots anteriores de esta factura (mantener historial)
            EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->delete();
            
            $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
            $tariff = $this->calculationService->calculateAverageTariff($invoice);
            
            // Categorías con ajuste climático y margen de días
            $categoriesWithClimateAdjustment = ['Climatización', 'Calefón Eléctrico', 'Calefacción'];
            $daysDiscountRatio = 0.75;
            $effectiveDaysCache = [];
            foreach ($request->equipments as $equipmentData) {
                $entityEquipment = EntityEquipment::with(['equipmentType.equipmentCategory'])->find($equipmentData['entity_equipment_id']);
                $categoryName = $entityEquipment->equipmentType?->equipmentCategory?->name ?? '';
                if (in_array($categoryName, $categoriesWithClimateAdjustment)) {
                    if (!isset($effectiveDaysCache[$categoryName])) {
                        $climateData = $this->climateService->getEffectiveDaysForClimateEquipment($invoice, $categoryName);
                        $effectiveDaysCache[$categoryName] = $climateData;
                    }
                    $daysByClimate = $effectiveDaysCache[$categoryName]['effective_days'];
                    $daysByMargin = (int) max(1, round($periodDays * $daysDiscountRatio));
                    $effectiveDays = min($daysByClimate, $daysByMargin);
                } else {
                    $effectiveDays = $periodDays;
                }
                $originalMinutes = $entityEquipment->avg_daily_use_minutes_override;
                $entityEquipment->avg_daily_use_minutes_override = $equipmentData['avg_daily_use_minutes'];
                $calc = $this->calculationService->calculateEquipmentConsumption(
                    $entityEquipment,
                    $effectiveDays,
                    $tariff
                );
                $entityEquipment->avg_daily_use_minutes_override = $originalMinutes;
                
                // Obtenemos datos para el snapshot
                $powerWatts = $entityEquipment->power_watts_override ?? $entityEquipment->equipmentType->default_power_watts;
                $hasStandby = (bool)($entityEquipment->has_standby_mode ?? false);
                $isDaily = (bool)($entityEquipment->is_daily_use ?? false);
                $daysPerWeek = $entityEquipment->usage_days_per_week;
                $usageWeekdays = $entityEquipment->usage_weekdays ?? null;
                
                EquipmentUsageSnapshot::create([
                    'entity_equipment_id' => $equipmentData['entity_equipment_id'],
                    'invoice_id' => $invoice->id,
                    'start_date' => $invoice->start_date,
                    'end_date' => $invoice->end_date,
                    'avg_daily_use_minutes' => $equipmentData['avg_daily_use_minutes'],
                    'power_watts' => $powerWatts,
                    'has_standby_mode' => $hasStandby,
                    'is_daily_use' => $isDaily,
                    'usage_days_per_week' => $isDaily ? null : $daysPerWeek,
                    'usage_weekdays' => $isDaily ? null : $usageWeekdays,
                    'minutes_per_session' => $entityEquipment->minutes_per_session,
                    'frequency_source' => 'inherited',
                    'calculated_kwh_period' => $calc['kwh_total'], // Valor calculado por el Service
                ]);
            }
        });
        
        return redirect()
            ->route('entities.show', $entity)
            ->with('success', 'Ajustes de equipos guardados exitosamente. El análisis se ha actualizado.');
    }

    /**
     * Muestra un resumen (dashboard) de los snapshots guardados para una factura.
     */
    public function show(Invoice $invoice)
    {
        $entity = $invoice->contract->supply->entity;
        $this->authorize('view', $entity);

        $snapshots = EquipmentUsageSnapshot::with(['entityEquipment.equipmentType.equipmentCategory'])
            ->where('invoice_id', $invoice->id)
            ->get();

        if ($snapshots->isEmpty()) {
            return redirect()
                ->route('snapshots.create', $invoice)
                ->with('info', 'Aún no hay ajustes guardados para este período. Configúralos ahora.');
        }

        $totalEstimated = $snapshots->sum('calculated_kwh_period');
        $real = (float) ($invoice->total_energy_consumed_kwh ?? 0);
        $percent = $real > 0 ? ($totalEstimated / $real) * 100 : null;

        // Agrupar por ambiente (location) para un mini resumen
        $byRoom = $snapshots->groupBy(function ($s) {
            $loc = $s->entityEquipment->location ?? 'Sin ubicación';
            // Si location viene JSON, intentamos decodificar nombre simple
            $decoded = json_decode($loc, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return $decoded['name'] ?? ($decoded['rooms'][0]['name'] ?? 'Sin ubicación');
                }
            }
            return $loc ?: 'Sin ubicación';
        })->map(function ($group) {
            return [
                'kwh' => $group->sum('calculated_kwh_period'),
            ];
        })->sortByDesc('kwh');

        return view('snapshots.show', [
            'invoice' => $invoice,
            'entity' => $entity,
            'snapshots' => $snapshots,
            'totalEstimated' => $totalEstimated,
            'percent' => $percent,
            'byRoom' => $byRoom,
        ]);
    }

    /**
     * Devuelve recomendaciones de standby SOLO para el período de esta factura (sin modificar historial).
     * GET /standby/recommendations/{invoice}
     */
    public function recommendations(Invoice $invoice)
    {
        $entity = $invoice->contract->supply->entity;
        $this->authorize('view', $entity);

        // Si ya existen snapshots para la factura, no recalculamos (el usuario ya cerró el período)
        $hasSnapshots = \App\Models\EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->exists();
        if ($hasSnapshots) {
            return response()->json([
                'invoice_id' => $invoice->id,
                'status' => 'already_adjusted',
                'message' => 'El período ya tiene ajustes guardados. No se generan recomendaciones.',
                'equipments' => [],
            ]);
        }

        $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

        // Traer todos los equipos de la entidad con sus tipos y categorías
        $equipments = $entity->equipments()->with(['equipmentType.equipmentCategory'])->get();

        $thresholdMinorKwh = 0.05; // kWh totales de standby en el período considerados "muy bajos"
        $thresholdLowUseMinutes = 15; // minutos/día de uso activo que consideramos marginal

        $result = [];
        foreach ($equipments as $eq) {
            $type = $eq->equipmentType;
            $category = $type?->equipmentCategory;

            $powerWatts = $eq->power_watts_override ?? $type?->default_power_watts ?? 0;
            // Uso activo estimado (minutos/día) - si no tenemos override usamos default avg horas
            $activeMinutesPerDay = $eq->avg_daily_use_minutes_override
                ?? (($type?->default_avg_daily_use_hours ?? 0) * 60);

            $activeHoursPeriod = ($activeMinutesPerDay / 60) * $periodDays;
            $activeKwhPeriod = ($powerWatts / 1000) * $activeHoursPeriod * max(1, (int)$eq->quantity);

            $standbyWatts = $type?->standby_power_watts ?? 0;
            $standbyHours = max(0, ($periodDays * 24) - $activeHoursPeriod);
            $standbyKwhPeriod = ($standbyWatts / 1000) * $standbyHours * max(1, (int)$eq->quantity);

            $currentHasStandby = (bool)$eq->has_standby_mode;
            $suggestedHasStandby = $currentHasStandby; // por defecto mantenemos
            $reason = 'Mantener';

            // Criterios para DESACTIVAR standby:
            // 1. Potencia standby cero
            if ($standbyWatts <= 0) {
                $suggestedHasStandby = false;
                $reason = 'Sin potencia standby declarada';
            }
            // 2. Categoría marcada sin soporte
            elseif ($category && !$category->supports_standby) {
                $suggestedHasStandby = false;
                $reason = 'Categoría sin soporte de standby';
            }
            // 3. Consumo standby muy bajo en todo el período
            elseif ($standbyKwhPeriod < $thresholdMinorKwh && $currentHasStandby) {
                $suggestedHasStandby = false;
                $reason = 'Standby marginal (<'.$thresholdMinorKwh.' kWh período)';
            }
            // 4. Uso activo muy bajo y standby relativamente bajo (optimizable)
            elseif ($activeMinutesPerDay < $thresholdLowUseMinutes && $standbyKwhPeriod < 0.2 && $currentHasStandby) {
                $suggestedHasStandby = false;
                $reason = 'Uso activo muy bajo y standby poco relevante';
            }
            // Nota: se pueden agregar reglas futuras (p.ej. porcentaje standby vs total)

            $result[] = [
                'id' => $eq->id,
                'name' => $eq->custom_name ?? ($type?->name ?? 'Equipo'),
                'category' => $category?->name,
                'quantity' => (int)$eq->quantity,
                'power_watts' => $powerWatts,
                'standby_watts' => $standbyWatts,
                'active_minutes_per_day' => (int)$activeMinutesPerDay,
                'active_kwh_period' => round($activeKwhPeriod, 4),
                'standby_kwh_period' => round($standbyKwhPeriod, 4),
                'current_has_standby' => $currentHasStandby,
                'suggested_has_standby' => $suggestedHasStandby,
                'reason' => $reason,
            ];
        }

        return response()->json([
            'invoice_id' => $invoice->id,
            'status' => 'ok',
            'period_days' => $periodDays,
            'equipments' => $result,
        ]);
    }
}
