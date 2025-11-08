<?php

namespace App\Observers;

use App\Models\EntityEquipment;
use App\Models\EquipmentHistory;
use App\Models\EquipmentUsageSnapshot;
use App\Models\SnapshotChangeAlert;
use Illuminate\Support\Facades\Auth;

class EntityEquipmentObserver
{
    /**
     * Al crear un nuevo equipo
     */
    public function created(EntityEquipment $equipment): void
    {
        // Registrar en historial
        EquipmentHistory::create([
            'entity_equipment_id' => $equipment->id,
            'company_id' => $equipment->entity->company_id,
            'change_type' => 'activated',
            'before_values' => null,
            'after_values' => $equipment->only([
                'equipment_type_id',
                'power_watts_override',
                'avg_daily_use_minutes_override',
                'quantity',
                'custom_name',
                'activated_at',
            ]),
            'change_description' => sprintf(
                'Equipo "%s" agregado a %s',
                $equipment->custom_name ?? $equipment->equipmentType->name,
                $equipment->entity->name
            ),
            'changed_by_user_id' => Auth::id(),
        ]);

        // Si hay snapshots confirmados, invalidarlos
        $this->invalidateExistingSnapshots($equipment, 'equipment_added');
    }

    /**
     * Al actualizar un equipo existente
     */
    public function updating(EntityEquipment $equipment): void
    {
        // Detectar cambios críticos
        $criticalChanges = [];

        // Cambio de potencia
        if ($equipment->isDirty('power_watts_override')) {
            $criticalChanges[] = 'power_changed';
            $equipment->power_last_changed_at = now();
        }

        // Cambio de frecuencia / uso
        $frequencyFields = [
            'avg_daily_use_minutes_override', 'is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session'
        ];
        $frequencyDirty = false;
        foreach ($frequencyFields as $f) {
            if ($equipment->isDirty($f)) { $frequencyDirty = true; break; }
        }
        if ($frequencyDirty) {
            $criticalChanges[] = 'frequency_changed';
            $equipment->usage_last_changed_at = now();
        }

        // Cambio de tipo de equipo (esto cambia TODA la categoría)
        if ($equipment->isDirty('equipment_type_id')) {
            $criticalChanges[] = 'type_changed';
        }

        // Si hay cambios críticos, registrar en historial
        if (!empty($criticalChanges)) {
            $this->recordChange($equipment, $criticalChanges);
        }
    }

    /**
     * Después de actualizar (para invalidar snapshots con datos ya guardados)
     */
    public function updated(EntityEquipment $equipment): void
    {
        // Si hubo cambios críticos, invalidar snapshots
        if ($equipment->wasChanged(['power_watts_override', 'avg_daily_use_minutes_override', 'equipment_type_id', 'is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session'])) {
            $changeType = $this->determineChangeType($equipment);
            $this->invalidateExistingSnapshots($equipment, $changeType);
        }
    }

    /**
     * Al eliminar (soft delete)
     */
    public function deleted(EntityEquipment $equipment): void
    {
        // Registrar en historial
        EquipmentHistory::create([
            'entity_equipment_id' => $equipment->id,
            'company_id' => $equipment->entity->company_id,
            'change_type' => 'deleted',
            'before_values' => $equipment->only([
                'equipment_type_id',
                'power_watts_override',
                'avg_daily_use_minutes_override',
                'quantity',
                'custom_name',
            ]),
            'after_values' => ['deleted_at' => now()],
            'change_description' => sprintf(
                'Equipo "%s" dado de baja',
                $equipment->custom_name ?? $equipment->equipmentType->name
            ),
            'changed_by_user_id' => Auth::id(),
        ]);

        // Marcar snapshots como "equipo eliminado"
        EquipmentUsageSnapshot::where('entity_equipment_id', $equipment->id)
            ->update(['is_equipment_deleted' => true]);

        // Invalidar snapshots confirmados
        $this->invalidateExistingSnapshots($equipment, 'equipment_deleted');
    }

    /**
     * Al forzar eliminación (hard delete) - SOLO si usuario confirma que fue error
     */
    public function forceDeleted(EntityEquipment $equipment): void
    {
        // Los snapshots se eliminan en cascada (configurar en migración si hace falta)
        // El historial se mantiene para auditoría
    }

    // ========== MÉTODOS AUXILIARES ==========

    private function recordChange(EntityEquipment $equipment, array $changeTypes): void
    {
        $before = $equipment->getOriginal();
        $after = $equipment->getAttributes();

        EquipmentHistory::create([
            'entity_equipment_id' => $equipment->id,
            'company_id' => $equipment->entity->company_id,
            'change_type' => implode(',', $changeTypes),
            'before_values' => [
                'equipment_type_id' => $before['equipment_type_id'] ?? null,
                'power_watts' => $before['power_watts_override'] ?? null,
                'avg_daily_use_minutes' => $before['avg_daily_use_minutes_override'] ?? null,
            ],
            'after_values' => [
                'equipment_type_id' => $after['equipment_type_id'] ?? null,
                'power_watts' => $after['power_watts_override'] ?? null,
                'avg_daily_use_minutes' => $after['avg_daily_use_minutes_override'] ?? null,
            ],
            'change_description' => $this->buildChangeDescription($before, $after, $changeTypes),
            'changed_by_user_id' => Auth::id(),
        ]);
    }

    private function invalidateExistingSnapshots(EntityEquipment $equipment, string $changeType): void
    {
        // Buscar snapshots confirmados de este equipo
        $confirmedSnapshots = EquipmentUsageSnapshot::where('entity_equipment_id', $equipment->id)
            ->where('status', 'confirmed')
            ->get();

        if ($confirmedSnapshots->isEmpty()) {
            return; // No hay nada que invalidar
        }

        // Invalidar todos
        $snapshotIds = $confirmedSnapshots->pluck('id')->toArray();
        
        EquipmentUsageSnapshot::whereIn('id', $snapshotIds)->update([
            'status' => 'invalidated',
            'invalidated_at' => now(),
            'invalidation_reason' => $this->getInvalidationReason($changeType, $equipment),
        ]);

        // Crear alerta para el usuario
        $lastHistory = $equipment->historyRecords()->latest()->first();

        SnapshotChangeAlert::create([
            'entity_id' => $equipment->entity_id,
            'company_id' => $equipment->entity->company_id,
            'entity_equipment_id' => $equipment->id,
            'equipment_history_id' => $lastHistory?->id,
            'alert_type' => $changeType,
            'message' => $this->getAlertMessage($changeType, $equipment, count($snapshotIds)),
            'affected_snapshots' => $snapshotIds,
            'status' => 'pending',
        ]);
    }

    private function determineChangeType(EntityEquipment $equipment): string
    {
        if ($equipment->wasChanged('power_watts_override')) {
            return 'power_changed';
        }
        if ($equipment->wasChanged(['avg_daily_use_minutes_override','is_daily_use','usage_days_per_week','usage_weekdays','minutes_per_session'])) {
            return 'frequency_changed';
        }
        if ($equipment->wasChanged('equipment_type_id')) {
            return 'type_changed';
        }
        return 'equipment_modified';
    }

    private function buildChangeDescription(array $before, array $after, array $changeTypes): string
    {
        $parts = [];

        if (in_array('power_changed', $changeTypes)) {
            $parts[] = sprintf('Potencia: %dW → %dW', $before['power_watts_override'] ?? 0, $after['power_watts_override'] ?? 0);
        }

        if (in_array('frequency_changed', $changeTypes)) {
            $parts[] = sprintf('Frecuencia/Uso: %d min/día → %d min/día', $before['avg_daily_use_minutes_override'] ?? 0, $after['avg_daily_use_minutes_override'] ?? 0);
            if (($before['usage_days_per_week'] ?? null) !== ($after['usage_days_per_week'] ?? null)) {
                $parts[] = sprintf('Días/semana: %s → %s', $before['usage_days_per_week'] ?? '-', $after['usage_days_per_week'] ?? '-');
            }
        }

        if (in_array('type_changed', $changeTypes)) {
            $parts[] = 'Tipo de equipo modificado';
        }

        return implode(', ', $parts);
    }

    private function getInvalidationReason(string $changeType, EntityEquipment $equipment): string
    {
        $equipmentName = $equipment->custom_name ?? $equipment->equipmentType->name;

        return match ($changeType) {
            'power_changed' => "La potencia de '{$equipmentName}' fue modificada",
            'frequency_changed' => "La frecuencia/uso de '{$equipmentName}' fue modificada",
            'type_changed' => "El tipo de '{$equipmentName}' fue cambiado",
            'equipment_added' => "Se agregó el equipo '{$equipmentName}'",
            'equipment_deleted' => "El equipo '{$equipmentName}' fue dado de baja",
            default => "El equipo '{$equipmentName}' fue modificado",
        };
    }

    private function getAlertMessage(string $changeType, EntityEquipment $equipment, int $affectedCount): string
    {
        $equipmentName = $equipment->custom_name ?? $equipment->equipmentType->name;

        return match ($changeType) {
            'power_changed' => "⚠️ Cambiaste la potencia de '{$equipmentName}'. Se invalidaron {$affectedCount} períodos históricos. Debes recalcularlos.",
            'frequency_changed' => "⚠️ Modificaste la frecuencia/uso de '{$equipmentName}'. Se invalidaron {$affectedCount} períodos históricos. Debes recalcularlos.",
            'type_changed' => "⚠️ Cambiaste el tipo de '{$equipmentName}'. Se invalidaron {$affectedCount} períodos históricos. Debes recalcularlos.",
            'equipment_added' => "✅ Agregaste '{$equipmentName}'. Hay {$affectedCount} períodos históricos que debes recalcular para incluir este equipo.",
            'equipment_deleted' => "❌ Diste de baja '{$equipmentName}'. Hay {$affectedCount} períodos históricos que debes recalcular.",
            default => "⚠️ Modificaste '{$equipmentName}'. Se invalidaron {$affectedCount} períodos históricos.",
        };
    }
}
