<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SolarInstallation;
use Carbon\Carbon;

class SolarController extends Controller
{
    /**
     * Muestra el dashboard solar.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $installations = SolarInstallation::whereHas('entity', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('entity')->get();

        if ($installations->isEmpty()) {
            return view('solar.dashboard_empty');
        }

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
}