<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\SolarHeaterAnalysisService;
use Illuminate\Http\Request;

class SolarHeaterController extends Controller
{
    protected $analysisService;

    public function __construct(SolarHeaterAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Muestra el análisis de calefón solar para todas las entidades del usuario
     */
    public function index()
    {
        $user = auth()->user();
        $entities = $user->company->entities()->with('equipments.equipmentType')->get();

        $analyses = [];
        foreach ($entities as $entity) {
            $analyses[$entity->id] = [
                'entity' => $entity,
                'analysis' => $this->analysisService->analyzeEntity($entity),
            ];
        }

        return view('solar-heater.index', compact('analyses'));
    }

    /**
     * Muestra el formulario para expresar interés y detalles del calefón actual
     */
    public function showInterest(Entity $entity)
    {
        $this->authorize('view', $entity);

        return view('solar-heater.interest', compact('entity'));
    }

    /**
     * Guarda el interés del usuario en calefón solar
     */
    public function storeInterest(Request $request, Entity $entity)
    {
        $this->authorize('update', $entity);

        $validated = $request->validate([
            'current_heater_type' => ['required', 'in:electric,gas,glp,wood,solar,none'],
            'solar_heater_interest' => ['required', 'boolean'],
            'solar_heater_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $entity->update($validated);

        return redirect()->route('solar-heater.index')
            ->with('success', "Información actualizada para {$entity->name}. ¡Gracias por tu interés!");
    }
}
