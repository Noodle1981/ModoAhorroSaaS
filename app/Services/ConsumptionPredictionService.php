<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\DailyWeatherLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ConsumptionPredictionService
{
    /**
     * Predice el consumo futuro basado en temperatura pronosticada
     *
     * @param Entity $entity
     * @param int $daysAhead Días hacia adelante a predecir
     * @return array
     */
    public function predictFutureConsumption(Entity $entity, int $daysAhead = 30): array
    {
        $locality = $entity->locality;
        
        if (!$locality || !$locality->latitude) {
            return [
                'success' => false,
                'message' => 'La entidad no tiene localidad con coordenadas definidas.',
            ];
        }

        // 1. Entrenar modelo con datos históricos
        $model = $this->trainModel($entity);
        
        if (!$model['success']) {
            return $model;
        }

        // 2. Obtener pronóstico de temperatura
        $forecast = $this->getTemperatureForecast($locality, $daysAhead);
        
        if (!$forecast['success']) {
            return $forecast;
        }

        // 3. Hacer predicciones
        $predictions = [];
        foreach ($forecast['daily_data'] as $day) {
            $predictedKwh = $this->predictConsumptionForTemp(
                $day['avg_temp'],
                $model['slope'],
                $model['intercept']
            );

            $predictions[] = [
                'date' => $day['date'],
                'avg_temp' => $day['avg_temp'],
                'predicted_kwh' => round($predictedKwh, 2),
            ];
        }

        // Calcular totales proyectados
        $totalPredicted = array_sum(array_column($predictions, 'predicted_kwh'));
        $avgDaily = $totalPredicted / count($predictions);

        return [
            'success' => true,
            'predictions' => $predictions,
            'model' => $model,
            'summary' => [
                'total_predicted_kwh' => round($totalPredicted, 2),
                'avg_daily_kwh' => round($avgDaily, 2),
                'forecast_days' => count($predictions),
                'model_r_squared' => $model['r_squared'],
            ],
        ];
    }

    /**
     * Entrena un modelo de regresión lineal simple: kWh = slope * temp + intercept
     */
    private function trainModel(Entity $entity): array
    {
        $locality = $entity->locality;

        // Obtener datos históricos (últimos 6 meses)
        $dataPoints = [];
        
        $invoices = Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
            ->where('start_date', '>=', Carbon::now()->subMonths(6))
            ->whereNotNull('total_energy_consumed_kwh')
            ->where('total_energy_consumed_kwh', '>', 0)
            ->orderBy('start_date')
            ->get();

        if ($invoices->count() < 3) {
            return [
                'success' => false,
                'message' => 'Se necesitan al menos 3 facturas históricas para entrenar el modelo.',
            ];
        }

        foreach ($invoices as $invoice) {
            $weatherStats = DailyWeatherLog::where('locality_id', $locality->id)
                ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
                ->selectRaw('AVG(avg_temp_celsius) as avg_temp, COUNT(*) as days')
                ->first();

            if (!$weatherStats || $weatherStats->days == 0) {
                continue;
            }

            $dataPoints[] = [
                'temp' => $weatherStats->avg_temp,
                'kwh_per_day' => $invoice->total_energy_consumed_kwh / $weatherStats->days,
            ];
        }

        if (count($dataPoints) < 3) {
            return [
                'success' => false,
                'message' => 'Datos insuficientes para entrenar el modelo.',
            ];
        }

        // Regresión lineal simple
        $temps = array_column($dataPoints, 'temp');
        $kwhs = array_column($dataPoints, 'kwh_per_day');

        $n = count($temps);
        $sumX = array_sum($temps);
        $sumY = array_sum($kwhs);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $temps[$i] * $kwhs[$i];
            $sumX2 += $temps[$i] * $temps[$i];
            $sumY2 += $kwhs[$i] * $kwhs[$i];
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumX2) - ($sumX * $sumX));
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        // Calcular R² (bondad de ajuste)
        $meanY = $sumY / $n;
        $ssTotal = 0;
        $ssResidual = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = ($slope * $temps[$i]) + $intercept;
            $ssTotal += pow($kwhs[$i] - $meanY, 2);
            $ssResidual += pow($kwhs[$i] - $predicted, 2);
        }

        $rSquared = 1 - ($ssResidual / ($ssTotal ?: 1));

        return [
            'success' => true,
            'slope' => round($slope, 4),
            'intercept' => round($intercept, 4),
            'r_squared' => round($rSquared, 3),
            'training_points' => $n,
            'interpretation' => $this->interpretModel($slope, $rSquared),
        ];
    }

    /**
     * Predice consumo para una temperatura dada
     */
    private function predictConsumptionForTemp(float $temp, float $slope, float $intercept): float
    {
        return max(0, ($slope * $temp) + $intercept);
    }

    /**
     * Obtiene pronóstico de temperatura desde Open-Meteo
     */
    private function getTemperatureForecast($locality, int $days): array
    {
        try {
            $endDate = Carbon::now()->addDays($days)->format('Y-m-d');

            $response = Http::timeout(30)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $locality->latitude,
                'longitude' => $locality->longitude,
                'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean',
                'timezone' => 'America/Argentina/Buenos_Aires',
                'forecast_days' => min($days, 16), // Open-Meteo limita a 16 días
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Error al obtener pronóstico de temperatura.',
                ];
            }

            $data = $response->json();
            
            $dailyData = [];
            foreach ($data['daily']['time'] as $index => $date) {
                $dailyData[] = [
                    'date' => $date,
                    'avg_temp' => round($data['daily']['temperature_2m_mean'][$index], 1),
                    'max_temp' => round($data['daily']['temperature_2m_max'][$index], 1),
                    'min_temp' => round($data['daily']['temperature_2m_min'][$index], 1),
                ];
            }

            return [
                'success' => true,
                'daily_data' => $dailyData,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al conectar con API de pronóstico: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Interpreta el modelo entrenado
     */
    private function interpretModel(float $slope, float $rSquared): string
    {
        $direction = $slope > 0 ? 'aumenta' : 'disminuye';
        $magnitude = abs($slope);

        $quality = '';
        if ($rSquared > 0.8) {
            $quality = 'excelente';
        } elseif ($rSquared > 0.6) {
            $quality = 'buena';
        } elseif ($rSquared > 0.4) {
            $quality = 'moderada';
        } else {
            $quality = 'baja';
        }

        return sprintf(
            'Por cada grado de temperatura, el consumo %s %.2f kWh/día. Precisión del modelo: %s (R² = %.2f).',
            $direction,
            $magnitude,
            $quality,
            $rSquared
        );
    }

    /**
     * Genera alertas si la predicción indica consumo alto
     */
    public function generatePredictionAlerts(Entity $entity): int
    {
        $prediction = $this->predictFutureConsumption($entity, 7);

        if (!$prediction['success']) {
            return 0;
        }

        // Obtener consumo promedio histórico
        $avgHistorical = Invoice::whereHas('contract.supply', fn($q) => $q->where('entity_id', $entity->id))
            ->where('start_date', '>=', Carbon::now()->subMonths(3))
            ->whereNotNull('total_energy_consumed_kwh')
            ->avg('total_energy_consumed_kwh');

        if (!$avgHistorical) {
            return 0;
        }

        // Proyectar para el período completo (30 días típicamente)
        $predictedMonthly = $prediction['summary']['avg_daily_kwh'] * 30;
        $deviation = (($predictedMonthly - $avgHistorical) / $avgHistorical) * 100;

        // Generar alerta solo si se predice aumento > 15%
        if ($deviation > 15) {
            $exists = \App\Models\SmartAlert::where('entity_id', $entity->id)
                ->where('type', 'consumption_prediction')
                ->where('created_at', '>', Carbon::now()->subDays(7))
                ->exists();

            if (!$exists) {
                \App\Models\SmartAlert::create([
                    'entity_id' => $entity->id,
                    'type' => 'consumption_prediction',
                    'severity' => $deviation > 30 ? 'warning' : 'info',
                    'title' => 'Alerta: Aumento de consumo previsto',
                    'description' => sprintf(
                        'Según el pronóstico climático de los próximos 7 días, se espera un consumo de %.2f kWh/día (%.2f kWh/mes), un %.1f%% superior a tu promedio.',
                        $prediction['summary']['avg_daily_kwh'],
                        $predictedMonthly,
                        $deviation
                    ),
                    'data' => [
                        'predicted_daily_kwh' => $prediction['summary']['avg_daily_kwh'],
                        'historical_avg_kwh' => round($avgHistorical, 2),
                        'deviation_percent' => round($deviation, 1),
                    ],
                ]);

                return 1;
            }
        }

        return 0;
    }
}
