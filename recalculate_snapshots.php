<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Models\EntityEquipment;
use App\Services\EquipmentCalculationService;
use Illuminate\Support\Facades\DB;

$invoiceId = (int)($_SERVER['argv'][1] ?? 1);

$invoice = Invoice::find($invoiceId);
if (!$invoice) { echo "Factura {$invoiceId} no encontrada\n"; exit(1); }
$entity = $invoice->contract->supply->entity;

$days = $invoice->start_date->diffInDays($invoice->end_date) + 1;
$service = new EquipmentCalculationService();
$tariff = $service->calculateAverageTariff($invoice);

$snapshots = EquipmentUsageSnapshot::where('invoice_id',$invoice->id)
  ->with('entityEquipment.equipmentType')
  ->get();

if ($snapshots->isEmpty()) { echo "No hay snapshots para esta factura.\n"; exit(0); }

echo "=== RE-CÁLCULO DE SNAPSHOTS (Factura #{$invoice->id}) ===\n";
echo "Período: {$days} días | Tarifa: ".number_format($tariff,2)." $/kWh\n\n";

$totalBefore = $snapshots->sum('calculated_kwh_period');

DB::transaction(function() use ($snapshots, $service, $days, $tariff) {
  foreach ($snapshots as $snap) {
    /** @var EntityEquipment $eq */
    $eq = $snap->entityEquipment;
    if (!$eq) continue;

    // Guardar valores originales
    $origMinutes = $eq->avg_daily_use_minutes_override;
    $origPower = $eq->power_watts_override;
    $origStandby = $eq->has_standby_mode;

    // Aplicar minutos y potencia del snapshot para el cálculo
    $eq->avg_daily_use_minutes_override = (int)$snap->avg_daily_use_minutes;
    if (!empty($snap->power_watts)) {
      $eq->power_watts_override = (int)$snap->power_watts;
    }
    $eq->has_standby_mode = (bool)$snap->has_standby_mode;

    $calc = $service->calculateEquipmentConsumption($eq, $days, $tariff);

    // Restaurar valores originales
    $eq->avg_daily_use_minutes_override = $origMinutes;
    $eq->power_watts_override = $origPower;
    $eq->has_standby_mode = $origStandby;

    // Actualizar snapshot
    $snap->calculated_kwh_period = $calc['kwh_total'];
    $snap->save();
  }
});

$updated = EquipmentUsageSnapshot::where('invoice_id',$invoice->id)->get();
$totalAfter = $updated->sum('calculated_kwh_period');
$real = (float)($invoice->total_energy_consumed_kwh ?? 0);

echo "Total ANTES: ".number_format($totalBefore,2)." kWh (".($real>0?number_format(($totalBefore/$real)*100,1):'n/a')."%)\n";
echo "Total DESPUÉS: ".number_format($totalAfter,2)." kWh (".($real>0?number_format(($totalAfter/$real)*100,1):'n/a')."%)\n";

echo "\nDetalle top 5:\n";
$top = $updated->sortByDesc('calculated_kwh_period')->take(5);
foreach ($top as $snap) {
  $name = $snap->entityEquipment?->custom_name ?? $snap->entityEquipment?->equipmentType?->name ?? 'Equipo';
  echo "  - {$name}: ".number_format($snap->calculated_kwh_period,2)." kWh (".$snap->avg_daily_use_minutes." min/d)\n";
}

