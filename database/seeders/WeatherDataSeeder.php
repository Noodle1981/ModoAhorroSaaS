<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Locality;
use App\Services\WeatherService;
use Carbon\Carbon;

class WeatherDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weatherService = new WeatherService();

        // Obtener Santa Lucía
        $santaLucia = Locality::where('name', 'Santa Lucía')->first();

        if (!$santaLucia) {
            $this->command->error('❌ No se encontró la localidad "Santa Lucía". Ejecuta LocalitiesTableSeeder primero.');
            return;
        }

        $this->command->info("🌤️  Cargando datos climáticos para {$santaLucia->name}...");

        // Cargar datos del último año (2024 completo + 2025 hasta hoy)
        $periods = [
            ['2024-01-01', '2024-12-31'],
            ['2025-01-01', Carbon::now()->format('Y-m-d')],
        ];

        foreach ($periods as [$startDate, $endDate]) {
            try {
                $this->command->info("  📅 Período: {$startDate} → {$endDate}");
                
                $stats = $weatherService->fetchAndStoreHistoricalWeather(
                    $santaLucia,
                    $startDate,
                    $endDate
                );

                $this->command->info("  ✅ Insertados: {$stats['inserted']} | Actualizados: {$stats['updated']} | Omitidos: {$stats['skipped']}");
                
                // Pausa entre requests para no saturar la API
                if ($periods[count($periods) - 1] !== [$startDate, $endDate]) {
                    sleep(1);
                }

            } catch (\Exception $e) {
                $this->command->error("  ❌ Error: {$e->getMessage()}");
            }
        }

        $this->command->newLine();
        $this->command->info('✨ Datos climáticos cargados correctamente');
        
        // Mostrar ejemplo de datos cargados
        $examplePeriod = $weatherService->getAverageTemperatureForPeriod(
            $santaLucia,
            '2024-01-01',
            '2024-01-31'
        );

        if ($examplePeriod) {
            $this->command->info("📊 Ejemplo - Enero 2024:");
            $this->command->info("   Temp. Promedio: {$examplePeriod['avg_temp']}°C");
            $this->command->info("   Temp. Máxima: {$examplePeriod['max_temp']}°C");
            $this->command->info("   Temp. Mínima: {$examplePeriod['min_temp']}°C");
            $this->command->info("   Grados-día refrigeración: {$examplePeriod['cooling_degree_days']}");
        }
    }
}
