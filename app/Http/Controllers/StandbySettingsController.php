<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Models\EntityEquipment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class StandbySettingsController extends Controller
{
    /**
     * Muestra el panel de gestión de standby: categorías + equipos propios del usuario
     */
    public function index()
    {
        $user = auth()->user();

        // Traer categorías con conteo de tipos
        $categories = EquipmentCategory::withCount('equipmentTypes')->orderBy('name')->get();

        // Traer equipos del usuario con tipo y categoría para agrupar
        $equipments = EntityEquipment::whereHas('entity', function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })
        ->with('equipmentType.equipmentCategory', 'entity')
        ->get()
        ->groupBy(function($e) {
            return $e->equipmentType->equipmentCategory->name ?? 'Sin categoría';
        });

        $confirmedAt = session('standby_confirmed_at');

        // Intentar encontrar la última factura pendiente de ajuste (sin snapshots) del usuario (por compañía)
        $lastPendingInvoice = Invoice::whereHas('contract.supply.entity', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->whereDoesntHave('equipmentUsageSnapshots')
            ->orderByDesc('end_date')
            ->first();

        return view('standby.index', compact('categories', 'equipments', 'confirmedAt', 'lastPendingInvoice'));
    }

    /**
     * Actualiza el supports_standby de una categoría
     */
    public function updateCategory(Request $request, EquipmentCategory $category)
    {
        $validated = $request->validate([
            'supports_standby' => ['required', 'boolean']
        ]);

        $category->update($validated);

        return redirect()->route('standby.index')->with('success', "Categoría \"{$category->name}\" actualizada");
    }

    /**
     * Actualiza el has_standby_mode de varios equipos de golpe (bulk)
     */
    public function bulkUpdateEquipment(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'equipment_ids' => ['required', 'array'],
            'equipment_ids.*' => ['exists:entity_equipment,id'],
            'has_standby_mode' => ['required', 'boolean']
        ]);

        EntityEquipment::whereIn('id', $validated['equipment_ids'])
            ->whereHas('entity', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->update(['has_standby_mode' => $validated['has_standby_mode']]);

        $count = count($validated['equipment_ids']);
        return redirect()->route('standby.index')->with('success', "Actualizados {$count} equipos");
    }

    /**
     * Marca la configuración actual como confirmada (requerido antes de aplicar "Otras Recomendaciones").
     */
    public function confirm(Request $request)
    {
        Session::put('standby_confirmed_at', Carbon::now()->toDateTimeString());

        // Generar alerta de recomendaciones disponibles si todavía no se aplicaron
        $user = auth()->user();
        $entityIds = $user->company?->entities?->pluck('id') ?? collect();
        if ($entityIds->isNotEmpty()) {
            $firstEntityId = $entityIds->first();
            // Al confirmar, descartar alertas de nuevo equipo pendientes
            \App\Models\SmartAlert::whereIn('entity_id', $entityIds)
                ->where('type','standby_new_equipment')
                ->active()
                ->update(['is_dismissed' => true, 'dismissed_at' => now()]);
            $alreadyApplied = \App\Models\SmartAlert::where('type','standby_recommendation_available')
                ->where('entity_id',$firstEntityId)
                ->active()
                ->exists();
            if (!$alreadyApplied) {
                // Contar equipos elegibles (no continuos y de categorías clave)
                $eligibleCount = EntityEquipment::whereHas('entity', function($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->whereHas('equipmentType.equipmentCategory', function($q) {
                        $q->whereIn('id',[1,4,5,12]);
                    })
                    ->count();

                \App\Models\SmartAlert::create([
                    'entity_id' => $firstEntityId,
                    'invoice_id' => null,
                    'type' => 'standby_recommendation_available',
                    'severity' => 'info',
                    'title' => 'Recomendaciones de Standby disponibles',
                    'description' => "Hay recomendaciones pendientes para optimizar standby en {$eligibleCount} equipos. Revisá antes de aplicar cambios masivos.",
                    'data' => ['eligible_count' => $eligibleCount],
                ]);
            }
        }

        return redirect()->route('standby.index')->with('success', 'Configuración de standby confirmada. Ahora podés revisar recomendaciones disponibles.');
    }

    /**
     * Aplica recomendaciones globales: activar standby en todos los equipos salvo los de uso continuo (≈24h/día).
     * Además prepara el último período pendiente para que tome estos valores por defecto.
     */
    public function applyRecommendations(Request $request)
    {
        $user = auth()->user();
        if (!session()->has('standby_confirmed_at')) {
            return redirect()->route('standby.index')->with('warning', 'Primero debés confirmar la configuración de standby.');
        }

        // Traer todos los equipos de la compañía
        $equipments = EntityEquipment::whereHas('entity', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->with('equipmentType')
            ->get();

        $updatedOn = 0; $updatedOff = 0;

        DB::transaction(function () use ($equipments, &$updatedOn, &$updatedOff) {
            foreach ($equipments as $eq) {
                $type = $eq->equipmentType;
                $defaultMinutes = ($type?->default_avg_daily_use_hours ?? 0) * 60;
                $minutes = $eq->avg_daily_use_minutes_override ?? $defaultMinutes;

                // Uso continuo ≈ 24h (umbral 23h = 1380 minutos)
                $isContinuous = ($minutes >= 1380);

                $newStandby = $isContinuous ? false : true;
                if ($eq->has_standby_mode !== $newStandby) {
                    $eq->has_standby_mode = $newStandby;
                    $eq->saveQuietly();
                }
                if ($newStandby) { $updatedOn++; } else { $updatedOff++; }
            }
        });

        // Dismiss de la alerta de recomendaciones disponibles (si existiera)
        $entityIds = $user->company?->entities?->pluck('id') ?? collect();
        if ($entityIds->isNotEmpty()) {
            \App\Models\SmartAlert::whereIn('entity_id', $entityIds)
                ->where('type', 'standby_recommendation_available')
                ->active()
                ->update(['is_dismissed' => true, 'dismissed_at' => now()]);
        }

        // No creamos snapshots aquí: la pantalla de ajuste tomará estos valores por defecto
        // para el último período pendiente.

        return redirect()->route('standby.index')->with('success', "Recomendaciones aplicadas: {$updatedOn} con standby ON, {$updatedOff} en uso continuo (sin standby).");
    }
}
