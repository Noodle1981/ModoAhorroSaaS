<?php

namespace App\Services;

use App\Models\User;
use App\Models\EntityEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceLog;
use App\Models\Recommendation;
use Carbon\Carbon;

class RecommendationService
{
    public function generateMaintenanceRecommendations()
    {
        $users = User::all();

        foreach ($users as $user) {
            $userEquipmentIds = $user->company->entities()->with('entityEquipments')->get()->pluck('entityEquipments')->flatten()->pluck('id');
            $userEquipments = EntityEquipment::whereIn('id', $userEquipmentIds)->with('equipmentType')->get();
            $equipmentTypeIds = $userEquipments->pluck('equipmentType.id')->unique();
            $applicableTasks = MaintenanceTask::whereIn('equipment_type_id', $equipmentTypeIds)->get();
            $maintenanceLogs = MaintenanceLog::whereIn('entity_equipment_id', $userEquipmentIds)
                                             ->latest('performed_on_date')
                                             ->get();

            foreach ($userEquipments as $equipment) {
                $tasksForEquipment = $applicableTasks->where('equipment_type_id', $equipment->equipment_type_id);

                foreach ($tasksForEquipment as $task) {
                    $lastLog = $maintenanceLogs->where('entity_equipment_id', $equipment->id)
                                               ->where('maintenance_task_id', $task->id)
                                               ->first();

                    $isPending = true;
                    if ($lastLog) {
                        $daysSinceLast = Carbon::parse($lastLog->performed_on_date)->diffInDays(now());
                        if ($daysSinceLast <= $task->recommended_frequency_days) {
                            $isPending = false;
                        }
                    }

                    if ($isPending) {
                        $this->createRecommendationForOverdueTask($equipment, $task, $lastLog);
                    }
                }
            }
        }
    }

    private function createRecommendationForOverdueTask($equipment, $task, $lastLog)
    {
        $code = 'MAINTENANCE_'.strtoupper(str_replace(' ', '_', $task->name)).'_'.$equipment->id;
        $title = 'Mantenimiento pendiente para: ' . ($equipment->custom_name ?? $equipment->equipmentType->name);
        $lastPerformed = $lastLog ? $lastLog->performed_on_date->format('d/m/Y') : 'Nunca';
        $description = 'La tarea de mantenimiento "'.$task->name.'" está pendiente. Última vez realizada: '.$lastPerformed.'. Frecuencia recomendada: cada '.$task->recommended_frequency_days.' días.';

        Recommendation::updateOrCreate(
            ['code' => $code],
            [
                'title' => $title,
                'description' => $description,
                'applies_to_category_id' => $equipment->equipmentType->equipment_category_id,
                'trigger_rules' => json_encode([
                    'type' => 'overdue_maintenance',
                    'entity_equipment_id' => $equipment->id,
                    'maintenance_task_id' => $task->id,
                ]),
            ]
        );
    }
}
