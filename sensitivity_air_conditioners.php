<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;

$invoice = Invoice::find(1);
if (!$invoice) { echo "Factura 1 no encontrada\n"; exit; }
$days = $invoice->start_date->diffInDays($invoice->end_date) + 1;
$totalReal = (float)($invoice->total_energy_consumed_kwh ?? 0);

$snaps = EquipmentUsageSnapshot::where('invoice_id',$invoice->id)
  ->with('entityEquipment.equipmentType')
  ->get();

$acSnaps = $snaps->filter(function($s){
  $name = strtolower($s->entityEquipment->custom_name ?? ($s->entityEquipment->equipmentType->name ?? ''));
  return str_contains($name,'aire') || str_contains($name,'acondicion');
});

if ($acSnaps->isEmpty()) { echo "No se encontraron aires en snapshots.\n"; exit; }

$otherKwh = $snaps->sum('calculated_kwh_period') - $acSnaps->sum('calculated_kwh_period');

$gridMinutes = [240, 300, 360, 420, 480];
$gridLoad = [0.35, 0.45, 0.55, 0.70];
$gridEff = [0.90, 1.00];

// Usamos potencia declarada por snapshot para cada AC
$acData = $acSnaps->map(function($s){
  $eq = $s->entityEquipment;
  return [
    'name' => $eq->custom_name ?? ($eq->equipmentType->name ?? 'Aire'),
    'power' => (int)$s->power_watts,
    'factor_carga' => (float)($eq->factor_carga ?? 1.0),
    'eficiencia' => (float)($eq->eficiencia ?? 1.0),
    'quantity' => max(1, (int)($eq->quantity ?? 1)),
  ];
})->values()->all();

function ac_kwh($powerW,$minutes,$days,$load,$eff,$qty){
  $hours = ($minutes * $days)/60.0;
  return ($powerW/1000.0) * $hours * $load / ($eff ?: 1.0) * $qty;
}

echo "=== SENSIBILIDAD PARA AIRES (Factura #{$invoice->id}, {$days} días, Real: {$totalReal} kWh) ===\n\n";

echo "Aires considerados:\n";
foreach ($acData as $i=>$ac) {
  echo "  - ".($i+1).": {$ac['name']} | Pot: {$ac['power']}W | F.carga: {$ac['factor_carga']} | Efic.: {$ac['eficiencia']} | Qty: {$ac['quantity']}\n";
}

echo "\nTabla (min/día, f_carga, eficiencia) → kWh_aires | kWh_total | %Factura\n";
echo str_repeat('-', 90)."\n";

foreach ($gridMinutes as $m) {
  foreach ($gridLoad as $lf) {
    foreach ($gridEff as $ef) {
      $sumAires = 0.0;
      foreach ($acData as $ac) {
        $sumAires += ac_kwh($ac['power'], $m, $days, $lf, $ef, $ac['quantity']);
      }
      $kwhTotal = $sumAires + $otherKwh;
      $pct = $totalReal > 0 ? ($kwhTotal / $totalReal) * 100 : 0;
      printf("min=%3d, f=%.2f, ef=%.2f → aires=%8.1f | total=%8.1f | %6.1f%%\n", $m, $lf, $ef, $sumAires, $kwhTotal, $pct);
    }
  }
}

// Adicional: variación de potencia para primer aire
$first = $acData[0];
$testPowers = [800, 900, 1000, 1100, 1200, 1300];
$testMinutes = [300, 360, 420];

echo "\nVariación de potencia para '{$first['name']}' (manteniendo segundo aire y resto fijos)\n";
echo str_repeat('-', 90)."\n";
foreach ($testMinutes as $m) {
  foreach ($testPowers as $pw) {
    // Recalcular con primer aire alterado, segundo con su power original
    $sumAires = 0.0;
    foreach ($acData as $idx=>$ac) {
      $p = ($idx==0) ? $pw : $ac['power'];
      $sumAires += ac_kwh($p, $m, $days, 0.45, 0.95, $ac['quantity']);
    }
    $kwhTotal = $sumAires + $otherKwh;
    $pct = $totalReal > 0 ? ($kwhTotal / $totalReal) * 100 : 0;
    printf("min=%3d, P=%4dW, f=0.45, ef=0.95 → aires=%8.1f | total=%8.1f | %6.1f%%\n", $m, $pw, $sumAires, $kwhTotal, $pct);
  }
}
