<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Services\WeatherService;

echo "=== TEST CLIMATE SNAPSHOT CREATION ===\n\n";

$invoice = Invoice::first();
echo "Factura #1: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}\n";

$entity = $invoice->contract->supply->entity;
$locality = $entity->locality;

echo "Entity: {$entity->name}\n";
echo "Locality: {$locality->name} ({$locality->latitude}, {$locality->longitude})\n\n";

$weatherService = app(WeatherService::class);

try {
    echo "1. Sincronizando datos diarios de Open-Meteo API...\n";
    $stats = $weatherService->fetchAndStoreHistoricalWeather(
        $locality,
        $invoice->start_date->format('Y-m-d'),
        $invoice->end_date->format('Y-m-d')
    );
    print_r($stats);
    echo "\n";
} catch (\Exception $e) {
    echo "ERROR en API: " . $e->getMessage() . "\n\n";
}

try {
    echo "2. Creando ClimateSnapshot...\n";
    $snapshot = $weatherService->createClimateSnapshot(
        $entity,
        $invoice->start_date,
        $invoice->end_date
    );
    
    echo "   ✅ Snapshot ID: {$snapshot->id}\n";
    echo "   📊 Período: {$snapshot->getFormattedPeriod()}\n";
    echo "   🌡️  Temp avg: {$snapshot->avg_temperature_c}°C\n";
    echo "   🔥 CDD: {$snapshot->total_cooling_degree_days}\n";
    echo "   ❄️  HDD: {$snapshot->total_heating_degree_days}\n";
    echo "   📁 Source: {$snapshot->data_source}\n";
    echo "   🏷️  Category: {$snapshot->getClimateCategoryLabel()}\n";
    
} catch (\Exception $e) {
    echo "ERROR creando snapshot: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
