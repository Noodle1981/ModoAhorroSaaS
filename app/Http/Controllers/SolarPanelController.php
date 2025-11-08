<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\SolarInstallation;
use App\Services\SolarPotentialAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolarPanelController extends Controller
{
    protected $solarService;

    public function __construct(SolarPotentialAnalysisService $solarService)
    {
        $this->solarService = $solarService;
    }

    /**
     * Muestra dashboard de instalaciones solares REALES (usuario CON paneles instalados)
     */
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        // Obtener instalaciones reales
        $installations = SolarInstallation::whereHas('entity', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('entity')->get();

        // Si no tiene instalaciones → redirigir a /solar
        if ($installations->isEmpty()) {
            return redirect()->route('solar.dashboard')
                ->with('info', 'Aún no tienes paneles solares instalados. Podés calcular el potencial de ahorro aquí.');
        }

        // Procesar datos de producción
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

        return view('solar-panel.index', compact('solarData'));
    }

    /**
     * Formulario para registrar/configurar una instalación real
     */
    public function configure(Entity $entity)
    {
        Gate::authorize('update', $entity);

        $installation = SolarInstallation::where('entity_id', $entity->id)->first();

        return view('solar-panel.configure', [
            'entity' => $entity,
            'installation' => $installation,
        ]);
    }

    /**
     * Guarda o actualiza la configuración de instalación real
     */
    public function storeConfig(Request $request, Entity $entity)
    {
        Gate::authorize('update', $entity);

        $validated = $request->validate([
            'installed_kwp' => 'required|numeric|min:0.1|max:1000',
            'panel_brand' => 'nullable|string|max:100',
            'panel_model' => 'nullable|string|max:100',
            'inverter_brand' => 'nullable|string|max:100',
            'inverter_model' => 'nullable|string|max:100',
            'installation_date' => 'nullable|date',
            'roof_orientation' => 'nullable|in:N,S,E,O,NE,NO,SE,SO',
            'roof_slope_degrees' => 'nullable|integer|min:0|max:90',
            'api_url' => 'nullable|url|max:500',
            'api_token' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        SolarInstallation::updateOrCreate(
            ['entity_id' => $entity->id],
            $validated
        );

        return redirect()
            ->route('solar-panel.index')
            ->with('success', 'Instalación solar configurada correctamente.');
    }
}
