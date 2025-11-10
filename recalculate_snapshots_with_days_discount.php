<?php
/**
 * Recalcula snapshots aplicando descuento automático del 25% de días
 * para categorías de Climatización y Calefón Eléctrico.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Services\EquipmentCalculationService;

$invoiceId = $argv[1] ?? null;
if (!$invoiceId) {
    echo "Uso: php recalculate_snapshots_with_days_discount.php {invoice_id}\n";
    exit(1);
}

$invoice = Invoice::with(['contract.supply.entity'])->find($invoiceId);
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

$service = app(EquipmentCalculationService::class);
$tariff = $service->calculateAverageTariff($invoice);

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

// Categorías con descuento de días
$categoriesWithDaysDiscount = ['Climatización', 'Calefón Eléctrico'];
$daysDiscountRatio = 0.75; // 25% de descuento

echo "\n=== RECALCULACIÓN DE SNAPSHOTS CON DESCUENTO AUTOMÁTICO DE DÍAS ===\n";
echo "Factura: #{$invoice->id} ({$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')})\n";
echo "Días período: {$periodDays}\n";
echo "Descuento aplicado: 25% (ratio 0.75) para Climatización y Calefón Eléctrico\n";
echo "Consumo real factura: {$invoice->total_energy_consumed_kwh} kWh\n\n";

$totalBefore = 0;
$totalAfter = 0;

$updates = [];

foreach ($snapshots as $snapshot) {
    $equipment = $snapshot->entityEquipment;
    $categoryName = $equipment->equipmentType?->equipmentCategory?->name ?? 'Sin categoría';
    
    // Determinar días efectivos
    $applyDiscount = in_array($categoryName, $categoriesWithDaysDiscount);
    $effectiveDays = $applyDiscount
        ? (int) max(1, round($periodDays * $daysDiscountRatio))
        : $periodDays;
    
    $oldKwh = $snapshot->calculated_kwh_period;
    $totalBefore += $oldKwh;
    
    // Temporalmente sobrescribir minutos
    $originalMinutes = $equipment->avg_daily_use_minutes_override;
    $equipment->avg_daily_use_minutes_override = $snapshot->avg_daily_use_minutes;
    
    // Recalcular con días efectivos
    $calc = $service->calculateEquipmentConsumption($equipment, $effectiveDays, $tariff);
    
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
        'applyDiscount' => $applyDiscount,
    ];
    
    echo sprintf(
        "%-30s | Categoría: %-20s | Días: %2d/%d %s | ANTES: %8.2f kWh | DESPUÉS: %8.2f kWh | Δ: %+7.2f kWh (%+.1f%%)\n",
        substr($equipment->custom_name ?? $equipment->equipmentType?->name ?? 'Equipo', 0, 28),
        substr($categoryName, 0, 18),
        $effectiveDays,
        $periodDays,
        $applyDiscount ? '(DESC)' : '      ',
        $oldKwh,
        $newKwh,
        $deltaKwh,
        $deltaPercent
    );
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
echo "\n--- TOP EQUIPOS POR REDUCCIÓN (DESC) ---\n";
$sorted = collect($updates)->sortByDesc(function($u) {
    return $u['snapshot']->calculated_kwh_period - $u['newKwh'];
});

foreach ($sorted->take(5) as $u) {
    $snapshot = $u['snapshot'];
    $equipment = $snapshot->entityEquipment;
    $reduction = $snapshot->calculated_kwh_period - $u['newKwh'];
    
    if ($reduction > 0.01) {
        echo sprintf(
            "%-30s | %s | Reducción: %.2f kWh (%.1f%%)\n",
            substr($equipment->custom_name ?? $equipment->equipmentType?->name ?? 'Equipo', 0, 28),
            $u['categoryName'],
            $reduction,
            ($reduction / max(0.01, $snapshot->calculated_kwh_period)) * 100
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
