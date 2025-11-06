<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\SolarPotentialAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SolarPanelController extends Controller
{
    protected $solarService;

    public function __construct(SolarPotentialAnalysisService $solarService)
    {
        $this->solarService = $solarService;
    }

    /**
     * Muestra análisis de potencial solar para todas las entidades
     */
    public function index()
    {
        $company = auth()->user()->company;

        $entities = $company->entities()
            ->with(['locality.province', 'invoices', 'equipments.equipmentType'])
            ->get();

        $analyses = [];
        foreach ($entities as $entity) {
            $analyses[$entity->id] = $this->solarService->analyzePotential($entity);
        }

        return view('solar-panel.index', [
            'entities' => $entities,
            'analyses' => $analyses,
        ]);
    }

    /**
     * Formulario para configurar datos del techo
     */
    public function configure(Entity $entity)
    {
        Gate::authorize('update', $entity);

        return view('solar-panel.configure', [
            'entity' => $entity,
        ]);
    }

    /**
     * Guarda configuración del techo y terreno
     */
    public function storeConfig(Request $request, Entity $entity)
    {
        Gate::authorize('update', $entity);

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
            ->route('solar-panel.index')
            ->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Vista de simulación de escenarios
     */
    public function simulate(Entity $entity)
    {
        Gate::authorize('view', $entity);

        if (!$entity->roof_area_m2 && !$entity->ground_area_m2) {
            return redirect()
                ->route('solar-panel.configure', $entity)
                ->with('warning', 'Primero debes configurar los datos de espacio disponible.');
        }

        // Analizar escenarios: 25%, 50%, 75%, 100% del área disponible
        $fullAnalysis = $this->solarService->analyzePotential($entity);
        
        $scenarios = [];
        foreach ([0.25, 0.5, 0.75, 1.0] as $factor) {
            // Crear entidad temporal con áreas reducidas
            $tempEntity = $entity->replicate();
            $tempEntity->roof_area_m2 = ($entity->roof_area_m2 ?? 0) * $factor;
            $tempEntity->ground_area_m2 = ($entity->ground_area_m2 ?? 0) * $factor;
            
            $scenarios[] = [
                'percent' => $factor * 100,
                'analysis' => $this->solarService->analyzePotential($tempEntity),
            ];
        }

        return view('solar-panel.simulate', [
            'entity' => $entity,
            'scenarios' => $scenarios,
            'fullAnalysis' => $fullAnalysis,
        ]);
    }
}
