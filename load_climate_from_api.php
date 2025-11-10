<?php
/**
 * Carga datos climáticos REALES desde Open-Meteo API (gratuita)
 * para el período de una factura
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Services\WeatherApiService;

$invoiceId = $argv[1] ?? null;

if (!$invoiceId) {
    echo "Uso: php load_climate_from_api.php {invoice_id}\n\n";
    echo "Este script carga datos climáticos REALES desde Open-Meteo (gratuita).\n";
    echo "No requiere API key y no tiene límites de uso.\n\n";
    exit(1);
}

$invoice = Invoice::with(['contract.supply.entity.locality'])->find($invoiceId);

if (!$invoice) {
    echo "Factura #{$invoiceId} no encontrada.\n";
    exit(1);
}

$locality = $invoice->contract->supply->entity->locality;

if (!$locality) {
    echo "❌ La entidad no tiene localidad asignada.\n";
    exit(1);
}

if (!$locality->latitude || !$locality->longitude) {
    echo "❌ La localidad '{$locality->name}' no tiene coordenadas GPS.\n";
    echo "Por favor, actualiza la localidad con:\n";
    echo "  - Latitud y Longitud\n";
    exit(1);
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║     CARGA DE DATOS CLIMÁTICOS DESDE OPEN-METEO API        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📋 Factura: #{$invoice->id}\n";
echo "📅 Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}\n";
echo "📍 Localidad: {$locality->name}\n";
echo "🌍 Coordenadas: {$locality->latitude}, {$locality->longitude}\n";
echo "🔗 API: Open-Meteo (gratuita, sin límites)\n\n";

// Verificar si ya existen datos
$existingCount = \App\Models\DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->count();

if ($existingCount > 0) {
    echo "⚠️  Ya existen {$existingCount} días de datos climáticos para este período.\n";
    echo "¿Deseas reemplazarlos? (y/n): ";
    
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if (strtolower($line) !== 'y') {
        echo "Cancelado.\n";
        exit(0);
    }
    
    // Eliminar datos existentes
    \App\Models\DailyWeatherLog::where('locality_id', $locality->id)
        ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
        ->delete();
    
    echo "✓ Datos anteriores eliminados.\n\n";
}

echo "Conectando con Open-Meteo API...\n";

$weatherApiService = new WeatherApiService();
$result = $weatherApiService->loadDataForInvoice($invoice);

if (!$result['success']) {
    echo "\n❌ ERROR: {$result['message']}\n\n";
    exit(1);
}

echo "\n✅ {$result['message']}\n\n";

// Mostrar estadísticas
$stats = \App\Models\DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->selectRaw('
        COUNT(*) as total_days,
        AVG(avg_temp_celsius) as temp_media,
        MIN(avg_temp_celsius) as temp_min,
        MAX(avg_temp_celsius) as temp_max,
        SUM(cooling_degree_days) as total_cdd,
        SUM(heating_degree_days) as total_hdd,
        SUM(CASE WHEN avg_temp_celsius > 24 THEN 1 ELSE 0 END) as dias_24,
        SUM(CASE WHEN avg_temp_celsius > 26 THEN 1 ELSE 0 END) as dias_26,
        SUM(CASE WHEN avg_temp_celsius > 28 THEN 1 ELSE 0 END) as dias_28,
        SUM(CASE WHEN avg_temp_celsius < 18 THEN 1 ELSE 0 END) as dias_18,
        SUM(CASE WHEN avg_temp_celsius < 16 THEN 1 ELSE 0 END) as dias_16
    ')
    ->first();

echo "═══════════════════════════════════════════════════════════\n";
echo "               ESTADÍSTICAS CLIMÁTICAS REALES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Días con datos: {$stats->total_days}\n";
echo "Temperatura media: " . round($stats->temp_media, 1) . "°C\n";
echo "Rango: " . round($stats->temp_min, 1) . "°C - " . round($stats->temp_max, 1) . "°C\n";
echo "CDD total (refrigeración): " . round($stats->total_cdd, 1) . "\n";
echo "HDD total (calefacción): " . round($stats->total_hdd, 1) . "\n\n";

echo "--- DISTRIBUCIÓN DE DÍAS ---\n\n";

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

echo "Refrigeración:\n";
echo "  Días >28°C (calor intenso): {$stats->dias_28} (" . round(($stats->dias_28 / $periodDays) * 100, 1) . "%)\n";
echo "  Días >26°C (uso de A/A):    {$stats->dias_26} (" . round(($stats->dias_26 / $periodDays) * 100, 1) . "%)\n";
echo "  Días >24°C (ventiladores):  {$stats->dias_24} (" . round(($stats->dias_24 / $periodDays) * 100, 1) . "%)\n\n";

echo "Calefacción:\n";
echo "  Días <18°C (fresco):        {$stats->dias_18} (" . round(($stats->dias_18 / $periodDays) * 100, 1) . "%)\n";
echo "  Días <16°C (frío):          {$stats->dias_16} (" . round(($stats->dias_16 / $periodDays) * 100, 1) . "%)\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "            DÍAS EFECTIVOS CALCULADOS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$ratioVentiladores = $stats->dias_24 / max(1, $periodDays);
$ratioAires = $stats->dias_26 / max(1, $periodDays);
$ratioCalefaccion = $stats->dias_18 / max(1, $periodDays);

echo "Ventiladores (>24°C):    {$stats->dias_24} días efectivos (ratio: " . round($ratioVentiladores, 2) . ")\n";
echo "Aires Acondicionados:    {$stats->dias_26} días efectivos (ratio: " . round($ratioAires, 2) . ")\n";
echo "Calefacción (<18°C):     {$stats->dias_18} días efectivos (ratio: " . round($ratioCalefaccion, 2) . ")\n\n";

$descuentoVentiladores = (1 - $ratioVentiladores) * 100;
$descuentoAires = (1 - $ratioAires) * 100;

echo "Descuento automático:\n";
echo "  Ventiladores: -" . round($descuentoVentiladores, 1) . "%\n";
echo "  Aires: -" . round($descuentoAires, 1) . "%\n\n";

echo "═══════════════════════════════════════════════════════════\n\n";

echo "💡 PRÓXIMO PASO:\n";
echo "   Recalcular snapshots con datos reales:\n\n";
echo "   php recalculate_snapshots_climate.php {$invoiceId}\n\n";

echo "═══════════════════════════════════════════════════════════\n\n";
