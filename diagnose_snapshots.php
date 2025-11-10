<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\EquipmentUsageSnapshot;

echo "=== DIAGNÓSTICO DE SNAPSHOTS DUPLICADOS ===\n\n";

$invoice = Invoice::find(1);
$entity = $invoice->contract->supply->entity;

echo "Entidad: {$entity->name}\n";
echo "Factura ID: {$invoice->id}\n";
echo "Período: {$invoice->start_date} - {$invoice->end_date}\n\n";

// Contar equipos reales de la entidad
$equipmentsCount = EntityEquipment::where('entity_id', $entity->id)->count();
echo "Total de equipos en inventario: {$equipmentsCount}\n\n";

// Contar snapshots activos
$activeSnapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
    ->whereNull('deleted_at')
    ->get();

echo "Total de snapshots activos: {$activeSnapshots->count()}\n\n";

// Verificar duplicados por entity_equipment_id
$grouped = $activeSnapshots->groupBy('entity_equipment_id');
$duplicates = $grouped->filter(function($group) {
    return $group->count() > 1;
});

if ($duplicates->count() > 0) {
    echo "⚠️  DUPLICADOS ENCONTRADOS:\n\n";
    foreach ($duplicates as $equipmentId => $snapshots) {
        $equipment = EntityEquipment::find($equipmentId);
        $equipmentName = $equipment ? ($equipment->custom_name ?? $equipment->equipmentType->name) : "Equipo #{$equipmentId}";
        
        echo "  {$equipmentName} (ID: {$equipmentId}): {$snapshots->count()} snapshots\n";
        foreach ($snapshots as $snap) {
            echo "    - Snapshot ID {$snap->id}: {$snap->calculated_kwh_period} kWh, creado: {$snap->created_at}\n";
        }
        echo "\n";
    }
    
    echo "CAUSA: El frontend está enviando equipos duplicados O el store() se ejecutó múltiples veces.\n\n";
} else {
    echo "✓ No hay duplicados. Cada equipo tiene 1 solo snapshot.\n\n";
}

// Verificar si la suma es correcta
$totalKwh = $activeSnapshots->sum('calculated_kwh_period');
echo "Suma total de snapshots activos: {$totalKwh} kWh\n";
echo "Consumo real de factura: {$invoice->total_energy_consumed_kwh} kWh\n";
echo "Nivel de acierto: " . number_format(($totalKwh / $invoice->total_energy_consumed_kwh) * 100, 1) . "%\n\n";

echo "=== SOLUCIÓN ===\n";
if ($duplicates->count() > 0) {
    echo "1. Limpiar snapshots duplicados manualmente.\n";
    echo "2. Verificar que el formulario no envíe equipos duplicados.\n";
    echo "3. Agregar validación en store() para prevenir duplicados.\n";
} else {
    echo "Los snapshots están bien. El problema puede estar en otro lado.\n";
}

echo "\n=== FIN ===\n";
