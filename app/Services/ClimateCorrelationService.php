<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\DailyWeatherLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClimateCorrelationService
{
    /**
     * Analiza la correlación entre temperatura y consumo para una entidad
     *
     * @param Entity $entity
     * @param int $monthsBack Cuántos meses atrás analizar
     * @return array
     */
    public function analyzeCorrelation(Entity $entity, int $monthsBack = 12): array
    {
        $locality = $entity->locality;
        
        if (!$locality || !$locality->latitude) {
            return [
                'success' => false,
                'message' => 'La entidad no tiene localidad con coordenadas definidas.',
            ];
        }

        // Obtener facturas de los últimos N meses con consumo
        $invoices = Invoice::whereHas('contract.supply', function ($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })
        ->where('start_date', '>=', Carbon::now()->subMonths($monthsBack))
        ->whereNotNull('total_energy_consumed_kwh')
        ->where('total_energy_consumed_kwh', '>', 0)
        ->orderBy('start_date')
        ->get();

        if ($invoices->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No hay facturas con consumo registrado en los últimos ' . $monthsBack . ' meses.',
            ];
        }

        // Correlacionar cada factura con clima
        $dataPoints = [];
        $totalCdd = 0;
        $totalHdd = 0;
        $totalKwh = 0;

        foreach ($invoices as $invoice) {
            $weatherStats = DailyWeatherLog::where('locality_id', $locality->id)
                ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
                ->selectRaw('
                    AVG(avg_temp_celsius) as avg_temp,
                    MAX(max_temp_celsius) as max_temp,
                    MIN(min_temp_celsius) as min_temp,
                    SUM(cooling_degree_days) as total_cdd,
                    SUM(heating_degree_days) as total_hdd,
                    COUNT(*) as days
                ')
                ->first();

            if (!$weatherStats || $weatherStats->days == 0) {
                continue; // Saltar si no hay datos climáticos
            }

            $dataPoint = [
                'invoice_id' => $invoice->id,
                'period_start' => $invoice->start_date->format('Y-m-d'),
                'period_end' => $invoice->end_date->format('Y-m-d'),
                'days' => $weatherStats->days,
                'kwh' => $invoice->total_energy_consumed_kwh,
                'kwh_per_day' => round($invoice->total_energy_consumed_kwh / $weatherStats->days, 2),
                'avg_temp' => round($weatherStats->avg_temp, 1),
                'max_temp' => round($weatherStats->max_temp, 1),
                'min_temp' => round($weatherStats->min_temp, 1),
                'cooling_degree_days' => round($weatherStats->total_cdd, 1),
                'heating_degree_days' => round($weatherStats->total_hdd, 1),
                'month' => $invoice->start_date->format('M Y'),
            ];

            $dataPoints[] = $dataPoint;
            $totalCdd += $weatherStats->total_cdd;
            $totalHdd += $weatherStats->total_hdd;
            $totalKwh += $invoice->total_energy_consumed_kwh;
        }

        if (empty($dataPoints)) {
            return [
                'success' => false,
                'message' => 'No se encontraron datos climáticos para los períodos de facturación.',
            ];
        }

        // Calcular correlación de Pearson (temperatura promedio vs kWh/día)
        $correlation = $this->calculatePearsonCorrelation(
            array_column($dataPoints, 'avg_temp'),
            array_column($dataPoints, 'kwh_per_day')
        );

        // Determinar si el consumo está más relacionado con frío o calor
        $climateProfile = $this->determineClimateProfile($totalCdd, $totalHdd, $totalKwh);

        // Detectar outliers (consumos anómalos)
        $outliers = $this->detectOutliers($dataPoints);

        return [
            'success' => true,
            'data_points' => $dataPoints,
            'correlation' => [
                'coefficient' => round($correlation, 3),
                'strength' => $this->interpretCorrelation($correlation),
                'interpretation' => $this->getCorrelationInterpretation($correlation),
            ],
            'climate_profile' => $climateProfile,
            'outliers' => $outliers,
            'summary' => [
                'total_periods' => count($dataPoints),
                'total_kwh' => round($totalKwh, 2),
                'avg_kwh_per_period' => round($totalKwh / count($dataPoints), 2),
                'total_cooling_degree_days' => round($totalCdd, 1),
                'total_heating_degree_days' => round($totalHdd, 1),
            ],
        ];
    }

    /**
     * Calcula el coeficiente de correlación de Pearson
     */
    private function calculatePearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n < 2) return 0;

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt((($n * $sumX2) - ($sumX * $sumX)) * (($n * $sumY2) - ($sumY * $sumY)));

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }

    /**
     * Interpreta la fuerza de la correlación
     */
    private function interpretCorrelation(float $r): string
    {
        $abs = abs($r);
        if ($abs >= 0.9) return 'Muy fuerte';
        if ($abs >= 0.7) return 'Fuerte';
        if ($abs >= 0.5) return 'Moderada';
        if ($abs >= 0.3) return 'Débil';
        return 'Muy débil o nula';
    }

    /**
     * Genera interpretación textual de la correlación
     */
    private function getCorrelationInterpretation(float $r): string
    {
        if ($r > 0.7) {
            return "Existe una fuerte correlación positiva: a mayor temperatura, mayor consumo. Tu consumo está dominado por refrigeración (AC).";
        } elseif ($r < -0.7) {
            return "Existe una fuerte correlación negativa: a menor temperatura, mayor consumo. Tu consumo está dominado por calefacción.";
        } elseif (abs($r) < 0.3) {
            return "No hay correlación significativa con la temperatura. Tu consumo es relativamente estable independientemente del clima.";
        } else {
            return "Hay una correlación " . ($r > 0 ? "positiva" : "negativa") . " moderada con la temperatura.";
        }
    }

    /**
     * Determina el perfil climático de consumo
     */
    private function determineClimateProfile(float $totalCdd, float $totalHdd, float $totalKwh): array
    {
        $cddRatio = $totalCdd > 0 ? ($totalCdd / ($totalCdd + $totalHdd)) : 0;

        if ($cddRatio > 0.7) {
            $profile = 'cooling_dominant';
            $icon = 'fa-fan';
            $color = 'orange';
            $description = 'Tu consumo está dominado por refrigeración (aire acondicionado). Los meses cálidos representan tu mayor gasto.';
        } elseif ($cddRatio < 0.3) {
            $profile = 'heating_dominant';
            $icon = 'fa-fire';
            $color = 'blue';
            $description = 'Tu consumo está dominado por calefacción. Los meses fríos representan tu mayor gasto.';
        } else {
            $profile = 'balanced';
            $icon = 'fa-balance-scale';
            $color = 'green';
            $description = 'Tienes un perfil balanceado: usas tanto refrigeración como calefacción según la estación.';
        }

        return [
            'profile' => $profile,
            'icon' => $icon,
            'color' => $color,
            'description' => $description,
            'cooling_ratio' => round($cddRatio * 100, 1),
            'heating_ratio' => round((1 - $cddRatio) * 100, 1),
        ];
    }

    /**
     * Detecta outliers (consumos anómalos) usando método IQR
     */
    private function detectOutliers(array $dataPoints): array
    {
        $kwhPerDay = array_column($dataPoints, 'kwh_per_day');
        sort($kwhPerDay);
        
        $n = count($kwhPerDay);
        $q1 = $kwhPerDay[floor($n * 0.25)];
        $q3 = $kwhPerDay[floor($n * 0.75)];
        $iqr = $q3 - $q1;
        
        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        $outliers = [];
        foreach ($dataPoints as $point) {
            if ($point['kwh_per_day'] < $lowerBound || $point['kwh_per_day'] > $upperBound) {
                $outliers[] = [
                    'period' => $point['month'],
                    'kwh_per_day' => $point['kwh_per_day'],
                    'avg_temp' => $point['avg_temp'],
                    'type' => $point['kwh_per_day'] > $upperBound ? 'high' : 'low',
                    'deviation_percent' => round((($point['kwh_per_day'] - $q3) / $q3) * 100, 1),
                ];
            }
        }

        return $outliers;
    }

    /**
     * Genera recomendaciones basadas en el análisis
     */
    public function generateRecommendations(array $analysis): array
    {
        if (!$analysis['success']) {
            return [];
        }

        $recommendations = [];

        // Recomendación basada en correlación
        if ($analysis['correlation']['coefficient'] > 0.7) {
            $recommendations[] = [
                'type' => 'cooling_optimization',
                'priority' => 'high',
                'title' => 'Optimizar uso de aire acondicionado',
                'description' => 'Tu consumo tiene fuerte correlación con temperatura. Considera: termostato programable, mejora de aislación, o reemplazo por equipo inverter.',
                'potential_saving_percent' => 30,
            ];
        } elseif ($analysis['correlation']['coefficient'] < -0.7) {
            $recommendations[] = [
                'type' => 'heating_optimization',
                'priority' => 'high',
                'title' => 'Optimizar sistema de calefacción',
                'description' => 'Tu consumo aumenta significativamente con el frío. Evalúa: termostato inteligente, aislación de ventanas, o equipo más eficiente.',
                'potential_saving_percent' => 25,
            ];
        }

        // Recomendación por outliers
        if (!empty($analysis['outliers'])) {
            $highOutliers = array_filter($analysis['outliers'], fn($o) => $o['type'] === 'high');
            if (count($highOutliers) > 0) {
                $recommendations[] = [
                    'type' => 'anomaly_investigation',
                    'priority' => 'medium',
                    'title' => 'Investigar picos de consumo',
                    'description' => 'Detectamos ' . count($highOutliers) . ' período(s) con consumo anormalmente alto. Revisa ajustes de equipos en esos meses.',
                    'affected_periods' => array_column($highOutliers, 'period'),
                ];
            }
        }

        return $recommendations;
    }
}
