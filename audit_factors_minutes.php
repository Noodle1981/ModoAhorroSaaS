<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Models\EntityEquipment;

$invoice = Invoice::find(1);
if (!$invoice) { echo "Factura 1 no encontrada\n"; exit; }
$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
$totalReal = $invoice->total_energy_consumed_kwh;

$snapshots = EquipmentUsageSnapshot::where('invoice_id',$invoice->id)
  ->with(['entityEquipment.equipmentType'])
  ->get();

if ($snapshots->isEmpty()) { echo "Sin snapshots para factura.\n"; exit; }

$rows = [];
foreach ($snapshots as $snap) {
  $eq = $snap->entityEquipment;
  $factorCarga = $eq->factor_carga ?? 1.0;
  $eficiencia = $eq->eficiencia ?? 1.0;
  $minDia = $snap->avg_daily_use_minutes;
  $power = $snap->power_watts;
  $kwh = $snap->calculated_kwh_period;
  $pctFactura = $totalReal > 0 ? ($kwh / $totalReal) * 100 : 0;
  $flags = [];
  if ($factorCarga > 0.6) $flags[] = 'F_CARGA_ALTO';
  if ($eficiencia < 0.95) $flags[] = 'EF_BAJA';
  if ($minDia > 480) $flags[] = 'MIN>480';
  if ($pctFactura > 50) $flags[] = '>%50_FACT';
  $rows[] = [
    'nombre' => $eq->custom_name ?? ($eq->equipmentType->name ?? 'Equipo'),
    'min' => $minDia,
    'power' => $power,
    'factor_carga' => $factorCarga,
    'eficiencia' => $eficiencia,
    'kwh' => $kwh,
    'pct_factura' => $pctFactura,
    'flags' => $flags,
  ];
}

usort($rows, fn($a,$b) => $b['kwh'] <=> $a['kwh']);

echo "=== AUDITORÍA FACTORES & MINUTOS (Factura #{$invoice->id}) ===\n";
echo "Período: {$periodDays} días | Consumo real: {$totalReal} kWh\n\n";
printf("%-30s %7s %7s %8s %9s %10s %9s %s\n","EQUIPO","MIN/D","POT(W)","F_CARGA","EFICIENCIA","KWH","%FACT","FLAGS");
echo str_repeat('-',110)."\n";
foreach ($rows as $r) {
  printf("%-30s %7d %7d %8.2f %9.2f %10.2f %8.1f %s\n",
    substr($r['nombre'],0,30),
    $r['min'], $r['power'], $r['factor_carga'], $r['eficiencia'], $r['kwh'], $r['pct_factura'], implode('|',$r['flags'])
  );
}

$topPct = array_sum(array_map(fn($r)=>$r['pct_factura'],$rows));
$topTwo = array_slice($rows,0,2);
$topTwoPct = array_sum(array_map(fn($r)=>$r['pct_factura'],$topTwo));

echo "\nResumen:\n";
$sumHighFlags = array_filter($rows, fn($r)=>in_array('F_CARGA_ALTO',$r['flags'])||in_array('EF_BAJA',$r['flags'])||in_array('MIN>480',$r['flags']));
echo "Equipos con alguna bandera: ".count($sumHighFlags)."/".count($rows)."\n";
echo "Top 2 equipos explican: ".number_format($topTwoPct,1)."% de la factura real\n";
if ($topTwoPct > 150) echo "⚠️  EXCESO: Top 2 superan 150% (sobreestimación severa)\n";
if ($topTwoPct > 90 && $topTwoPct <=150) echo "⚠️  Alta concentración (>90%)\n";

echo "\nSugerencias inmediatas:\n";
foreach ($topTwo as $t) {
  echo "- Revisar {$t['nombre']}: potencia declarada {$t['power']}W, minutos {$t['min']} min/d, F.carga {$t['factor_carga']}, eficiencia {$t['eficiencia']}\n";
  echo "  Ajuste prueba: min 360 y factor_carga 0.45 → reducción ≈ ".number_format($t['kwh'] * (360/$t['min']) * (0.45/$t['factor_carga']),2)." kWh estimado\n";
}

echo "\nPróximo paso: ejecutar script sensibilidad (pendiente).\n";
