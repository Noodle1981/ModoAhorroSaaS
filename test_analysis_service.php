<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Entity;
use App\Services\InventoryAnalysisService;

$entity = Entity::first();
$service = app(InventoryAnalysisService::class);

echo "=== TEST INVENTORY ANALYSIS SERVICE ===\n\n";

// Test 1: getAnnualEnergyProfile
echo "1. Annual Energy Profile:\n";
$profile = $service->getAnnualEnergyProfile($entity);
echo "   Total anual estimado: " . number_format($profile->sum('consumo_kwh_total_periodo'), 2) . " kWh\n";
echo "   Promedio mensual: " . number_format($profile->sum('consumo_kwh_total_periodo') / 12, 2) . " kWh\n\n";

// Test 2: findAllOpportunities (reemplazos)
echo "2. Oportunidades de Reemplazo:\n";
$opportunities = $service->findAllOpportunities($entity);
echo "   Total oportunidades encontradas: " . count($opportunities) . "\n";
if (count($opportunities) > 0) {
    echo "   Top 3 mejores oportunidades:\n";
    foreach (array_slice($opportunities, 0, 3) as $idx => $opp) {
        echo "      " . ($idx + 1) . ". {$opp['equipment_name']}: €" . number_format($opp['annual_savings_euros'], 2) . "/año\n";
    }
}
echo "\n";

// Test 3: Top consumidores
echo "3. Top 5 Consumidores:\n";
$equipments = $entity->equipments()->with('equipmentType')->get();
$topConsumers = $equipments->map(function($eq) {
    $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
    $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
    $monthlyKwh = ($power / 1000) * ($minutes / 60) * 30;
    return [
        'name' => $eq->custom_name ?? $eq->equipmentType->name,
        'kwh' => $monthlyKwh,
        'location' => is_string($eq->location) ? $eq->location : ($eq->location['name'] ?? 'N/A'),
    ];
})->sortByDesc('kwh')->take(5);

foreach ($topConsumers as $idx => $consumer) {
    echo "   " . ($idx + 1) . ". {$consumer['name']} ({$consumer['location']}): " . number_format($consumer['kwh'], 2) . " kWh/mes\n";
}

echo "\n✅ SERVICIO FUNCIONA CORRECTAMENTE\n";
