<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Services\EquipmentCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EconomicsCenterController extends Controller
{
    protected $calculationService;

    public function __construct(EquipmentCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Centro Económico: visión consolidada de costos, ahorros y potenciales inversiones.
     * Usa EquipmentCalculationService para cálculos precisos con tipo_de_proceso.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Capturar últimas facturas (para baseline de gasto)
        $invoices = Invoice::whereHas('contract.supply.entity', function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })
            ->orderByDesc('end_date')
            ->limit(3)
            ->get();

        // Equipos con relaciones necesarias para cálculos
        $equipments = EntityEquipment::whereHas('entity', function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })
            ->with(['entity', 'equipmentType', 'processFactor'])
            ->get();

        // Período de referencia: 30 días (mensual)
        $daysRef = 30;

        // Tarifa promedio de las facturas
        $avgTariff = 0.2; // Fallback
        if ($invoices->isNotEmpty()) {
            $tariffs = $invoices->filter(fn($inv) => ($inv->total_energy_consumed_kwh ?? 0) > 0 && ($inv->total_amount ?? 0) > 0)
                ->map(fn($inv) => $this->calculationService->calculateAverageTariff($inv));
            $avgTariff = $tariffs->isNotEmpty() ? $tariffs->avg() : 0.2;
        }

        // ============================================
        // CÁLCULOS USANDO EL NUEVO SERVICE
        // ============================================

        // 1. Consumo total mensual REAL (usando tipo_de_proceso)
        $bulkCalculation = $this->calculationService->calculateBulkConsumption(
            $equipments, 
            $daysRef, 
            $avgTariff
        );

        // 2. Potencial de ahorro por Standby (usando cálculos precisos con detalles)
        $standbySavings = $this->calculationService->calculateStandbySavingsPotential(
            $equipments, 
            $daysRef, 
            $avgTariff
        );

        // 3. Análisis de reemplazo de equipos
        // Generar sugerencias automáticas
        $replacementSuggestions = $this->calculationService->generateReplacementSuggestions(
            $equipments,
            $daysRef,
            $avgTariff
        );
        
        // Calcular ROI y ahorros si hay sugerencias
        $replacementAnalysis = null;
        if (!empty($replacementSuggestions)) {
            $replacementAnalysis = $this->calculationService->calculateReplacementAnalysis(
                $equipments,
                $replacementSuggestions,
                $daysRef,
                $avgTariff
            );
        }

        // 4. Gasto mensual estimado desde facturas reales
        $monthlyCostEstimate = null;
        $validAmounts = $invoices->filter(fn($inv) => ($inv->total_amount ?? 0) > 0);
        if ($validAmounts->isNotEmpty()) {
            // Normalizar por duración del período: costo por día * 30
            $perMonth = $validAmounts->map(function($inv){
                $days = max(1, $inv->start_date->diffInDays($inv->end_date) + 1);
                return ($inv->total_amount / $days) * 30;
            });
            $monthlyCostEstimate = round($perMonth->avg(), 2);
        }

        // Comparar costo calculado vs costo de facturas
        $calculatedVsActual = null;
        if ($monthlyCostEstimate && $bulkCalculation['total_cost'] > 0) {
            $diff = $monthlyCostEstimate - $bulkCalculation['total_cost'];
            $diffPercent = ($diff / $monthlyCostEstimate) * 100;
            $calculatedVsActual = [
                'actual' => $monthlyCostEstimate,
                'calculated' => $bulkCalculation['total_cost'],
                'difference' => round($diff, 2),
                'difference_percent' => round($diffPercent, 2),
            ];
        }

        $metrics = [
            'monthly_cost_estimate' => $monthlyCostEstimate, // Desde facturas reales
            'monthly_cost_calculated' => $bulkCalculation['total_cost'], // Desde equipos
            'monthly_kwh_calculated' => $bulkCalculation['total_kwh'],
            'avg_tariff' => round($avgTariff, 4),
            
            // Standby
            'standby_kwh' => $standbySavings['standby_kwh'],
            'standby_cost' => $standbySavings['standby_cost'],
            'standby_savings_percent' => $standbySavings['savings_percentage'],
            'standby_equipment_count' => $standbySavings['equipment_count'],
            
            // Reemplazo
            'replacement_total_investment' => $replacementAnalysis['total_inversion'] ?? null,
            'replacement_annual_savings' => $replacementAnalysis['total_ahorro_anual_estimado'] ?? null,
            'replacement_payback_years' => $replacementAnalysis['total_payback_años'] ?? null,
            'replacement_viable_count' => $replacementAnalysis['viable_count'] ?? 0,
            'replacement_suggestions_count' => $replacementAnalysis['equipment_count'] ?? 0,
            
            // Comparación
            'calculated_vs_actual' => $calculatedVsActual,
            
            // Próximas fases
            'usage_optimization_savings' => null, // TODO: días/semana confirmados vs default
        ];

        // Detalles por equipo para gráficos/tablas
        $equipmentDetails = $bulkCalculation['equipments_detail'];
        
        // Detalles de standby
        $standbyDetails = $standbySavings['equipment_details'] ?? [];
        
        // Detalles de reemplazo
        $replacementDetails = $replacementAnalysis['comparisons'] ?? [];

        return view('economics.index', compact(
            'invoices', 
            'equipments', 
            'metrics', 
            'equipmentDetails',
            'standbyDetails',
            'replacementDetails'
        ));
    }
}
