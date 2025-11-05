<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\Recommendation;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal de la aplicación.
     */
    public function index(): View
    {
        $user = Auth::user()->load('company.entities.supplies.contracts');

        $entities = $user->company ? $user->company->entities : collect();

        // Calcular métricas globales
        $totalConsumption = 0;
        $totalCost = 0;
        $lastMonthConsumption = 0;
        $lastMonthCost = 0;
        $equipmentCount = 0;

        foreach ($entities as $entity) {
            // Obtener facturas de esta entidad a través de supplies -> contracts
            $supplyIds = $entity->supplies()->pluck('id');
            
            // Última factura de cada entidad
            $lastInvoice = Invoice::whereHas('contract', function($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastInvoice) {
                $totalConsumption += $lastInvoice->total_energy_consumed_kwh;
                $totalCost += $lastInvoice->total_amount;
            }

            // Penúltima factura para comparación
            $previousInvoice = Invoice::whereHas('contract', function($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->orderBy('end_date', 'desc')
                ->skip(1)
                ->first();

            if ($previousInvoice) {
                $lastMonthConsumption += $previousInvoice->total_energy_consumed_kwh;
                $lastMonthCost += $previousInvoice->total_amount;
            }

            // Contar equipos
            $equipmentCount += $entity->equipments()->count();
        }

        // Calcular tendencias
        $consumptionTrend = $lastMonthConsumption > 0 
            ? (($totalConsumption - $lastMonthConsumption) / $lastMonthConsumption) * 100 
            : 0;

        $costTrend = $lastMonthCost > 0 
            ? (($totalCost - $lastMonthCost) / $lastMonthCost) * 100 
            : 0;

        // Obtener todas las supply IDs de todas las entidades
        $allSupplyIds = collect();
        foreach ($entities as $entity) {
            $allSupplyIds = $allSupplyIds->merge($entity->supplies()->pluck('id'));
        }

        // Obtener últimas facturas para gráfico (últimos 6 meses)
        $recentInvoices = Invoice::whereHas('contract', function($query) use ($allSupplyIds) {
                $query->whereIn('supply_id', $allSupplyIds);
            })
            ->where('end_date', '>=', now()->subMonths(6))
            ->orderBy('end_date', 'asc')
            ->get()
            ->groupBy(function($invoice) {
                return $invoice->end_date->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'consumption' => $group->sum('total_energy_consumed_kwh'),
                    'cost' => $group->sum('total_amount'),
                    'month' => $group->first()->end_date->locale('es')->isoFormat('MMM YYYY'),
                ];
            });

        // Consumo por entidad para gráfico de distribución
        $consumptionByEntity = $entities->map(function($entity) {
            $supplyIds = $entity->supplies()->pluck('id');
            
            $lastInvoice = Invoice::whereHas('contract', function($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->orderBy('end_date', 'desc')
                ->first();

            return [
                'name' => $entity->name,
                'consumption' => $lastInvoice ? $lastInvoice->total_energy_consumed_kwh : 0,
                'cost' => $lastInvoice ? $lastInvoice->total_amount : 0,
            ];
        })->sortByDesc('consumption')->take(5);

        // Top equipos consumidores (estimado por potencia × uso promedio)
        $topEquipment = EntityEquipment::whereIn('entity_id', $entities->pluck('id'))
            ->with(['equipmentType', 'entity'])
            ->get()
            ->map(function($eq) {
                $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
                $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes ?? 0;
                $qty = max(1, $eq->quantity ?? 1);
                $dailyKwh = ($power / 1000) * ($minutes / 60) * $qty;
                $monthlyKwh = $dailyKwh * 30;

                return [
                    'name' => $eq->custom_name ?? $eq->equipmentType->name,
                    'entity' => $eq->entity->name,
                    'location' => $eq->location,
                    'monthly_kwh' => $monthlyKwh,
                    'power' => $power,
                ];
            })
            ->sortByDesc('monthly_kwh')
            ->take(10);

        // Recomendaciones activas (no implementadas)
        $activeRecommendations = Recommendation::whereIn('entity_id', $entities->pluck('id'))
            ->where('status', 'pending')
            ->orderBy('estimated_annual_savings_kwh', 'desc')
            ->take(5)
            ->get();

        $globalSummary = [
            'entity_count' => $entities->count(),
            'total_consumption' => $totalConsumption,
            'total_cost' => $totalCost,
            'equipment_count' => $equipmentCount,
            'consumption_trend' => $consumptionTrend,
            'cost_trend' => $costTrend,
        ];

        return view('dashboard', [
            'user' => $user,
            'entities' => $entities,
            'globalSummary' => (object) $globalSummary,
            'recentInvoices' => $recentInvoices,
            'consumptionByEntity' => $consumptionByEntity,
            'topEquipment' => $topEquipment,
            'activeRecommendations' => $activeRecommendations,
        ]);
    }
}