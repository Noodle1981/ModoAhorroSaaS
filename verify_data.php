<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DE DATOS ===\n\n";

// Facturas
$invoiceCount = \App\Models\Invoice::count();
echo "Facturas totales: {$invoiceCount}\n";

if ($invoiceCount > 0) {
    $invoice = \App\Models\Invoice::first();
    echo "Primera factura ID: {$invoice->id}\n";
    echo "Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}\n";
    echo "Consumo: {$invoice->total_energy_consumed_kwh} kWh\n";
    echo "Monto: \${$invoice->total_amount}\n\n";
}

// Snapshots
$snapshotCount = \App\Models\EquipmentUsageSnapshot::count();
echo "Snapshots totales: {$snapshotCount}\n";

if ($snapshotCount > 0) {
    $snapshot = \App\Models\EquipmentUsageSnapshot::first();
    echo "Primer snapshot: Invoice #{$snapshot->invoice_id}, Status: {$snapshot->status}\n\n";
}

// Equipos
$equipmentCount = \App\Models\EntityEquipment::count();
echo "Equipos totales: {$equipmentCount}\n";

// Entidades
$entityCount = \App\Models\Entity::count();
echo "Entidades totales: {$entityCount}\n";

if ($entityCount > 0) {
    $entity = \App\Models\Entity::first();
    echo "Primera entidad ID: {$entity->id}, Nombre: {$entity->name}\n";
    
    // Verificar si tiene facturas relacionadas
    $invoicesForEntity = \App\Models\Invoice::whereHas('contract.supply.entity', function($q) use ($entity) {
        $q->where('id', $entity->id);
    })->count();
    
    echo "Facturas de entidad #{$entity->id}: {$invoicesForEntity}\n";
}
