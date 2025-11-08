<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentUsageSnapshot;
use App\Models\SnapshotChangeAlert;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SnapshotController extends Controller
{
    /**
     * Mostrar alertas de snapshots invalidados con detalles de cambios
     */
    public function reviewChanges(Entity $entity)
    {
        // Alertas pendientes para esta entidad
        $pendingAlerts = SnapshotChangeAlert::where('entity_id', $entity->id)
            ->where('status', 'pending')
            ->with(['equipment.equipmentType', 'historyRecord'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Snapshots invalidados agrupados por período
        $invalidatedSnapshots = EquipmentUsageSnapshot::whereIn('status', ['invalidated', 'draft'])
            ->whereHas('equipment', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->with(['equipment.equipmentType'])
            ->get()
            ->groupBy('snapshot_date');

        // Historial de recálculos recientes (snapshots con al menos 1 recálculo)
        $recentRecalculations = EquipmentUsageSnapshot::where('recalculation_count', '>', 0)
            ->whereHas('equipment', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->where('updated_at', '>=', now()->subDays(30))
            ->with(['equipment.equipmentType'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('snapshots.review-changes', compact(
            'entity',
            'pendingAlerts',
            'invalidatedSnapshots',
            'recentRecalculations'
        ));
    }

    /**
     * Recalcular un snapshot individual
     */
    public function recalculate(Request $request, EquipmentUsageSnapshot $snapshot)
    {
        $equipment = $snapshot->equipment;
        $entity = $equipment->entity;

        // Obtener valores actuales del equipo
        $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
        $usage = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;

        // Calcular días en el período (con prorrateo si el equipo es nuevo)
        $daysInPeriod = $this->calculateDaysInPeriod($equipment, $snapshot->snapshot_date);

        // Recalcular consumo
        $dailyKwh = ($power / 1000) * ($usage / 60);
        $periodKwh = $dailyKwh * $daysInPeriod;

        // Actualizar snapshot
        $snapshot->update([
            'power_watts' => $power,
            'avg_daily_use_minutes' => $usage,
            'calculated_daily_kwh' => round($dailyKwh, 4),
            'calculated_period_kwh' => round($periodKwh, 4),
            'status' => 'confirmed', // Confirmar automáticamente tras recalcular
            'recalculation_count' => $snapshot->recalculation_count + 1,
            'invalidated_at' => null,
            'invalidation_reason' => null,
        ]);

        // Marcar alertas como resueltas
        SnapshotChangeAlert::where('entity_id', $entity->id)
            ->whereJsonContains('affected_snapshots', $snapshot->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        return back()->with('success', sprintf(
            'Snapshot recalculado. Nuevo consumo: %.2f kWh (%d días)',
            $periodKwh,
            $daysInPeriod
        ));
    }

    /**
     * Recalcular todos los snapshots de un período
     */
    public function recalculatePeriod(Request $request, Entity $entity, string $date)
    {
        $snapshotDate = Carbon::parse($date);

        $snapshots = EquipmentUsageSnapshot::whereIn('status', ['invalidated', 'draft'])
            ->whereHas('equipment', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->where('snapshot_date', $snapshotDate->format('Y-m-d'))
            ->get();

        if ($snapshots->isEmpty()) {
            return back()->with('warning', 'No hay snapshots para recalcular en este período');
        }

        $recalculated = 0;
        foreach ($snapshots as $snapshot) {
            $equipment = $snapshot->equipment;

            // Obtener valores actuales
            $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
            $usage = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;

            // Calcular días con prorrateo
            $daysInPeriod = $this->calculateDaysInPeriod($equipment, $snapshot->snapshot_date);

            // Recalcular
            $dailyKwh = ($power / 1000) * ($usage / 60);
            $periodKwh = $dailyKwh * $daysInPeriod;

            $snapshot->update([
                'power_watts' => $power,
                'avg_daily_use_minutes' => $usage,
                'calculated_daily_kwh' => round($dailyKwh, 4),
                'calculated_period_kwh' => round($periodKwh, 4),
                'status' => 'confirmed', // Confirmar automáticamente tras recalcular
                'recalculation_count' => $snapshot->recalculation_count + 1,
                'invalidated_at' => null,
                'invalidation_reason' => null,
            ]);

            $recalculated++;
        }

        // Marcar alertas como resueltas
        SnapshotChangeAlert::where('entity_id', $entity->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        return back()->with('success', sprintf(
            'Se recalcularon %d snapshots del período %s',
            $recalculated,
            $snapshotDate->format('m/Y')
        ));
    }

    /**
     * Recalcular TODOS los snapshots invalidados de la entidad
     */
    public function recalculateAll(Entity $entity)
    {
        $snapshots = EquipmentUsageSnapshot::whereIn('status', ['invalidated', 'draft'])
            ->whereHas('equipment', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->get();

        if ($snapshots->isEmpty()) {
            return back()->with('warning', 'No hay snapshots invalidados para recalcular');
        }

        $recalculated = 0;
        $periodsAffected = [];

        foreach ($snapshots as $snapshot) {
            $equipment = $snapshot->equipment;

            $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
            $usage = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;
            $daysInPeriod = $this->calculateDaysInPeriod($equipment, $snapshot->snapshot_date);

            $dailyKwh = ($power / 1000) * ($usage / 60);
            $periodKwh = $dailyKwh * $daysInPeriod;

            $snapshot->update([
                'power_watts' => $power,
                'avg_daily_use_minutes' => $usage,
                'calculated_daily_kwh' => round($dailyKwh, 4),
                'calculated_period_kwh' => round($periodKwh, 4),
                'status' => 'confirmed', // Confirmar automáticamente tras recalcular
                'recalculation_count' => $snapshot->recalculation_count + 1,
                'invalidated_at' => null,
                'invalidation_reason' => null,
            ]);

            $periodsAffected[] = Carbon::parse($snapshot->snapshot_date)->format('m/Y');
            $recalculated++;
        }

        // Marcar alertas como resueltas
        SnapshotChangeAlert::where('entity_id', $entity->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        $uniquePeriods = count(array_unique($periodsAffected));

        return redirect()
            ->route('entities.show', $entity)
            ->with('success', sprintf(
                '✅ Se recalcularon %d snapshots en %d períodos diferentes',
                $recalculated,
                $uniquePeriods
            ));
    }

    /**
     * Calcular días en el período con prorrateo si el equipo es nuevo
     */
    private function calculateDaysInPeriod(EntityEquipment $equipment, string $snapshotDate): int
    {
        $periodDate = Carbon::parse($snapshotDate);
        $periodStart = $periodDate->copy()->startOfMonth();
        $periodEnd = $periodDate->copy()->endOfMonth();

        // Si el equipo tiene fecha de activación
        if ($equipment->activated_at) {
            $activatedAt = Carbon::parse($equipment->activated_at);

            // Si se activó DESPUÉS del inicio del período
            if ($activatedAt->greaterThan($periodStart)) {
                // Si se activó DENTRO del período
                if ($activatedAt->lessThanOrEqualTo($periodEnd)) {
                    // Calcular días desde activación hasta fin de período
                    return $activatedAt->diffInDays($periodEnd) + 1;
                } else {
                    // Se activó después del período completo = 0 días
                    return 0;
                }
            }
        }

        // Si el equipo fue dado de baja
        if ($equipment->deleted_at) {
            $deletedAt = Carbon::parse($equipment->deleted_at);

            // Si se dio de baja ANTES del fin del período
            if ($deletedAt->lessThan($periodEnd)) {
                // Si se dio de baja DENTRO del período
                if ($deletedAt->greaterThanOrEqualTo($periodStart)) {
                    // Calcular días desde inicio hasta baja
                    return $periodStart->diffInDays($deletedAt) + 1;
                } else {
                    // Se dio de baja antes del período = 0 días
                    return 0;
                }
            }
        }

        // Por defecto: todo el mes
        return $periodDate->daysInMonth;
    }

    /**
     * Confirmar snapshot (cambiar de draft a confirmed)
     */
    public function confirm(EquipmentUsageSnapshot $snapshot)
    {
        $snapshot->update(['status' => 'confirmed']);

        return back()->with('success', 'Snapshot confirmado');
    }

    /**
     * Confirmar todos los snapshots de un período
     */
    public function confirmPeriod(Entity $entity, string $date)
    {
        $snapshotDate = Carbon::parse($date);

        $updated = EquipmentUsageSnapshot::where('status', 'draft')
            ->whereHas('equipment', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->where('snapshot_date', $snapshotDate->format('Y-m-d'))
            ->update(['status' => 'confirmed']);

        return back()->with('success', sprintf(
            'Se confirmaron %d snapshots del período %s',
            $updated,
            $snapshotDate->format('m/Y')
        ));
    }
}
