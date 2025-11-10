<?php

namespace App\Http\Controllers;

use App\Models\EntityEquipment;
use App\Models\EquipmentCategory;
use App\Models\SmartAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class UsageSettingsController extends Controller
{
    /**
     * Panel de Gestión de Uso: configurar días de uso por semana y opciones relacionadas
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Traer equipos del usuario con tipo y categoría
        $equipments = EntityEquipment::whereHas('entity', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->with('equipmentType.equipmentCategory', 'entity')
            ->orderBy('entity_id')
            ->get();

        $confirmedAt = session('usage_confirmed_at');

        // Período (invoice) opcional para contexto
        $invoiceId = $request->query('invoice');
        $invoice = null;
        if ($invoiceId) {
            $invoice = \App\Models\Invoice::with('contract.supply.entity')
                ->find($invoiceId);
            if ($invoice && $invoice->contract->supply->entity->company_id !== $user->company_id) {
                $invoice = null; // Seguridad: no mostrar períodos ajenos
            }
        }

        return view('usage.index', compact('equipments', 'confirmedAt', 'invoice'));
    }

    /**
     * Actualización masiva de frecuencia de uso
     */
    public function bulkUpdate(Request $request)
    {
        $user = auth()->user();

        // Normalizar inputs: convertir '' o 'null' a null y forzar booleanos
        $normalizedItems = collect($request->input('items', []))
            ->map(function ($item) {
                $item = is_array($item) ? $item : [];
                $item['id'] = $item['id'] ?? null;
                // Booleano a 0/1
                $item['is_daily_use'] = isset($item['is_daily_use']) ? (int)!!$item['is_daily_use'] : null;
                // Normalizar días por semana
                if (!isset($item['usage_days_per_week']) || $item['usage_days_per_week'] === '' || $item['usage_days_per_week'] === 'null') {
                    $item['usage_days_per_week'] = null;
                }
                return $item;
            })
            ->values()
            ->all();

        $request->merge(['items' => $normalizedItems]);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:entity_equipment,id'],
            'items.*.is_daily_use' => ['nullable', 'boolean'],
            'items.*.usage_days_per_week' => ['nullable', 'integer', 'min:1', 'max:7'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            foreach ($validated['items'] as $item) {
                $isDaily = array_key_exists('is_daily_use', $item) ? (bool)$item['is_daily_use'] : null;
                $days = $item['usage_days_per_week'] ?? null;
                if ($isDaily === true) {
                    $days = null; // Si es diario, no guardamos días
                }

                EntityEquipment::where('id', $item['id'])
                    ->whereHas('entity', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->update([
                        'is_daily_use' => $isDaily,
                        'usage_days_per_week' => $days,
                    ]);
            }
        });

        $invoiceId = $request->input('invoice');
        return redirect()->route('usage.index', $invoiceId ? ['invoice' => $invoiceId] : [])
            ->with('success', 'Frecuencia de uso actualizada.');
    }

    /**
     * Confirmar la configuración actual (habilita recomendaciones y gating).
     */
    public function confirm(Request $request)
    {
        Session::put('usage_confirmed_at', Carbon::now()->toDateTimeString());

        $user = auth()->user();
        $entityIds = $user->company?->entities?->pluck('id') ?? collect();
        if ($entityIds->isNotEmpty()) {
            $firstEntityId = $entityIds->first();

            // Descartar alerta de pending si existiera
            SmartAlert::whereIn('entity_id', $entityIds)
                ->where('type', 'usage_pending')
                ->active()
                ->update(['is_dismissed' => true, 'dismissed_at' => now()]);

            // Crear alerta de recomendaciones disponibles si no existe
            $exists = SmartAlert::where('type', 'usage_recommendation_available')
                ->where('entity_id', $firstEntityId)
                ->active()
                ->exists();
            if (!$exists) {
                $eligibleCount = EntityEquipment::whereHas('entity', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                    ->count();

                SmartAlert::create([
                    'entity_id' => $firstEntityId,
                    'invoice_id' => null,
                    'type' => 'usage_recommendation_available',
                    'severity' => 'info',
                    'title' => 'Recomendaciones de Uso disponibles',
                    'description' => "Hay recomendaciones pendientes para ajustar días/semana en {$eligibleCount} equipos.",
                    'data' => ['eligible_count' => $eligibleCount],
                ]);
            }
        }

        return redirect()->route('usage.index')->with('success', 'Gestión de uso confirmada.');
    }

    /**
     * Recomendaciones heurísticas de días/semana.
     * Devuelve JSON con sugerencias por equipo.
     */
    public function recommendations()
    {
        $user = auth()->user();
        $equipments = EntityEquipment::whereHas('entity', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->with('equipmentType.equipmentCategory')
            ->get();

        $result = [];
        foreach ($equipments as $eq) {
            $type = $eq->equipmentType;
            $power = $eq->power_watts_override ?? $type?->default_power_watts ?? 0;
            $defaultMinutes = $eq->avg_daily_use_minutes_override ?? (($type?->default_avg_daily_use_hours ?? 0) * 60);

            $suggest = $this->suggestDaysPerWeek($defaultMinutes, $power, optional($type->equipmentCategory)->name);

            $result[] = [
                'id' => $eq->id,
                'name' => $eq->custom_name ?? ($type?->name ?? 'Equipo'),
                'category' => optional($type->equipmentCategory)->name,
                'current' => [
                    'is_daily_use' => (bool)($eq->is_daily_use ?? false),
                    'usage_days_per_week' => $eq->usage_days_per_week,
                ],
                'suggested' => $suggest,
                'reason' => $suggest['reason'],
            ];
        }

        return response()->json(['status' => 'ok', 'equipments' => $result]);
    }

    /**
     * Aplicar recomendaciones en lote.
     */
    public function applyRecommendations(Request $request)
    {
        $user = auth()->user();
        $equipments = EntityEquipment::whereHas('entity', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->with('equipmentType.equipmentCategory')
            ->get();

        $updated = 0;
        DB::transaction(function () use (&$updated, $equipments) {
            foreach ($equipments as $eq) {
                $type = $eq->equipmentType;
                $power = $eq->power_watts_override ?? $type?->default_power_watts ?? 0;
                $defaultMinutes = $eq->avg_daily_use_minutes_override ?? (($type?->default_avg_daily_use_hours ?? 0) * 60);
                $suggest = $this->suggestDaysPerWeek($defaultMinutes, $power, optional($type->equipmentCategory)->name);

                $changed = false;
                if (($eq->is_daily_use ?? false) !== $suggest['is_daily_use']) { $changed = true; }
                if (!$suggest['is_daily_use'] && (int)($eq->usage_days_per_week ?? 0) !== (int)$suggest['usage_days_per_week']) { $changed = true; }

                if ($changed) {
                    $eq->is_daily_use = $suggest['is_daily_use'];
                    $eq->usage_days_per_week = $suggest['is_daily_use'] ? null : $suggest['usage_days_per_week'];
                    $eq->saveQuietly();
                    $updated++;
                }
            }
        });

        // Dismiss de la alerta de recomendaciones de uso
        $entityIds = $user->company?->entities?->pluck('id') ?? collect();
        if ($entityIds->isNotEmpty()) {
            SmartAlert::whereIn('entity_id', $entityIds)
                ->where('type', 'usage_recommendation_available')
                ->active()
                ->update(['is_dismissed' => true, 'dismissed_at' => now()]);
        }

        return redirect()->route('usage.index')->with('success', "Recomendaciones de uso aplicadas a {$updated} equipos.");
    }

    private function suggestDaysPerWeek(int $defaultMinutes, int $power, ?string $categoryName): array
    {
        // Reglas simples y robustas a nombres de categorías
        $isContinuous = $defaultMinutes >= 720; // 12h+
        if ($isContinuous) {
            return ['is_daily_use' => true, 'usage_days_per_week' => null, 'reason' => 'Uso continuo o diario'];
        }

        // Heurística por patrón
        if ($categoryName) {
            $cat = mb_strtolower($categoryName);
            if (str_contains($cat, 'lavar') || str_contains($cat, 'lavado') || str_contains($cat, 'ropa')) {
                return ['is_daily_use' => false, 'usage_days_per_week' => 3, 'reason' => 'Electrodoméstico cíclico típico (3/sem)'];
            }
            if (str_contains($cat, 'cocina') || str_contains($cat, 'alimento')) {
                return ['is_daily_use' => false, 'usage_days_per_week' => 5, 'reason' => 'Uso frecuente pero no diario (5/sem)'];
            }
            if (str_contains($cat, 'entreten') || str_contains($cat, 'tv') || str_contains($cat, 'audio')) {
                return ['is_daily_use' => true, 'usage_days_per_week' => null, 'reason' => 'Suele usarse a diario'];
            }
            if (str_contains($cat, 'cuidado') || str_contains($cat, 'afeit') || str_contains($cat, 'plancha')) {
                return ['is_daily_use' => false, 'usage_days_per_week' => 2, 'reason' => 'Uso esporádico típico (2/sem)'];
            }
        }

        // Heurística genérica por minutos y potencia
        if ($defaultMinutes >= 180 || $power >= 1200) {
            return ['is_daily_use' => false, 'usage_days_per_week' => 4, 'reason' => 'Uso intenso pero no diario (4/sem)'];
        }
        if ($defaultMinutes <= 30 && $power <= 300) {
            return ['is_daily_use' => false, 'usage_days_per_week' => 2, 'reason' => 'Uso corto y liviano (2/sem)'];
        }

        return ['is_daily_use' => false, 'usage_days_per_week' => 5, 'reason' => 'Uso regular (5/sem)'];
    }
}
