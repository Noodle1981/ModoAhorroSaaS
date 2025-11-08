<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\Recommendation;
use App\Models\SmartAlert;

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

        // Alertas globales (últimas 5 de todas las entidades del usuario)
        $entityIds = $entities->pluck('id');
        
        // Gestionar alerta de Standby pendiente
        $this->manageStandbyAlert($user, $entityIds);
    // Gestionar alerta de Uso pendiente
    $this->manageUsageAlert($user, $entityIds);
        
        $recentAlerts = SmartAlert::whereIn('entity_id', $entityIds)
            ->active()
            ->with('entity')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'entitiesData' => $entitiesData,
            'blockedEntities' => $blockedEntities,
            'recentAlerts' => $recentAlerts,
        ]);
    }

    /**
     * Gestiona la alerta de Standby pendiente.
     * Si no está confirmado, crea la alerta (una sola para toda la compañía).
     * Si ya está confirmado, descarta la alerta existente.
     */
    private function manageStandbyAlert($user, $entityIds)
    {
        $confirmedAt = session('standby_confirmed_at');
        
        // Si no hay entidades, no hacer nada
        if ($entityIds->isEmpty()) {
            return;
        }
        
        // Usar la primera entidad del usuario para vincular la alerta (o null si prefieres company-wide)
        $firstEntityId = $entityIds->first();
        
        if (!$confirmedAt) {
            // No confirmado: verificar si ya existe la alerta
            $exists = SmartAlert::where('type', 'standby_pending')
                ->where('entity_id', $firstEntityId)
                ->active()
                ->exists();
            
            if (!$exists) {
                // Contar equipos con standby potencial (de categorías clave)
                $equipmentCount = EntityEquipment::whereHas('entity', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->whereHas('equipmentType.equipmentCategory', function($q) {
                        $q->whereIn('id', [1, 4, 5, 12]); // Climatización, Cocina, Entretenimiento, Seguridad
                    })
                    ->count();
                
                SmartAlert::create([
                    'entity_id' => $firstEntityId,
                    'invoice_id' => null,
                    'type' => 'standby_pending',
                    'severity' => 'info',
                    'title' => 'Configurá la gestión de Standby',
                    'description' => sprintf(
                        'Tenés %d equipos en categorías que suelen consumir en modo standby. Revisá y confirmá tu configuración para optimizar tu consumo.',
                        $equipmentCount
                    ),
                    'data' => ['equipment_count' => $equipmentCount],
                ]);
            }
        } else {
            // Ya confirmado: descartar alerta si existe
            SmartAlert::where('type', 'standby_pending')
                ->where('entity_id', $firstEntityId)
                ->active()
                ->update([
                    'is_dismissed' => true,
                    'dismissed_at' => now(),
                ]);
        }
    }

    /**
     * Gestiona la alerta de Uso pendiente (frecuencia de días/semana).
     */
    private function manageUsageAlert($user, $entityIds)
    {
        $confirmedAt = session('usage_confirmed_at');
        if ($entityIds->isEmpty()) { return; }
        $firstEntityId = $entityIds->first();

        if (!$confirmedAt) {
            $exists = SmartAlert::where('type', 'usage_pending')
                ->where('entity_id', $firstEntityId)
                ->active()
                ->exists();
            if (!$exists) {
                $equipmentCount = \App\Models\EntityEquipment::whereHas('entity', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->count();
                SmartAlert::create([
                    'entity_id' => $firstEntityId,
                    'invoice_id' => null,
                    'type' => 'usage_pending',
                    'severity' => 'info',
                    'title' => 'Configurá la frecuencia de uso',
                    'description' => "Tenés {$equipmentCount} equipos. Definí cuántos días por semana se usan antes de ajustar consumos.",
                    'data' => ['equipment_count' => $equipmentCount],
                ]);
            }
        } else {
            SmartAlert::where('type', 'usage_pending')
                ->where('entity_id', $firstEntityId)
                ->active()
                ->update([
                    'is_dismissed' => true,
                    'dismissed_at' => now(),
                ]);
        }
    }
}
