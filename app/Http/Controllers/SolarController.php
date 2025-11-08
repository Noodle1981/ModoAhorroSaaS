<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\SolarInstallation;
use App\Models\Entity;
use App\Services\SolarPotentialAnalysisService;
use Carbon\Carbon;

class SolarController extends Controller
{
    protected $solarPotentialService;

    public function __construct(SolarPotentialAnalysisService $solarPotentialService)
    {
        $this->solarPotentialService = $solarPotentialService;
    }

    /**
     * Muestra el dashboard solar adaptativo.
     * Si tiene instalación real → redirige a /solar-panel
     * Si no tiene → análisis de potencial y simulación
     */
    public function dashboard()
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener instalaciones reales
        $installations = SolarInstallation::whereHas('entity', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('entity')->get();

        // Si tiene instalaciones reales → redirigir a /solar-panel
        if ($installations->isNotEmpty()) {
            return redirect()->route('solar-panel.index')
                ->with('info', 'Tienes instalaciones solares activas. Acá podés ver su producción y configuración.');
        }

        // Si NO tiene instalaciones → análisis de potencial
        return $this->index();
    }

    /**
     * Muestra análisis de potencial solar para todas las entidades (usuarios SIN instalación)
     */
    public function index()
    {
        $company = auth()->user()->company;

        $entities = $company->entities()
            ->with(['locality.province', 'equipments.equipmentType'])
            ->get();

        $analyses = [];
        foreach ($entities as $entity) {
            $analyses[$entity->id] = $this->solarPotentialService->analyzePotential($entity);
        }

        return view('solar.index', [
            'entities' => $entities,
            'analyses' => $analyses,
        ]);
    }

    /**
     * Formulario para configurar datos del techo (usuarios SIN instalación)
     */
    public function configure(Entity $entity)
    {
        $this->authorize('update', $entity);

        return view('solar.configure', [
            'entity' => $entity,
        ]);
    }

    /**
     * Guarda configuración del techo y terreno (usuarios SIN instalación)
     */
    public function storeConfig(Request $request, Entity $entity)
    {
        $this->authorize('update', $entity);

        $validated = $request->validate([
            // Techo
            'roof_area_m2' => 'nullable|numeric|min:0|max:10000',
            'roof_obstacles_percent' => 'nullable|integer|min:0|max:100',
            'has_shading' => 'boolean',
            'shading_hours_daily' => 'nullable|integer|min:0|max:12',
            'shading_source' => 'nullable|string|max:255',
            'roof_orientation' => 'nullable|in:N,S,E,O,NE,NO,SE,SO',
            'roof_slope_degrees' => 'nullable|integer|min:0|max:90',
            // Terreno
            'ground_area_m2' => 'nullable|numeric|min:0|max:10000',
            'ground_location' => 'nullable|in:front,back,side',
            'ground_has_trees' => 'boolean',
            'ground_shade_percent' => 'nullable|integer|min:0|max:100',
            'ground_notes' => 'nullable|string|max:500',
            // General
            'solar_panel_interest' => 'boolean',
            'solar_panel_notes' => 'nullable|string|max:500',
        ]);

        $entity->update($validated);

        return redirect()
            ->route('solar.index')
            ->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Vista de simulación de escenarios (usuarios SIN instalación)
     */
    public function simulate(Entity $entity)
    {
        $this->authorize('view', $entity);

        if (!$entity->roof_area_m2 && !$entity->ground_area_m2) {
            return redirect()
                ->route('solar.configure', $entity)
                ->with('warning', 'Primero debes configurar los datos de espacio disponible.');
        }

        // Analizar escenarios: 25%, 50%, 75%, 100% del área disponible
        $fullAnalysis = $this->solarPotentialService->analyzePotential($entity);
        
        $scenarios = [];
        foreach ([0.25, 0.5, 0.75, 1.0] as $factor) {
            // Crear entidad temporal con áreas reducidas
            $tempEntity = $entity->replicate();
            $tempEntity->roof_area_m2 = ($entity->roof_area_m2 ?? 0) * $factor;
            $tempEntity->ground_area_m2 = ($entity->ground_area_m2 ?? 0) * $factor;
            
            $scenarios[] = [
                'percent' => $factor * 100,
                'analysis' => $this->solarPotentialService->analyzePotential($tempEntity),
            ];
        }

        return view('solar.simulate', [
            'entity' => $entity,
            'scenarios' => $scenarios,
            'fullAnalysis' => $fullAnalysis,
        ]);
    }
}