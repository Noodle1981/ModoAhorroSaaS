<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceLog;
use App\Models\SmartAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MaintenanceSchedulerService
{
    /**
     * Escanea las tareas aplicables y genera alertas de mantenimiento próximas o vencidas.
     * Regresa conteos básicos.
     */
    public function scanEntity(Entity $entity): array
    {
        $equipments = $entity->equipments()->with('equipmentType')->get();
        $equipmentTypeIds = $equipments->pluck('equipment_type_id')->unique();
        $tasks = MaintenanceTask::whereIn('equipment_type_id', $equipmentTypeIds)->get();

        $newAlerts = 0;
        foreach ($equipments as $eq) {
            foreach ($tasks->where('equipment_type_id', $eq->equipment_type_id) as $task) {
                // Buscar último mantenimiento registrado para este equipo/tarea
                $last = MaintenanceLog::where('entity_equipment_id', $eq->id)
                    ->where('maintenance_task_id', $task->id)
                    ->orderByDesc('performed_on_date')
                    ->first();

                $context = [
                    'usage_hours_per_day' => $eq->avg_daily_use_minutes_override ? ($eq->avg_daily_use_minutes_override/60) : ($eq->equipmentType->default_avg_daily_use_hours ?? null),
                ];
                $intervalDays = $task->resolveEffectiveIntervalDays($context);

                $rawLast = $last?->performed_on_date ?? $eq->created_at;
                // Asegurar instancia Carbon
                if (!$rawLast instanceof \Carbon\Carbon) {
                    $rawLast = \Carbon\Carbon::parse($rawLast);
                }
                $dueDate = $rawLast->copy()->addDays($intervalDays);
                $daysLeft = now()->diffInDays($dueDate, false);

                // Generar alerta si faltan <= 3 días o ya está vencido
                if ($daysLeft <= 3) {
                    // Evitar duplicados: misma entidad/equipo/tarea dentro del mismo ciclo
                    $exists = SmartAlert::where('entity_id', $entity->id)
                        ->where('type', 'maintenance_due')
                        ->where('is_dismissed', false)
                        ->where('data->entity_equipment_id', $eq->id)
                        ->where('data->maintenance_task_id', $task->id)
                        ->whereDate('created_at', '>=', now()->subDays($intervalDays))
                        ->exists();

                    if (!$exists) {
                        SmartAlert::create([
                            'entity_id' => $entity->id,
                            'type' => 'maintenance_due',
                            'severity' => $daysLeft < 0 ? 'warning' : 'info',
                            'title' => $this->buildTitle($eq, $task, $daysLeft),
                            'description' => $this->buildDescription($eq, $task, $lastDate, $dueDate, $daysLeft),
                            'data' => [
                                'entity_equipment_id' => $eq->id,
                                'maintenance_task_id' => $task->id,
                                'due_date' => $dueDate->toDateString(),
                                'days_left' => $daysLeft,
                                'maintenance_type' => $task->maintenance_type,
                            ],
                        ]);
                        $newAlerts++;
                    }
                }
            }
        }

        return [
            'new_alerts' => $newAlerts,
        ];
    }

    private function buildTitle($eq, $task, int $daysLeft): string
    {
        $equipName = $eq->custom_name ?? $eq->equipmentType->name;
        $taskName = $task->name ?? ($task->task_name ?? 'Mantenimiento');
        $prefix = $daysLeft < 0 ? 'Vencido' : 'Próximo';
        return "$prefix: $taskName en $equipName";
    }

    private function buildDescription($eq, $task, $lastDate, $dueDate, int $daysLeft): string
    {
        $loc = $eq->entity?->name;
        $taskName = $task->name ?? ($task->task_name ?? 'Mantenimiento');
        $lastTxt = $lastDate ? $lastDate->format('d/m/Y') : 'Nunca';
        $dueTxt = $dueDate->format('d/m/Y');
        $when = $daysLeft < 0 ? (abs($daysLeft)." días de atraso") : ($daysLeft." días");
    $equipName = $eq->custom_name ? $eq->custom_name : ($eq->equipmentType->name ?? 'Equipo');
    return "$taskName para $equipName en $loc. Último: $lastTxt. Próximo: $dueTxt (en $when).";
    }
}
