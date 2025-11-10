<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Models\EntityEquipment;

echo "=== MINUTOS GUARDADOS EN SNAPSHOTS ===\n\n";

$invoice = Invoice::find(1);

echo "Factura: #{$invoice->id}\n";
echo "Período: {$invoice->start_date} - {$invoice->end_date}\n\n";

$snapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
    ->with('entityEquipment.equipmentType')
    ->get();

echo "Total snapshots: {$snapshots->count()}\n\n";

echo str_pad("EQUIPO", 35) . str_pad("MIN/DÍA", 12) . str_pad("kWh", 12) . "ORIGEN\n";
echo str_repeat("-", 80) . "\n";

foreach ($snapshots as $snapshot) {
    $equipment = $snapshot->entityEquipment;
    $name = $equipment->custom_name ?? ($equipment->equipmentType->name ?? 'Equipo');
    
    // Comparar con el valor por defecto del equipo
    $defaultMinutes = $equipment->avg_daily_use_minutes_override
        ?? (($equipment->equipmentType->default_avg_daily_use_hours ?? 0) * 60);
    
    $origin = ($snapshot->avg_daily_use_minutes == $defaultMinutes) ? 'DEFAULT' : 'AJUSTADO';
    
    echo str_pad(substr($name, 0, 34), 35) 
         . str_pad($snapshot->avg_daily_use_minutes . ' min', 12)
         . str_pad(number_format($snapshot->calculated_kwh_period, 2) . ' kWh', 12)
         . $origin . "\n";
}

$totalKwh = $snapshots->sum('calculated_kwh_period');
echo "\n" . str_repeat("-", 80) . "\n";
echo "TOTAL: " . number_format($totalKwh, 2) . " kWh\n";
echo "REAL:  " . number_format($invoice->total_energy_consumed_kwh, 2) . " kWh\n";
echo "NIVEL: " . number_format(($totalKwh / $invoice->total_energy_consumed_kwh) * 100, 1) . "%\n\n";
