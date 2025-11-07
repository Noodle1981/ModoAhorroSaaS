<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\ClimateCorrelationService;
use App\Services\ConsumptionPredictionService;
use App\Services\SmartAlertService;
use Illuminate\Http\Request;

class InsightsController extends Controller
{
    protected ClimateCorrelationService $climateService;
    protected ConsumptionPredictionService $predictionService;
    protected SmartAlertService $alertService;

    public function __construct(
        ClimateCorrelationService $climateService,
        ConsumptionPredictionService $predictionService,
        SmartAlertService $alertService
    ) {
        $this->climateService = $climateService;
        $this->predictionService = $predictionService;
        $this->alertService = $alertService;
    }

    /**
     * Muestra el dashboard de insights para una entidad
     */
    public function show(Entity $entity)
    {
        $this->authorize('view', $entity);

        // 1. Análisis de correlación clima-consumo
        $correlation = $this->climateService->analyzeCorrelation($entity, 12);
        
        // 2. Predicción de consumo futuro
        $prediction = $this->predictionService->predictFutureConsumption($entity, 14);
        
        // 3. Alertas activas
        $alerts = $this->alertService->getActiveAlerts($entity);
        
        // 4. Recomendaciones basadas en análisis
        $recommendations = [];
        if ($correlation['success']) {
            $recommendations = $this->climateService->generateRecommendations($correlation);
        }

        // 5. Comparativa de períodos (último mes vs anteriores)
        $comparison = $this->getPeriodsComparison($entity);

        return view('insights.show', [
            'entity' => $entity,
            'correlation' => $correlation,
            'prediction' => $prediction,
            'alerts' => $alerts,
            'recommendations' => $recommendations,
            'comparison' => $comparison,
        ]);
    }

    /**
     * Obtiene comparativa de períodos para gráficos
     */
    private function getPeriodsComparison(Entity $entity): array
    {
        $invoices = \App\Models\Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
            ->whereNotNull('total_energy_consumed_kwh')
            ->where('total_energy_consumed_kwh', '>', 0)
            ->orderBy('start_date', 'desc')
            ->limit(12)
            ->get();

        if ($invoices->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No hay facturas suficientes para comparar períodos.',
            ];
        }

        $current = $invoices->first();
        $previous = $invoices->skip(1)->first();
        $yearAgo = $invoices->firstWhere(function($inv) use ($current) {
            return $inv->start_date->month === $current->start_date->month 
                && $inv->start_date->year === $current->start_date->year - 1;
        });

        return [
            'success' => true,
            'current' => [
                'period' => $current->start_date->format('M Y'),
                'kwh' => $current->total_energy_consumed_kwh,
                'cost' => $current->total_amount ?? 0,
            ],
            'previous' => $previous ? [
                'period' => $previous->start_date->format('M Y'),
                'kwh' => $previous->total_energy_consumed_kwh,
                'cost' => $previous->total_amount ?? 0,
                'change_percent' => round((($current->total_energy_consumed_kwh - $previous->total_energy_consumed_kwh) / $previous->total_energy_consumed_kwh) * 100, 1),
            ] : null,
            'year_ago' => $yearAgo ? [
                'period' => $yearAgo->start_date->format('M Y'),
                'kwh' => $yearAgo->total_energy_consumed_kwh,
                'cost' => $yearAgo->total_amount ?? 0,
                'change_percent' => round((($current->total_energy_consumed_kwh - $yearAgo->total_energy_consumed_kwh) / $yearAgo->total_energy_consumed_kwh) * 100, 1),
            ] : null,
            'last_12_months' => $invoices->map(fn($inv) => [
                'period' => $inv->start_date->format('M Y'),
                'kwh' => $inv->total_energy_consumed_kwh,
                'cost' => $inv->total_amount ?? 0,
            ])->toArray(),
        ];
    }
}
