<?php

namespace App\Services;

use App\Models\DailyWeatherLog;
use App\Models\Locality;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
    /**
     * Base URL para la API histórica de Open-Meteo
     */
    private const ARCHIVE_API_URL = 'https://archive-api.open-meteo.com/v1/archive';

    /**
     * Obtiene y almacena datos climáticos históricos para una localidad
     *
     * @param Locality $locality Localidad con lat/long
     * @param string $startDate Fecha inicio (formato Y-m-d)
     * @param string $endDate Fecha fin (formato Y-m-d)
     * @return array Estadísticas del proceso
     */
    public function fetchAndStoreHistoricalWeather(Locality $locality, string $startDate, string $endDate): array
    {
        if (!$locality->latitude || !$locality->longitude) {
            throw new \Exception("Locality {$locality->name} no tiene coordenadas definidas.");
        }

        try {
            $response = Http::timeout(30)->get(self::ARCHIVE_API_URL, [
                'latitude' => $locality->latitude,
                'longitude' => $locality->longitude,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean',
                'timezone' => 'America/Argentina/Buenos_Aires',
            ]);

            if (!$response->successful()) {
                throw new \Exception("Error al obtener datos de Open-Meteo: " . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['daily']['time'])) {
                throw new \Exception("Respuesta inesperada de Open-Meteo: no contiene datos diarios.");
            }

            return $this->storeWeatherData($locality, $data['daily']);

        } catch (\Exception $e) {
            Log::error("Error en WeatherService::fetchAndStoreHistoricalWeather", [
                'locality_id' => $locality->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Almacena los datos climáticos en la base de datos
     *
     * @param Locality $locality
     * @param array $dailyData Datos diarios de la API
     * @return array Estadísticas
     */
    private function storeWeatherData(Locality $locality, array $dailyData): array
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        $dates = $dailyData['time'];
        $temps_max = $dailyData['temperature_2m_max'];
        $temps_min = $dailyData['temperature_2m_min'];
        $temps_mean = $dailyData['temperature_2m_mean'];

        foreach ($dates as $index => $date) {
            // Validar que los datos de temperatura existen
            if (is_null($temps_mean[$index]) || is_null($temps_max[$index]) || is_null($temps_min[$index])) {
                $skipped++;
                continue;
            }

            $weatherData = [
                'locality_id' => $locality->id,
                'date' => $date,
                'avg_temp_celsius' => round($temps_mean[$index], 1),
                'max_temp_celsius' => round($temps_max[$index], 1),
                'min_temp_celsius' => round($temps_min[$index], 1),
                'heating_degree_days' => $this->calculateHeatingDegreeDays($temps_mean[$index]),
                'cooling_degree_days' => $this->calculateCoolingDegreeDays($temps_mean[$index]),
            ];

            $existing = DailyWeatherLog::where('locality_id', $locality->id)
                ->where('date', $date)
                ->first();

            if ($existing) {
                $existing->update($weatherData);
                $updated++;
            } else {
                DailyWeatherLog::create($weatherData);
                $inserted++;
            }
        }

        return [
            'locality' => $locality->name,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'total' => count($dates),
        ];
    }

    /**
     * Calcula grados-día de calefacción (HDD)
     * Base: 18°C (temperatura de confort)
     *
     * @param float $avgTemp Temperatura promedio del día
     * @return float
     */
    private function calculateHeatingDegreeDays(float $avgTemp): float
    {
        $baseTemp = 18.0;
        return max(0, $baseTemp - $avgTemp);
    }

    /**
     * Calcula grados-día de refrigeración (CDD)
     * Base: 24°C (temperatura de confort superior)
     *
     * @param float $avgTemp Temperatura promedio del día
     * @return float
     */
    private function calculateCoolingDegreeDays(float $avgTemp): float
    {
        $baseTemp = 24.0;
        return max(0, $avgTemp - $baseTemp);
    }

    /**
     * Obtiene temperatura promedio para un rango de fechas desde la DB
     *
     * @param Locality $locality
     * @param string $startDate
     * @param string $endDate
     * @return array|null
     */
    public function getAverageTemperatureForPeriod(Locality $locality, string $startDate, string $endDate): ?array
    {
        $stats = DailyWeatherLog::where('locality_id', $locality->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                AVG(avg_temp_celsius) as avg_temp,
                MAX(max_temp_celsius) as max_temp,
                MIN(min_temp_celsius) as min_temp,
                SUM(cooling_degree_days) as total_cdd,
                SUM(heating_degree_days) as total_hdd,
                COUNT(*) as days_count
            ')
            ->first();

        if (!$stats || $stats->days_count == 0) {
            return null;
        }

        return [
            'avg_temp' => round($stats->avg_temp, 1),
            'max_temp' => round($stats->max_temp, 1),
            'min_temp' => round($stats->min_temp, 1),
            'cooling_degree_days' => round($stats->total_cdd, 1),
            'heating_degree_days' => round($stats->total_hdd, 1),
            'days_count' => $stats->days_count,
        ];
    }
}
