<?php
/**
 * Servicio para obtener datos climáticos desde APIs externas
 * Soporta múltiples proveedores gratuitos
 */

namespace App\Services;

use App\Models\Locality;
use App\Models\DailyWeatherLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherApiService
{
    /**
     * APIs disponibles (gratuitas con límites razonables)
     * 
     * 1. Open-Meteo (RECOMENDADO): Sin API key, sin límites, datos históricos completos
     *    https://open-meteo.com/
     * 
     * 2. Visual Crossing (BACKUP): 1000 requests/día gratis
     *    https://www.visualcrossing.com/
     * 
     * 3. WeatherAPI.com: 1M requests/mes gratis
     *    https://www.weatherapi.com/
     */

    private $preferredProvider = 'open-meteo'; // Sin API key necesaria

    /**
     * Obtiene datos climáticos históricos para un período y localidad
     * 
     * @param Locality $locality
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function fetchHistoricalData(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        if (!$locality->latitude || !$locality->longitude) {
            return [
                'success' => false,
                'message' => 'La localidad no tiene coordenadas definidas.',
                'data' => [],
            ];
        }

        try {
            switch ($this->preferredProvider) {
                case 'open-meteo':
                    return $this->fetchFromOpenMeteo($locality, $startDate, $endDate);
                
                case 'visual-crossing':
                    return $this->fetchFromVisualCrossing($locality, $startDate, $endDate);
                
                case 'weatherapi':
                    return $this->fetchFromWeatherAPI($locality, $startDate, $endDate);
                
                default:
                    return $this->fetchFromOpenMeteo($locality, $startDate, $endDate);
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo datos climáticos: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Open-Meteo API (GRATIS, sin API key, excelente para históricos)
     * https://open-meteo.com/en/docs/historical-weather-api
     */
    private function fetchFromOpenMeteo(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        $url = 'https://archive-api.open-meteo.com/v1/archive';
        
        $response = Http::timeout(30)->get($url, [
            'latitude' => $locality->latitude,
            'longitude' => $locality->longitude,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean,precipitation_sum,wind_speed_10m_max',
            'timezone' => 'auto',
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Error en la respuesta de Open-Meteo: ' . $response->status(),
                'data' => [],
            ];
        }

        $data = $response->json();

        if (!isset($data['daily']['time'])) {
            return [
                'success' => false,
                'message' => 'Respuesta inválida de Open-Meteo',
                'data' => [],
            ];
        }

        $weatherData = [];
        $dailyData = $data['daily'];
        $count = count($dailyData['time']);

        for ($i = 0; $i < $count; $i++) {
            $avgTemp = $dailyData['temperature_2m_mean'][$i] ?? null;
            $maxTemp = $dailyData['temperature_2m_max'][$i] ?? null;
            $minTemp = $dailyData['temperature_2m_min'][$i] ?? null;

            if ($avgTemp === null) {
                continue; // Saltar días sin datos
            }

            // Calcular CDD y HDD (base 18°C)
            $cdd = max(0, $avgTemp - 18);
            $hdd = max(0, 18 - $avgTemp);

            $weatherData[] = [
                'date' => $dailyData['time'][$i],
                'avg_temp_celsius' => round($avgTemp, 1),
                'max_temp_celsius' => round($maxTemp ?? $avgTemp, 1),
                'min_temp_celsius' => round($minTemp ?? $avgTemp, 1),
                'cooling_degree_days' => round($cdd, 2),
                'heating_degree_days' => round($hdd, 2),
                'precipitation_mm' => round($dailyData['precipitation_sum'][$i] ?? 0, 1),
                'wind_speed_kmh' => round($dailyData['wind_speed_10m_max'][$i] ?? 0, 1),
                'humidity_percent' => null, // Open-Meteo no incluye humedad en plan gratuito
            ];
        }

        return [
            'success' => true,
            'message' => 'Datos obtenidos exitosamente desde Open-Meteo',
            'data' => $weatherData,
            'provider' => 'open-meteo',
        ];
    }

    /**
     * Visual Crossing API (1000 requests/día gratis)
     * Requiere API key: https://www.visualcrossing.com/sign-up
     */
    private function fetchFromVisualCrossing(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        $apiKey = config('services.visual_crossing.api_key');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'API key de Visual Crossing no configurada',
                'data' => [],
            ];
        }

        $location = "{$locality->latitude},{$locality->longitude}";
        $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/{$location}/{$startDate->format('Y-m-d')}/{$endDate->format('Y-m-d')}";

        $response = Http::timeout(30)->get($url, [
            'unitGroup' => 'metric',
            'key' => $apiKey,
            'include' => 'days',
            'elements' => 'datetime,tempmax,tempmin,temp,precip,humidity,windspeed',
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Error en Visual Crossing: ' . $response->status(),
                'data' => [],
            ];
        }

        $data = $response->json();
        $weatherData = [];

        foreach ($data['days'] ?? [] as $day) {
            $avgTemp = $day['temp'];
            $cdd = max(0, $avgTemp - 18);
            $hdd = max(0, 18 - $avgTemp);

            $weatherData[] = [
                'date' => $day['datetime'],
                'avg_temp_celsius' => round($avgTemp, 1),
                'max_temp_celsius' => round($day['tempmax'], 1),
                'min_temp_celsius' => round($day['tempmin'], 1),
                'cooling_degree_days' => round($cdd, 2),
                'heating_degree_days' => round($hdd, 2),
                'precipitation_mm' => round($day['precip'] ?? 0, 1),
                'wind_speed_kmh' => round(($day['windspeed'] ?? 0) * 1.60934, 1), // mph → km/h
                'humidity_percent' => $day['humidity'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Datos obtenidos desde Visual Crossing',
            'data' => $weatherData,
            'provider' => 'visual-crossing',
        ];
    }

    /**
     * WeatherAPI.com (1M requests/mes gratis)
     * Requiere API key: https://www.weatherapi.com/signup.aspx
     */
    private function fetchFromWeatherAPI(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        $apiKey = config('services.weatherapi.api_key');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'API key de WeatherAPI no configurada',
                'data' => [],
            ];
        }

        // WeatherAPI requiere llamadas diarias para históricos
        $weatherData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $url = 'http://api.weatherapi.com/v1/history.json';
            
            $response = Http::timeout(10)->get($url, [
                'key' => $apiKey,
                'q' => "{$locality->latitude},{$locality->longitude}",
                'dt' => $currentDate->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $day = $data['forecast']['forecastday'][0]['day'] ?? null;

                if ($day) {
                    $avgTemp = $day['avgtemp_c'];
                    $cdd = max(0, $avgTemp - 18);
                    $hdd = max(0, 18 - $avgTemp);

                    $weatherData[] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'avg_temp_celsius' => round($avgTemp, 1),
                        'max_temp_celsius' => round($day['maxtemp_c'], 1),
                        'min_temp_celsius' => round($day['mintemp_c'], 1),
                        'cooling_degree_days' => round($cdd, 2),
                        'heating_degree_days' => round($hdd, 2),
                        'precipitation_mm' => round($day['totalprecip_mm'] ?? 0, 1),
                        'wind_speed_kmh' => round($day['maxwind_kph'] ?? 0, 1),
                        'humidity_percent' => $day['avghumidity'] ?? null,
                    ];
                }
            }

            $currentDate->addDay();
            usleep(100000); // 100ms delay entre requests para no saturar
        }

        return [
            'success' => true,
            'message' => 'Datos obtenidos desde WeatherAPI',
            'data' => $weatherData,
            'provider' => 'weatherapi',
        ];
    }

    /**
     * Guarda los datos obtenidos en la base de datos
     * 
     * @param Locality $locality
     * @param array $weatherData
     * @return int Cantidad de registros insertados
     */
    public function saveWeatherData(Locality $locality, array $weatherData): int
    {
        $inserted = 0;

        foreach ($weatherData as $dayData) {
            DailyWeatherLog::updateOrCreate(
                [
                    'locality_id' => $locality->id,
                    'date' => $dayData['date'],
                ],
                [
                    'avg_temp_celsius' => $dayData['avg_temp_celsius'],
                    'max_temp_celsius' => $dayData['max_temp_celsius'],
                    'min_temp_celsius' => $dayData['min_temp_celsius'],
                    'cooling_degree_days' => $dayData['cooling_degree_days'],
                    'heating_degree_days' => $dayData['heating_degree_days'],
                    'precipitation_mm' => $dayData['precipitation_mm'],
                    'wind_speed_kmh' => $dayData['wind_speed_kmh'],
                    'humidity_percent' => $dayData['humidity_percent'],
                ]
            );
            
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Carga datos automáticamente para una factura
     */
    public function loadDataForInvoice(\App\Models\Invoice $invoice): array
    {
        $locality = $invoice->contract->supply->entity->locality;
        
        if (!$locality) {
            return [
                'success' => false,
                'message' => 'La entidad no tiene localidad asignada',
            ];
        }

        $result = $this->fetchHistoricalData($locality, $invoice->start_date, $invoice->end_date);
        
        if (!$result['success']) {
            return $result;
        }

        $inserted = $this->saveWeatherData($locality, $result['data']);

        return [
            'success' => true,
            'message' => "Cargados {$inserted} días de datos climáticos desde {$result['provider']}",
            'inserted' => $inserted,
            'provider' => $result['provider'],
        ];
    }
}
