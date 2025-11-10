<?php
/**
 * Recalcula snapshots usando ClimateCorrelationService para días efectivos
 * basados en temperatura real del período
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Services\EquipmentCalculationService;
use App\Services\ClimateCorrelationService;

$invoiceId = $argv[1] ?? null;
if (!$invoiceId) {
    echo "Uso: php recalculate_snapshots_climate.php {invoice_id}\n";
    exit(1);
}

$invoice = Invoice::with(['contract.supply.entity.locality'])->find($invoiceId);
if (!$invoice) {
    echo "Factura #{$invoiceId} no encontrada.\n";
    exit(1);
}

$snapshots = EquipmentUsageSnapshot::with(['entityEquipment.equipmentType.equipmentCategory'])
    ->where('invoice_id', $invoiceId)
    ->get();

if ($snapshots->isEmpty()) {
    echo "No hay snapshots para esta factura.\n";
    exit(0);
}

$calculationService = app(EquipmentCalculationService::class);
$climateService = app(ClimateCorrelationService::class);
$tariff = $calculationService->calculateAverageTariff($invoice);

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

echo "\n=== RECALCULACIÓN DE SNAPSHOTS CON DÍAS EFECTIVOS POR CLIMA ===\n";
echo "Factura: #{$invoice->id} ({$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')})\n";
echo "Días período: {$periodDays}\n";
echo "Consumo real factura: {$invoice->total_energy_consumed_kwh} kWh\n";
echo "Localidad: " . ($invoice->contract->supply->entity->locality?->name ?? 'Sin localidad') . "\n\n";

$categoriesWithClimateAdjustment = ['Climatización', 'Calefón Eléctrico', 'Calefacción'];
$effectiveDaysCache = [];

$totalBefore = 0;
$totalAfter = 0;
$updates = [];

foreach ($snapshots as $snapshot) {
    $equipment = $snapshot->entityEquipment;
    $categoryName = $equipment->equipmentType?->equipmentCategory?->name ?? 'Sin categoría';
    
    // Determinar días efectivos usando servicio de clima
    $applyClimateAdjustment = in_array($categoryName, $categoriesWithClimateAdjustment);
    
    if ($applyClimateAdjustment) {
        if (!isset($effectiveDaysCache[$categoryName])) {
            $climateData = $climateService->getEffectiveDaysForClimateEquipment($invoice, $categoryName);
            $effectiveDaysCache[$categoryName] = $climateData;
        }
        $effectiveDays = $effectiveDaysCache[$categoryName]['effective_days'];
        $dataSource = $effectiveDaysCache[$categoryName]['data_source'];
    } else {
        $effectiveDays = $periodDays;
        $dataSource = 'full_period';
    }
    
    $oldKwh = $snapshot->calculated_kwh_period;
    $totalBefore += $oldKwh;
    
    // Temporalmente sobrescribir minutos
    $originalMinutes = $equipment->avg_daily_use_minutes_override;
    $equipment->avg_daily_use_minutes_override = $snapshot->avg_daily_use_minutes;
    
    // Recalcular con días efectivos climáticos
    $calc = $calculationService->calculateEquipmentConsumption($equipment, $effectiveDays, $tariff);
    
    // Restaurar
    $equipment->avg_daily_use_minutes_override = $originalMinutes;
    
    $newKwh = $calc['kwh_total'];
    $totalAfter += $newKwh;
    
    $deltaKwh = $newKwh - $oldKwh;
    $deltaPercent = $oldKwh > 0 ? (($deltaKwh / $oldKwh) * 100) : 0;
    
    $updates[] = [
        'snapshot' => $snapshot,
        'newKwh' => $newKwh,
        'categoryName' => $categoryName,
        'effectiveDays' => $effectiveDays,
        'dataSource' => $dataSource,
        'applyClimateAdjustment' => $applyClimateAdjustment,
    ];
    
    $climateLabel = $applyClimateAdjustment ? "({$dataSource})" : '';
    
    echo sprintf(
        "%-30s | %-20s | Días: %2d/%d %-20s | ANTES: %8.2f | DESPUÉS: %8.2f | Δ: %+7.2f kWh (%+.1f%%)\n",
        substr($equipment->custom_name ?? $equipment->equipmentType?->name ?? 'Equipo', 0, 28),
        substr($categoryName, 0, 18),
        $effectiveDays,
        $periodDays,
        substr($climateLabel, 0, 18),
        $oldKwh,
        $newKwh,
        $deltaKwh,
        $deltaPercent
    );
}

echo "\n--- RESUMEN DE AJUSTES CLIMÁTICOS ---\n";
foreach ($effectiveDaysCache as $category => $data) {
    echo "\n{$category}:\n";
    echo "  Fuente de datos: {$data['data_source']}\n";
    echo "  Días efectivos: {$data['effective_days']} / {$data['total_days']} (ratio: " . 
        number_format($data['ratio'], 2) . ")\n";
    echo "  Descuento aplicado: " . number_format((1 - $data['ratio']) * 100, 1) . "%\n";
    
    if ($data['data_source'] === 'weather_logs') {
        echo "  Umbral usado: {$data['threshold']}°C\n";
        echo "  Días con datos: {$data['days_with_data']}\n";
    } elseif ($data['data_source'] === 'seasonal_estimate') {
        echo "  (Estimación estacional - sin datos climáticos reales)\n";
    }
}

echo "\n--- TOTALES ---\n";
echo "ANTES del recálculo: " . number_format($totalBefore, 2) . " kWh\n";
echo "DESPUÉS del recálculo: " . number_format($totalAfter, 2) . " kWh\n";
echo "Delta: " . number_format($totalAfter - $totalBefore, 2) . " kWh (" . 
    number_format((($totalAfter - $totalBefore) / max(1, $totalBefore)) * 100, 1) . "%)\n\n";

$realKwh = (float) $invoice->total_energy_consumed_kwh;
if ($realKwh > 0) {
    $percentBefore = ($totalBefore / $realKwh) * 100;
    $percentAfter = ($totalAfter / $realKwh) * 100;
    
    echo "Explicado ANTES: " . number_format($percentBefore, 1) . "%\n";
    echo "Explicado DESPUÉS: " . number_format($percentAfter, 1) . "%\n";
    echo "Mejora: " . number_format($percentBefore - $percentAfter, 1) . " puntos porcentuales\n\n";
}

// Listar equipos por impacto de reducción
echo "\n--- TOP EQUIPOS POR REDUCCIÓN ---\n";
$sorted = collect($updates)->sortByDesc(function($u) {
    return $u['snapshot']->calculated_kwh_period - $u['newKwh'];
});

foreach ($sorted->take(5) as $u) {
    $snapshot = $u['snapshot'];
    $equipment = $snapshot->entityEquipment;
    $reduction = $snapshot->calculated_kwh_period - $u['newKwh'];
    
    if ($reduction > 0.01) {
        echo sprintf(
            "%-30s | %s | Reducción: %.2f kWh (%.1f%%) | %s\n",
            substr($equipment->custom_name ?? $equipment->equipmentType?->name ?? 'Equipo', 0, 28),
            $u['categoryName'],
            $reduction,
            ($reduction / max(0.01, $snapshot->calculated_kwh_period)) * 100,
            $u['dataSource']
        );
    }
}

// Aplicar actualizaciones
echo "\n¿Aplicar estos cambios a la base de datos? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) === 'y') {
    foreach ($updates as $u) {
        $u['snapshot']->update([
            'calculated_kwh_period' => $u['newKwh'],
        ]);
    }
    echo "✓ Snapshots actualizados.\n";
} else {
    echo "✗ No se aplicaron cambios.\n";
}

echo "\n=== FIN ===\n";
