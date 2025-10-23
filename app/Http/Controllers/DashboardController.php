<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal de la aplicación.
     */
    public function index(): View
    {
        $user = Auth::user()->load('company.entities');

        $entities = $user->company ? $user->company->entities : collect();

        $globalSummary = [
            'entity_count' => $entities->count(),
            'total_consumption' => 0, // TODO: Implementar cálculo real
            'total_cost' => 0,        // TODO: Implementar cálculo real
        ];

        // En el futuro, se podría expandir esto para calcular el consumo y coste
        // global real iterando sobre las últimas facturas de cada entidad.

        return view('dashboard', [
            'user' => $user,
            'entities' => $entities,
            'globalSummary' => (object) $globalSummary,
        ]);
    }
}