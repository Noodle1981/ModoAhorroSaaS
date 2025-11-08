<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentType;
use App\Services\ScheduleOptimizationService;
use Illuminate\Http\Request;

class ScheduleOptimizationController extends Controller
{
    public function index(Request $request, ScheduleOptimizationService $service)
    {
        $user = $request->user();
        $entities = $user->company->entities()->with(['equipments.equipmentType'])->get();

        // Disponibilidad de módulos según inventario actual
        $allEquipments = $entities->pluck('equipments')->flatten();
        $names = $allEquipments->map(function ($eq) {
            $name = $eq->custom_name ?? ($eq->equipmentType->name ?? '');
            return strtolower($name);
        });
        $hasAny = function (array $keywords) use ($names) {
            return $names->contains(function ($n) use ($keywords) {
                foreach ($keywords as $k) {
                    if (strpos($n, $k) !== false) return true;
                }
                return false;
            });
        };
        $availability = [
            'laundry' => $hasAny(['lavarrop']), // evita confundir con lavavajillas
            'ironing' => $hasAny(['planch']),
            'carwash' => $hasAny(['hidrolav', 'lavado de auto']),
            'shower'  => $hasAny(['ducha']),
            'vacuum'  => $hasAny(['aspirador']),
        ];

        $result = null;
        $savedProfile = null;
        $equipment = null;
        if ($request->filled(['equipment_id', 'numero_personas'])) {
            $equipment = EntityEquipment::find($request->input('equipment_id'));
            $capacidad = (float) $request->input('capacidad_lavarropa_kg', 0);
            if ($capacidad <= 0 && $equipment) {
                // Si el tipo de equipo tuviera capacity_kg (futuro), usarla
                $capacidad = (float)($equipment->equipmentType->capacity_kg ?? 0);
            }
            $result = $service->recommendLaundrySchedule(
                (int)$request->input('numero_personas'),
                max(1.0, $capacidad),
                $equipment
            );
        } elseif ($request->filled('equipment_id')) {
            // Mostrar último perfil guardado si existe
            $equipment = EntityEquipment::find($request->input('equipment_id'));
            $savedProfile = $equipment?->optimization_profile ?? null;
        }

    return view('schedule-optimization.index', compact('entities', 'result', 'savedProfile', 'equipment', 'availability'));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'equipment_id' => ['required', 'exists:entity_equipments,id'],
            'weekdays' => ['required', 'array'],
            'minutes_per_session' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);

        $equipment = EntityEquipment::findOrFail($request->input('equipment_id'));
        $weekdays = array_map('intval', $request->input('weekdays'));

        $equipment->update([
            'is_daily_use' => count($weekdays) >= 7,
            'usage_days_per_week' => count($weekdays),
            'usage_weekdays' => $weekdays,
            'minutes_per_session' => $request->input('minutes_per_session'),
        ]);

        // Calcular ahorro y persistir
        $service = app(\App\Services\ScheduleOptimizationService::class);
        $numeroPersonas = (int) $request->input('numero_personas', 0);
        $capacidad = (float) $request->input('capacidad_lavarropa_kg', 0);
        $recalc = $service->recommendLaundrySchedule($numeroPersonas, max(1.0, $capacidad), $equipment);

        $equipment->optimization_profile = [
            'type' => 'laundry',
            'numero_personas' => $numeroPersonas,
            'capacidad_lavarropa_kg' => $capacidad,
            'frecuencia_sugerida' => $recalc['frecuencia_sugerida'],
            'weekdays' => $recalc['weekdays_recomendados'],
            'minutes_per_session' => (int) $request->input('minutes_per_session'),
            'ahorro' => $recalc['ahorro'],
            'applied_at' => now()->toDateTimeString(),
        ];
        $equipment->save();

        return redirect()->route('equipment.show', $equipment)
            ->with('success', 'Patrón de uso actualizado según recomendación de horarios.');
    }
}
