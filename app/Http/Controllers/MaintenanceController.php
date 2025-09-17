<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceLogRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EntityEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceLog;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * (INDEX) Muestra el dashboard de mantenimiento.
     */
    public function index()
    {
        $user = Auth::user();
        $userEquipmentIds = $user->company->entities()->with('entityEquipment')->get()->pluck('entityEquipment')->flatten()->pluck('id');
        $userEquipments = EntityEquipment::whereIn('id', $userEquipmentIds)->with('equipmentType')->get();

        $equipmentTypeIds = $userEquipments->pluck('equipmentType.id')->unique();
        $applicableTasks = MaintenanceTask::whereIn('equipment_type_id', $equipmentTypeIds)->get();

        $maintenanceLogs = MaintenanceLog::whereIn('entity_equipment_id', $userEquipmentIds)
                                         ->with('entityEquipment.equipmentType', 'maintenanceTask')
                                         ->latest('performed_on_date')
                                         ->get();

        $pendingTasks = [];
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
                    $pendingTasks[] = [
                        'equipment' => $equipment,
                        'task' => $task,
                        'last_performed' => $lastLog ? $lastLog->performed_on_date->format('d/m/Y') : 'Nunca',
                    ];
                }
            }
        }

        return view('maintenance.index', compact('pendingTasks', 'maintenanceLogs', 'userEquipments', 'applicableTasks'));
    }

    /**
     * (STORE) Guarda un nuevo registro de mantenimiento.
     */
    public function store(StoreMaintenanceLogRequest $request)
    {
        $validated = $request->validated();
        $equipment = EntityEquipment::findOrFail($validated['entity_equipment_id']);

        $this->authorize('update', $equipment); // Usamos la policy de EntityEquipment

        MaintenanceLog::create($validated + ['verification_status' => 'user_reported']);

        return redirect()->route('maintenance.index')
                         ->with('success', 'Mantenimiento registrado exitosamente.');
    }
}