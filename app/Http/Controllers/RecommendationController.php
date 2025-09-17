<?php

namespace App\Http\Controllers;

use App\Services\InventoryAnalysisService;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    /**
     * Muestra una lista de todas las recomendaciones y oportunidades de mejora.
     */
    public function index(InventoryAnalysisService $analysisService)
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

        return view('recommendations.index', compact('opportunitiesByEntity'));
    }
}