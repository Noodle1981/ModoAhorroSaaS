<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\SmartAlert;
use App\Services\ClimateCorrelationService;
use Carbon\Carbon;

class SmartAlertService
{
    protected ClimateCorrelationService $climateService;

    public function __construct(ClimateCorrelationService $climateService)
    {
        $this->climateService = $climateService;
    }

    /**
     * Genera alertas inteligentes para una entidad
     *
     * @param Entity $entity
     * @return array Estadísticas de alertas generadas
     */
    public function generateAlertsForEntity(Entity $entity): array
    {
        $newAlerts = 0;

        // 1. Analizar anomalías de consumo vs temperatura
        $newAlerts += $this->checkConsumptionAnomalies($entity);

        // 2. Detectar equipos ineficientes
        $newAlerts += $this->checkEquipmentEfficiency($entity);

        // 3. Alertas climáticas (próximos períodos extremos)
        $newAlerts += $this->checkClimateAlerts($entity);

        // 4. Desviación del baseline
        $newAlerts += $this->checkBaselineDeviation($entity);

        return [
            'entity_id' => $entity->id,
            'new_alerts' => $newAlerts,
            'active_alerts' => SmartAlert::where('entity_id', $entity->id)->active()->count(),
        ];
    }

    /**
     * Detecta anomalías de consumo basadas en análisis climático
     */
    private function checkConsumptionAnomalies(Entity $entity): int
    {
        $analysis = $this->climateService->analyzeCorrelation($entity, 12);
        
        if (!$analysis['success'] || empty($analysis['outliers'])) {
            return 0;
        }

        $created = 0;
        foreach ($analysis['outliers'] as $outlier) {
            if ($outlier['type'] === 'high') {
                // Buscar la factura correspondiente
                $invoice = Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
                    ->whereRaw("strftime('%m %Y', start_date) = ?", [$outlier['period']])
                    ->first();

                if (!$invoice) continue;

                // Evitar duplicados
                $exists = SmartAlert::where('entity_id', $entity->id)
                    ->where('invoice_id', $invoice->id)
                    ->where('type', 'consumption_anomaly')
                    ->exists();

                if ($exists) continue;

                SmartAlert::create([
                    'entity_id' => $entity->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'consumption_anomaly',
                    'severity' => abs($outlier['deviation_percent']) > 50 ? 'critical' : 'warning',
                    'title' => 'Consumo anómalo detectado',
                    'description' => sprintf(
                        'El período %s tuvo un consumo de %.2f kWh/día, un %.1f%% superior al promedio esperado. Temperatura promedio: %.1f°C.',
                        $outlier['period'],
                        $outlier['kwh_per_day'],
                        $outlier['deviation_percent'],
                        $outlier['avg_temp']
                    ),
                    'data' => $outlier,
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Detecta equipos ineficientes comparando con catálogo de mercado
     */
    private function checkEquipmentEfficiency(Entity $entity): int
    {
        // Obtener equipos con alto consumo estimado
        $equipments = $entity->equipments()
            ->with(['equipmentType', 'equipmentType.equipmentCategory'])
            ->get();

        $created = 0;

        foreach ($equipments as $equipment) {
            $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
            $dailyMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;

            // Consumo diario estimado en kWh
            $dailyKwh = ($powerWatts * ($dailyMinutes / 60)) / 1000;

            // Umbral simple: si un equipo consume más de 5 kWh/día, alertar
            if ($dailyKwh > 5) {
                $exists = SmartAlert::where('entity_id', $entity->id)
                    ->where('type', 'equipment_inefficiency')
                    ->where('data->equipment_id', $equipment->id)
                    ->where('created_at', '>', Carbon::now()->subDays(30))
                    ->exists();

                if ($exists) continue;

                SmartAlert::create([
                    'entity_id' => $entity->id,
                    'type' => 'equipment_inefficiency',
                    'severity' => $dailyKwh > 10 ? 'warning' : 'info',
                    'title' => 'Equipo con alto consumo detectado',
                    'description' => sprintf(
                        '%s está consumiendo aproximadamente %.2f kWh/día. Considera revisar su eficiencia o evaluar reemplazo por modelo más eficiente.',
                        $equipment->custom_name ?? $equipment->equipmentType->name,
                        $dailyKwh
                    ),
                    'data' => [
                        'equipment_id' => $equipment->id,
                        'equipment_name' => $equipment->custom_name ?? $equipment->equipmentType->name,
                        'daily_kwh' => round($dailyKwh, 2),
                        'power_watts' => $powerWatts,
                        'daily_minutes' => $dailyMinutes,
                    ],
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Alertas climáticas basadas en pronóstico (simulado por ahora)
     */
    private function checkClimateAlerts(Entity $entity): int
    {
        $locality = $entity->locality;
        if (!$locality) return 0;

        // Obtener clima de los próximos 7 días (usamos histórico como proxy por ahora)
        $weekData = \App\Models\DailyWeatherLog::where('locality_id', $locality->id)
            ->whereBetween('date', [Carbon::now()->subWeek(), Carbon::now()])
            ->selectRaw('AVG(avg_temp_celsius) as avg_temp, MAX(max_temp_celsius) as max_temp')
            ->first();

        if (!$weekData) return 0;

        $created = 0;

        // Alerta por ola de calor
        if ($weekData->max_temp > 35) {
            $exists = SmartAlert::where('entity_id', $entity->id)
                ->where('type', 'climate_alert')
                ->where('created_at', '>', Carbon::now()->subDays(7))
                ->exists();

            if (!$exists) {
                SmartAlert::create([
                    'entity_id' => $entity->id,
                    'type' => 'climate_alert',
                    'severity' => 'warning',
                    'title' => 'Alerta: Temperaturas elevadas',
                    'description' => sprintf(
                        'Se esperan temperaturas de hasta %.1f°C en los próximos días. Considera pre-enfriar tu hogar y optimizar el uso del AC durante horas pico.',
                        $weekData->max_temp
                    ),
                    'data' => [
                        'max_temp' => $weekData->max_temp,
                        'avg_temp' => $weekData->avg_temp,
                    ],
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Detecta desviaciones del consumo baseline (promedio histórico)
     */
    private function checkBaselineDeviation(Entity $entity): int
    {
        // Obtener última factura
        $lastInvoice = Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
            ->whereNotNull('total_energy_consumed_kwh')
            ->latest('end_date')
            ->first();

        if (!$lastInvoice) return 0;

        // Calcular baseline (promedio de los últimos 6 meses, excluyendo la última)
        $baseline = Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
            ->where('id', '!=', $lastInvoice->id)
            ->where('start_date', '>=', Carbon::now()->subMonths(6))
            ->whereNotNull('total_energy_consumed_kwh')
            ->avg('total_energy_consumed_kwh');

        if (!$baseline) return 0;

        $deviation = (($lastInvoice->total_energy_consumed_kwh - $baseline) / $baseline) * 100;

        // Alertar solo si la desviación es significativa (> 20%)
        if (abs($deviation) > 20) {
            $exists = SmartAlert::where('entity_id', $entity->id)
                ->where('invoice_id', $lastInvoice->id)
                ->where('type', 'baseline_deviation')
                ->exists();

            if (!$exists) {
                SmartAlert::create([
                    'entity_id' => $entity->id,
                    'invoice_id' => $lastInvoice->id,
                    'type' => 'baseline_deviation',
                    'severity' => abs($deviation) > 40 ? 'warning' : 'info',
                    'title' => $deviation > 0 ? 'Consumo superior al promedio' : 'Consumo inferior al promedio',
                    'description' => sprintf(
                        'Tu consumo en el período %s fue de %.2f kWh, un %.1f%% %s que tu promedio histórico de %.2f kWh.',
                        $lastInvoice->start_date->format('M Y'),
                        $lastInvoice->total_energy_consumed_kwh,
                        abs($deviation),
                        $deviation > 0 ? 'mayor' : 'menor',
                        $baseline
                    ),
                    'data' => [
                        'current_kwh' => $lastInvoice->total_energy_consumed_kwh,
                        'baseline_kwh' => round($baseline, 2),
                        'deviation_percent' => round($deviation, 1),
                    ],
                ]);

                return 1;
            }
        }

        return 0;
    }

    /**
     * Obtiene alertas activas para una entidad
     */
    public function getActiveAlerts(Entity $entity, int $limit = null): \Illuminate\Support\Collection
    {
        $query = SmartAlert::where('entity_id', $entity->id)
            ->active()
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Marca todas las alertas como leídas
     */
    public function markAllAsRead(Entity $entity): int
    {
        return SmartAlert::where('entity_id', $entity->id)
            ->active()
            ->update(['is_read' => true]);
    }
}
