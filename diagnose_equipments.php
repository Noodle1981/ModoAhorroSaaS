<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Entity;
use App\Services\InventoryAnalysisService;

$entity = Entity::first();

if (!$entity) {
    echo "No hay entidades en la base de datos\n";
    exit(1);
}

echo "DIAGNÓSTICO DE EQUIPOS - {$entity->name}\n";
echo str_repeat("=", 80) . "\n\n";

// ✅ USAR SERVICIO OFICIAL EN LUGAR DE CÁLCULO MANUAL
$service = app(InventoryAnalysisService::class);
$monthlyProfile = $service->calculateEnergyProfileForPeriod($entity, 30);

echo sprintf("%-40s %8s %10s %12s\n", "Equipo", "Potencia", "Uso/día", "kWh/mes");
echo str_repeat("-", 80) . "\n";

$totalMonthly = 0;

foreach($monthlyProfile as $eq) {
    $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
    $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
    $monthlyKwh = $eq->consumo_kwh_total_periodo; // ✅ Usar cálculo del servicio
    $totalMonthly += $monthlyKwh;
    
    $name = $eq->custom_name ?? $eq->equipmentType->name;
    $name = substr($name, 0, 38);
    
    echo sprintf(
        "%-40s %6dW %8d min %10.2f kWh",
        $name,
        $power,
        $minutes,
        $monthlyKwh
    );
    
    // Mostrar desglose si tiene standby
    if ($eq->consumo_kwh_standby_periodo > 0) {
        echo sprintf(" (Activo: %.2f + Standby: %.2f)", 
            $eq->consumo_kwh_activo_periodo, 
            $eq->consumo_kwh_standby_periodo
        );
    }
    
    echo "\n";
}

echo str_repeat("-", 80) . "\n";
echo sprintf("%-40s %8s %10s %10.2f kWh\n", "TOTAL MENSUAL ESTIMADO", "", "", $totalMonthly);
echo "\n";

// Obtener consumo real de última factura
$lastInvoice = \App\Models\Invoice::whereHas('contract', function($q) use ($entity) {
    $q->whereHas('supply', function($q2) use ($entity) {
        $q2->where('entity_id', $entity->id);
    });
})->orderBy('end_date', 'desc')->first();

if ($lastInvoice) {
    $realKwh = $lastInvoice->total_energy_consumed_kwh;
    $periodDays = $lastInvoice->start_date->diffInDays($lastInvoice->end_date) + 1;
    $realMonthly = ($realKwh / $periodDays) * 30;
    
    echo "CONSUMO REAL (última factura):\n";
    echo "  Período: {$lastInvoice->start_date->format('d/m/Y')} - {$lastInvoice->end_date->format('d/m/Y')} ({$periodDays} días)\n";
    echo "  Total: {$realKwh} kWh\n";
    echo "  Promedio mensual: " . number_format($realMonthly, 2) . " kWh\n\n";
    
    $diff = $totalMonthly - $realMonthly;
    $percent = ($totalMonthly / $realMonthly) * 100;
    
    echo "DIFERENCIA:\n";
    echo "  Estimado - Real: " . number_format($diff, 2) . " kWh\n";
    echo "  Porcentaje: " . number_format($percent, 1) . "%\n\n";
    
    if ($percent > 150) {
        echo "⚠️  SOBRESTIMACIÓN GRAVE (>150%)\n";
        echo "Posibles causas:\n";
        echo "  - Tiempos de uso muy altos\n";
        echo "  - Potencias incorrectas\n";
        echo "  - Equipos duplicados o que no existen\n";
    } elseif ($percent > 120) {
        echo "⚠️  Sobrestimación moderada (120-150%)\n";
    } elseif ($percent < 80) {
        echo "⚠️  Subestimación - faltan equipos\n";
    } else {
        echo "✅ Estimación razonable (80-120%)\n";
    }
}
