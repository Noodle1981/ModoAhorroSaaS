<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Si tiene instalación real → producción
     * Si no tiene → potencial de ahorro
     */
    public function dashboard()
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener instalaciones reales
        $installations = SolarInstallation::whereHas('entity', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('entity')->get();

        // Si tiene instalaciones reales → mostrar producción
        if ($installations->isNotEmpty()) {
            $solarData = $installations->map(function ($installation) {
                
                $kpis = [
                    'today' => round($installation->solarProductionReadings()->whereDate('reading_timestamp', today())->sum('produced_kwh'), 2),
                    'this_month' => round($installation->solarProductionReadings()->whereYear('reading_timestamp', now()->year)->whereMonth('reading_timestamp', now()->month)->sum('produced_kwh'), 2),
                    'total' => round($installation->solarProductionReadings()->sum('produced_kwh'), 2),
                ];

                $chartReadings = $installation->solarProductionReadings()
                    ->where('reading_timestamp', '>=', Carbon::now()->subDays(30))
                    ->select(
                        DB::raw('DATE(reading_timestamp) as date'),
                        DB::raw('SUM(produced_kwh) as total_kwh')
                    )
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();
                
                $chartData = [
                    'labels' => $chartReadings->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d/m')),
                    'data' => $chartReadings->pluck('total_kwh'),
                ];

                return (object) [
                    'installation' => $installation,
                    'kpis' => (object) $kpis,
                    'chartData' => (object) $chartData,
                ];
            });

            return view('solar.dashboard', compact('solarData'));
        }

        // Si NO tiene instalaciones → analizar potencial
        $entities = $company->entities()->get();

        // Analizar potencial de cada entidad
        $potentialAnalyses = [];
        $totalSavingsPotential = 0;
        $hasRoofData = false;

        foreach ($entities as $entity) {
            // Cargar relaciones necesarias
            $entity->load('locality.province');
            
            $analysis = $this->solarPotentialService->analyzePotential($entity);
            
            if ($analysis['has_data']) {
                $hasRoofData = true;
                $potentialAnalyses[] = [
                    'entity' => $entity,
                    'analysis' => $analysis,
                ];
                $totalSavingsPotential += $analysis['savings_annual_ars'];
            }
        }

        // Si tiene datos del techo → mostrar potencial
        if ($hasRoofData) {
            return view('solar.dashboard_potential', [
                'potentialAnalyses' => $potentialAnalyses,
                'totalSavingsPotential' => $totalSavingsPotential,
            ]);
        }

        // Si no tiene datos → estado vacío
        return view('solar.dashboard_empty');
    }
}