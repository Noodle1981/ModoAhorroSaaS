<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Locality;
use App\Services\WeatherService;
use Carbon\Carbon;

class UpdateWeatherData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:update 
                            {--locality= : Nombre de la localidad específica (opcional)} 
                            {--start= : Fecha de inicio (Y-m-d, por defecto: primer día del mes pasado)}
                            {--end= : Fecha de fin (Y-m-d, por defecto: hoy)}
                            {--all : Actualizar todas las localidades con coordenadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza datos climáticos históricos desde Open-Meteo para localidades con coordenadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weatherService = new WeatherService();

        // Determinar fechas
        $startDate = $this->option('start') ?? Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endDate = $this->option('end') ?? Carbon::now()->format('Y-m-d');

        $this->info("🌤️  Actualizando datos climáticos: {$startDate} → {$endDate}");
        $this->newLine();

        // Obtener localidades
        if ($this->option('all')) {
            $localities = Locality::whereNotNull('latitude')->whereNotNull('longitude')->get();
            $this->info("📍 Actualizando {$localities->count()} localidades con coordenadas...");
        } elseif ($localityName = $this->option('locality')) {
            $locality = Locality::where('name', $localityName)->first();
            if (!$locality) {
                $this->error("❌ No se encontró la localidad '{$localityName}'");
                return 1;
            }
            if (!$locality->latitude || !$locality->longitude) {
                $this->error("❌ La localidad '{$localityName}' no tiene coordenadas definidas");
                return 1;
            }
            $localities = collect([$locality]);
        } else {
            $this->error("❌ Debes especificar --locality=NombreLocalidad o --all");
            return 1;
        }

        // Procesar cada localidad
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalErrors = 0;

        foreach ($localities as $locality) {
            $this->info("  📍 Procesando: {$locality->name} ({$locality->latitude}, {$locality->longitude})");

            try {
                $stats = $weatherService->fetchAndStoreHistoricalWeather($locality, $startDate, $endDate);
                $totalInserted += $stats['inserted'];
                $totalUpdated += $stats['updated'];
                $this->line("     ✅ Insertados: {$stats['inserted']} | Actualizados: {$stats['updated']}");

                // Pausa entre requests para no saturar la API
                if ($localities->count() > 1) {
                    sleep(1);
                }

            } catch (\Exception $e) {
                $totalErrors++;
                $this->error("     ❌ Error: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✨ Proceso completado:");
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Localidades procesadas', $localities->count()],
                ['Registros insertados', $totalInserted],
                ['Registros actualizados', $totalUpdated],
                ['Errores', $totalErrors],
            ]
        );

        return 0;
    }
}
