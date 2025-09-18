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
        $user = Auth::user();

        // En el futuro, aquí podrías recopilar datos para mostrar en el dashboard,
        // como la cantidad de entidades, el consumo del último mes, etc.
        // $entityCount = $user->company->entities()->count();
        // $lastMonthConsumption = ...

        // Por ahora, simplemente devolvemos la vista.
        return view('dashboard'
            // , compact('entityCount', 'lastMonthConsumption')
        );
    }
}