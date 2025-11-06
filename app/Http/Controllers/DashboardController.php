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

        // Construir datos por entidad de forma segura
        $entitiesData = $entities->map(function ($entity) {
            $supplyIds = $entity->supplies()->pluck('id');

            // Última y penúltima factura
            $lastInvoice = Invoice::whereHas('contract', function ($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->orderBy('end_date', 'desc')
                ->first();

            $previousInvoice = Invoice::whereHas('contract', function ($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->orderBy('end_date', 'desc')
                ->skip(1)
                ->first();

            // Evolución últimos 6 meses
            $monthlyEvolution = Invoice::whereHas('contract', function ($query) use ($supplyIds) {
                    $query->whereIn('supply_id', $supplyIds);
                })
                ->where('end_date', '>=', now()->subMonths(6))
                ->orderBy('end_date', 'asc')
                ->get()
                ->groupBy(function ($invoice) {
                    return $invoice->end_date->format('Y-m');
                })
                ->map(function ($group) {
                    return [
                        'consumption' => $group->sum('total_energy_consumed_kwh'),
                        'month' => $group->first()->end_date->locale('es')->isoFormat('MMM'),
                    ];
                })
                ->values();

            // Consumo por categoría a partir de equipos
            $consumptionByCategory = $entity->equipments()
                ->with(['equipmentType.equipmentCategory'])
                ->get()
                ->groupBy(function ($eq) {
                    return $eq->equipmentType?->equipmentCategory?->name ?? 'Sin categoría';
                })
                ->map(function ($equipments, $categoryName) {
                    $totalMonthlyKwh = $equipments->sum(function ($eq) {
                        $power = $eq->power_watts_override ?? ($eq->equipmentType->default_power_watts ?? 0);
                        // Si no hay minutos override, derivar desde horas por defecto
                        $minutesOverride = $eq->avg_daily_use_minutes_override ?? null;
                        $hoursDefault = $eq->equipmentType->default_avg_daily_use_hours ?? 0;
                        $minutes = $minutesOverride !== null ? $minutesOverride : ($hoursDefault * 60);
                        $qty = max(1, $eq->quantity ?? 1);
                        $dailyKwh = ($power / 1000) * ($minutes / 60) * $qty;
                        return $dailyKwh * 30;
                    });

                    // Estimación simple de costo (fallback)
                    $estimatedCost = $totalMonthlyKwh * 20; // $/kWh aproximado

                    return [
                        'category' => $categoryName,
                        'consumption' => $totalMonthlyKwh,
                        'cost' => $estimatedCost,
                    ];
                })
                ->sortByDesc('consumption')
                ->values();

            $currentConsumption = $lastInvoice->total_energy_consumed_kwh ?? 0;
            $previousConsumption = $previousInvoice->total_energy_consumed_kwh ?? 0;
            $trend = $previousConsumption > 0
                ? (($currentConsumption - $previousConsumption) / $previousConsumption) * 100
                : 0;

            return [
                'entity' => $entity,
                'consumption' => $currentConsumption,
                'cost' => $lastInvoice->total_amount ?? 0,
                'trend' => $trend,
                'equipment_count' => $entity->equipments()->count(),
                'rooms_count' => is_array($entity->details ?? null) ? ($entity->details['rooms'] ?? null) : null,
                'monthly_evolution' => $monthlyEvolution,
                'consumption_by_category' => $consumptionByCategory,
                'last_invoice' => $lastInvoice,
            ];
        })->values();

        // Entidades bloqueadas (freemium / demo)
        $blockedEntities = [
            ['name' => 'Comercio', 'icon' => 'fa-store', 'type' => 'comercio'],
            ['name' => 'Oficina', 'icon' => 'fa-building', 'type' => 'oficina'],
        ];

        return view('dashboard', [
            'user' => $user,
            'entitiesData' => $entitiesData,
            'blockedEntities' => $blockedEntities,
        ]);
    }
}