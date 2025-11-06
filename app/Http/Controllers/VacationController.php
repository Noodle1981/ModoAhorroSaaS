<?php

namespace App\Http\Controllers;

use App\Models\VacationPlan;
use App\Models\Entity;
use App\Models\EntityEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VacationController extends Controller
{
    /**
     * Mostrar lista de planes de vacaciones
     */
    public function index()
    {
        $user = Auth::user();
        
        $plans = VacationPlan::whereHas('entity', function($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
        ->with('entity')
        ->orderBy('start_date', 'desc')
        ->get();

        // Actualizar estados automáticamente
        foreach ($plans as $plan) {
            $plan->updateStatus();
        }

        return view('vacations.index', compact('plans'));
    }

    /**
     * Mostrar formulario para crear nuevo plan
     */
    public function create()
    {
        $user = Auth::user();
        
        $entities = Entity::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('vacations.create', compact('entities'));
    }

    /**
     * Guardar nuevo plan de vacaciones
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysAway = $startDate->diffInDays($endDate) + 1;

        $plan = VacationPlan::create([
            'entity_id' => $validated['entity_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_away' => $daysAway,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('vacations.recommendations', $plan)
            ->with('success', 'Plan de vacaciones creado. Aquí están tus recomendaciones.');
    }

    /**
     * Mostrar recomendaciones de equipos para apagar
     */
    public function recommendations(VacationPlan $plan)
    {
        // Verificar que el usuario tenga acceso
        $user = Auth::user();
        if ($plan->entity->company_id !== $user->company_id) {
            abort(403);
        }

        $entity = $plan->entity;
        $daysAway = $plan->days_away;

        // Obtener equipos de la entidad
        $equipments = EntityEquipment::where('entity_id', $entity->id)
            ->with(['equipmentType', 'category'])
            ->get();

        // Clasificar equipos según recomendaciones
        $recommendations = $this->generateRecommendations($equipments, $daysAway);

        return view('vacations.recommendations', compact('plan', 'entity', 'recommendations'));
    }

    /**
     * Generar recomendaciones según duración y tipo de equipo
     */
    private function generateRecommendations($equipments, $daysAway)
    {
        $recommendations = [
            'always_off' => [],      // Siempre apagar (termotanques, AC, calefacción)
            'if_long' => [],         // Apagar si ausencia > 7 días (heladeras, freezers)
            'standby_off' => [],     // Desenchufar standby (TVs, consolas, cargadores)
            'keep_on' => [],         // Dejar encendidos (alarmas, cámaras, router si necesario)
        ];

        foreach ($equipments as $equipment) {
            $categoryName = strtolower($equipment->category->name ?? '');
            $typeName = strtolower($equipment->equipmentType->name ?? '');

            // SIEMPRE APAGAR (alto consumo sin uso)
            if (
                str_contains($categoryName, 'calefacción') ||
                str_contains($categoryName, 'climatización') ||
                str_contains($typeName, 'aire acondicionado') ||
                str_contains($typeName, 'termotanque') ||
                str_contains($typeName, 'calefón') ||
                str_contains($typeName, 'estufa') ||
                str_contains($typeName, 'split') ||
                str_contains($typeName, 'radiador')
            ) {
                $recommendations['always_off'][] = $equipment;
                continue;
            }

            // APAGAR SI AUSENCIA LARGA (>7 días)
            if (
                $daysAway > 7 && (
                    str_contains($typeName, 'heladera') ||
                    str_contains($typeName, 'freezer') ||
                    str_contains($typeName, 'frigorífico')
                )
            ) {
                $recommendations['if_long'][] = $equipment;
                continue;
            }

            // STANDBY OFF (bajo consumo pero acumulativo)
            if (
                str_contains($categoryName, 'entretenimiento') ||
                str_contains($typeName, 'tv') ||
                str_contains($typeName, 'televisor') ||
                str_contains($typeName, 'consola') ||
                str_contains($typeName, 'playstation') ||
                str_contains($typeName, 'xbox') ||
                str_contains($typeName, 'cargador') ||
                str_contains($typeName, 'microondas') ||
                str_contains($typeName, 'cafetera')
            ) {
                $recommendations['standby_off'][] = $equipment;
                continue;
            }

            // MANTENER ENCENDIDOS (seguridad/conectividad)
            if (
                str_contains($categoryName, 'seguridad') ||
                str_contains($typeName, 'alarma') ||
                str_contains($typeName, 'cámara') ||
                str_contains($typeName, 'router') ||
                str_contains($typeName, 'modem')
            ) {
                $recommendations['keep_on'][] = $equipment;
                continue;
            }

            // Por defecto, a standby_off
            $recommendations['standby_off'][] = $equipment;
        }

        return $recommendations;
    }

    /**
     * Eliminar plan
     */
    public function destroy(VacationPlan $plan)
    {
        $user = Auth::user();
        if ($plan->entity->company_id !== $user->company_id) {
            abort(403);
        }

        $plan->delete();

        return redirect()->route('vacations.index')
            ->with('success', 'Plan de vacaciones eliminado.');
    }
}
