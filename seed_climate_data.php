<?php
/**
 * Seed de datos climáticos de ejemplo para el período de la factura
 * Simula temperaturas reales para demostrar el cálculo de días efectivos
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\DailyWeatherLog;
use Carbon\Carbon;

$invoiceId = $argv[1] ?? 1;

$invoice = Invoice::with(['contract.supply.entity.locality'])->find($invoiceId);
if (!$invoice) {
    echo "Factura #{$invoiceId} no encontrada.\n";
    exit(1);
}

$locality = $invoice->contract->supply->entity->locality;
if (!$locality) {
    echo "La entidad no tiene localidad asignada.\n";
    exit(1);
}

echo "\n=== SEED DE DATOS CLIMÁTICOS DE EJEMPLO ===\n";
echo "Factura: #{$invoice->id}\n";
echo "Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}\n";
echo "Localidad: {$locality->name}\n\n";

// Eliminar datos anteriores del período
DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->delete();

$currentDate = $invoice->start_date->copy();
$endDate = $invoice->end_date;

$diasCreados = 0;
$diasCalor = 0;
$diasTemplados = 0;
$diasFrescos = 0;

echo "Generando datos climáticos realistas para verano (enero-marzo)...\n\n";

while ($currentDate <= $endDate) {
    // Simular temperaturas de verano en Uruguay/Argentina
    // Enero: muy caluroso, Febrero: caluroso, Marzo: templado
    $mes = $currentDate->month;
    
    if ($mes == 1) {
        // Enero: muy caluroso
        $tempBase = 28;
        $variacion = rand(-4, 6);
    } elseif ($mes == 2) {
        // Febrero: caluroso
        $tempBase = 27;
        $variacion = rand(-5, 5);
    } else {
        // Marzo: templado (otoño)
        $tempBase = 23;
        $variacion = rand(-4, 4);
    }
    
    $avgTemp = $tempBase + $variacion;
    $maxTemp = $avgTemp + rand(3, 8);
    $minTemp = $avgTemp - rand(3, 6);
    
    // Calcular CDD y HDD (base 18°C)
    $cdd = max(0, $avgTemp - 18);
    $hdd = max(0, 18 - $avgTemp);
    
    DailyWeatherLog::create([
        'locality_id' => $locality->id,
        'date' => $currentDate->format('Y-m-d'),
        'avg_temp_celsius' => $avgTemp,
        'max_temp_celsius' => $maxTemp,
        'min_temp_celsius' => $minTemp,
        'cooling_degree_days' => round($cdd, 2),
        'heating_degree_days' => round($hdd, 2),
        'precipitation_mm' => rand(0, 10) / 10, // Poca lluvia en verano
        'humidity_percent' => rand(50, 80),
        'wind_speed_kmh' => rand(5, 25),
    ]);
    
    $diasCreados++;
    
    if ($avgTemp > 26) {
        $diasCalor++;
    } elseif ($avgTemp > 22) {
        $diasTemplados++;
    } else {
        $diasFrescos++;
    }
    
    $currentDate->addDay();
}

echo "✓ Creados {$diasCreados} registros climáticos\n\n";

echo "--- RESUMEN ---\n";
echo "Días muy calurosos (>26°C): {$diasCalor} (" . round(($diasCalor / $diasCreados) * 100, 1) . "%)\n";
echo "Días templados (22-26°C): {$diasTemplados} (" . round(($diasTemplados / $diasCreados) * 100, 1) . "%)\n";
echo "Días frescos (<22°C): {$diasFrescos} (" . round(($diasFrescos / $diasCreados) * 100, 1) . "%)\n\n";

// Calcular estadísticas
$stats = DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->selectRaw('
        AVG(avg_temp_celsius) as temp_media,
        MIN(avg_temp_celsius) as temp_min,
        MAX(avg_temp_celsius) as temp_max,
        SUM(cooling_degree_days) as total_cdd,
        SUM(heating_degree_days) as total_hdd,
        SUM(CASE WHEN avg_temp_celsius > 24 THEN 1 ELSE 0 END) as dias_24,
        SUM(CASE WHEN avg_temp_celsius > 26 THEN 1 ELSE 0 END) as dias_26,
        SUM(CASE WHEN avg_temp_celsius > 28 THEN 1 ELSE 0 END) as dias_28
    ')
    ->first();

echo "Temperatura media del período: " . round($stats->temp_media, 1) . "°C\n";
echo "Rango: " . round($stats->temp_min, 1) . "°C - " . round($stats->temp_max, 1) . "°C\n";
echo "CDD total: " . round($stats->total_cdd, 1) . "\n";
echo "HDD total: " . round($stats->total_hdd, 1) . "\n\n";

echo "--- DÍAS EFECTIVOS PARA CLIMATIZACIÓN ---\n";
echo "Días >24°C (ventiladores): {$stats->dias_24} / {$diasCreados} (ratio: " . 
    round($stats->dias_24 / $diasCreados, 2) . ")\n";
echo "Días >26°C (aires): {$stats->dias_26} / {$diasCreados} (ratio: " . 
    round($stats->dias_26 / $diasCreados, 2) . ")\n";
echo "Días >28°C (calor intenso): {$stats->dias_28} / {$diasCreados} (ratio: " . 
    round($stats->dias_28 / $diasCreados, 2) . ")\n\n";

echo "💡 Ahora puedes ejecutar:\n";
echo "   php recalculate_snapshots_climate.php {$invoiceId}\n";
echo "   para recalcular con datos climáticos reales.\n\n";

echo "=== FIN ===\n";
