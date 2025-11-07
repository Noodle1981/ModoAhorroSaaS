<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceLogRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EntityEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceLog;
use App\Models\SmartAlert;
use App\Services\MaintenanceSchedulerService;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * (INDEX) Muestra el dashboard de mantenimiento.
     */
    public function index(MaintenanceSchedulerService $scheduler)
    {
        $user = Auth::user();
        
        // Obtener todos los equipos de las entidades del usuario
        $userEquipments = EntityEquipment::whereHas('entity', function($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->with(['equipmentType', 'entity'])
            ->get();

        $equipmentTypeIds = $userEquipments->pluck('equipment_type_id')->unique();
        $applicableTasks = MaintenanceTask::whereIn('equipment_type_id', $equipmentTypeIds)->get();

        $userEquipmentIds = $userEquipments->pluck('id');
        $maintenanceLogs = MaintenanceLog::whereIn('entity_equipment_id', $userEquipmentIds)
                                         ->with(['entityEquipment.equipmentType', 'maintenanceTask'])
                                         ->latest('performed_on_date')
                                         ->get();

        $pendingTasks = [];
        $upcomingTasks = [];
        $overdueTasks = [];
        foreach ($userEquipments as $equipment) {
            $tasksForEquipment = $applicableTasks->where('equipment_type_id', $equipment->equipment_type_id);
            
            foreach ($tasksForEquipment as $task) {
                $lastLog = $maintenanceLogs->where('entity_equipment_id', $equipment->id)
                                           ->where('maintenance_task_id', $task->id)
                                           ->first();
                
                $context = [
                    'usage_hours_per_day' => $equipment->avg_daily_use_minutes_override ? ($equipment->avg_daily_use_minutes_override/60) : ($equipment->equipmentType->default_avg_daily_use_hours ?? null),
                ];
                $interval = $task->resolveEffectiveIntervalDays($context);
                $lastDate = $lastLog?->performed_on_date ?? $equipment->created_at;
                if (!$lastDate instanceof \Carbon\Carbon) {
                    $lastDate = \Carbon\Carbon::parse($lastDate);
                }
                $dueDate = $lastDate->copy()->addDays($interval);
                $daysLeft = now()->diffInDays($dueDate, false);
                if ($daysLeft < 0) {
                    $overdueTasks[] = compact('equipment','task','lastLog','dueDate','daysLeft');
                } elseif ($daysLeft <= 3) {
                    $pendingTasks[] = compact('equipment','task','lastLog','dueDate','daysLeft');
                } elseif ($daysLeft <= 14) {
                    $upcomingTasks[] = compact('equipment','task','lastLog','dueDate','daysLeft');
                }
            }
        }
        // Opcional: escaneo rápido para generar alertas inmediatas (no masivo)
        foreach ($user->company?->entities ?? [] as $entity) {
            $scheduler->scanEntity($entity); // idempotente por duplicado controlado en servicio
        }

        return view('maintenance.index', [
            'pendingTasks' => $pendingTasks,
            'upcomingTasks' => $upcomingTasks,
            'overdueTasks' => $overdueTasks,
            'maintenanceLogs' => $maintenanceLogs,
            'userEquipments' => $userEquipments,
            'applicableTasks' => $applicableTasks,
        ]);
    }

    /**
     * (STORE) Guarda un nuevo registro de mantenimiento.
     */
    public function store(StoreMaintenanceLogRequest $request)
    {
        $validated = $request->validated();
        $equipment = EntityEquipment::findOrFail($validated['entity_equipment_id']);

        $this->authorize('update', $equipment); // Usamos la policy de EntityEquipment

        $log = MaintenanceLog::create($validated + [
            'verification_status' => 'user_reported',
            'action_type' => $validated['action_type'] ?? null,
        ]);

        // Cerrar alerta asociada si existe
        if (isset($validated['smart_alert_id'])) {
            $alert = SmartAlert::find($validated['smart_alert_id']);
            if ($alert && !$alert->is_dismissed) {
                $alert->update([
                    'is_dismissed' => true,
                    'dismissed_at' => now(),
                    'action_taken_at' => now(),
                    'is_read' => true,
                ]);
            }
        }

        return redirect()->route('maintenance.index')
                         ->with('success', 'Mantenimiento registrado exitosamente.');
    }

    /** Marca tarea de mantenimiento como realizada desde alerta (flujo rápido). */
    public function completeFromAlert(Request $request, SmartAlert $alert)
    {
        $data = $alert->data ?? [];
        if (!isset($data['entity_equipment_id'],$data['maintenance_task_id'])) {
            return redirect()->back()->with('success','Alerta sin datos suficientes.');
        }
        $request->merge([
            'entity_equipment_id' => $data['entity_equipment_id'],
            'maintenance_task_id' => $data['maintenance_task_id'],
            'performed_on_date' => now()->toDateString(),
            'notes' => 'Registro rápido desde alerta',
            'action_type' => $data['maintenance_type'] ?? null,
            'smart_alert_id' => $alert->id,
        ]);

        // Validación mínima manual si Request Form no cubre
        MaintenanceLog::create([
            'entity_equipment_id' => $data['entity_equipment_id'],
            'maintenance_task_id' => $data['maintenance_task_id'],
            'performed_on_date' => now()->toDateString(),
            'verification_status' => 'user_reported',
            'action_type' => $data['maintenance_type'] ?? null,
            'notes' => 'Registro rápido desde alerta',
        ]);
        $alert->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
            'action_taken_at' => now(),
            'is_read' => true,
        ]);
        return redirect()->back()->with('success','Mantenimiento registrado y alerta cerrada.');
    }
}