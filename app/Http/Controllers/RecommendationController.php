<?php

namespace App\Http\Controllers;

use App\Services\InventoryAnalysisService;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    /**
     * Muestra el hub central de recomendaciones
     */
    public function index()
    {
        $user = auth()->user();

        // Aquí podrás agregar contadores o métricas en el futuro
        // Ej: pendingMaintenances, unreadRecommendations, etc.

        return view('recommendations.index', compact('user'));
    }

    /**
     * Muestra una lista de todas las recomendaciones automáticas y oportunidades de mejora
     * (Esta es la funcionalidad original que podría ser un submódulo)
     */
    public function opportunities(InventoryAnalysisService $analysisService)
    {
        $user = Auth::user();
        $entities = $user->company->entities()->get();
        $opportunitiesByEntity = [];

        foreach ($entities as $entity) {
            $opportunities = $analysisService->findAllOpportunities($entity);
            
            if (!empty($opportunities)) {
                $opportunitiesByEntity[$entity->name] = $opportunities;
            }
        }

        return view('recommendations.opportunities', compact('opportunitiesByEntity'));
    }
}