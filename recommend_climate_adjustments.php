<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Models\DailyWeatherLog;

$invoice = Invoice::find(1);
if (!$invoice) { echo "Factura 1 no encontrada\n"; exit; }
$entity = $invoice->contract->supply->entity;
$locality = $entity->locality;
if (!$locality) { echo "Entidad sin localidad\n"; exit; }

$days = $invoice->start_date->diffInDays($invoice->end_date) + 1;

// Obtener CDD/HDD del período
$weather = DailyWeatherLog::where('locality_id',$locality->id)
  ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
  ->selectRaw('SUM(cooling_degree_days) as cdd, SUM(heating_degree_days) as hdd, AVG(avg_temp_celsius) as tavg')
  ->first();

$cdd = (float)($weather->cdd ?? 0);
$hdd = (float)($weather->hdd ?? 0);
$tavg = (float)($weather->tavg ?? 0);

// Heurísticas para estimar minutos razonables de AC según CDD:
// asumimos: minutos_recomendados = base (120–180) + (cdd_factor * CDD_diario_promedio)
// CDD diario promedio = cdd / días. Ajuste por tipo (split vs portátil)

$snapshots = EquipmentUsageSnapshot::where('invoice_id',$invoice->id)
  ->with('entityEquipment.equipmentType')
  ->get();

$acSnaps = $snapshots->filter(function($s){
  $n = strtolower($s->entityEquipment->custom_name ?? ($s->entityEquipment->equipmentType->name ?? ''));
  return str_contains($n,'aire');
});

if ($acSnaps->isEmpty()) { echo "No hay aires para analizar.\n"; exit; }

$avgCddPerDay = $days > 0 ? $cdd / $days : 0; // típicamente 0–10

echo "=== RECOMENDACIONES CLIMÁTICAS (Factura #{$invoice->id}) ===\n";
echo "Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')} ({$days} días)\n";
echo "Temp prom: ".number_format($tavg,1)."°C | CDD totales: ".number_format($cdd,1)." | CDD/día: ".number_format($avgCddPerDay,2)."\n\n";

echo str_pad('EQUIPO',30).str_pad('MIN ACT',8).str_pad('KWH ACT',10).str_pad('MIN SUG',10).str_pad('DELTA MIN',10).str_pad('KWH EST NUEVO',15)."NOTA\n";
echo str_repeat('-',100)."\n";

$TOTAL_NEW_KWH = 0; $TOTAL_CURRENT_KWH = 0;

foreach ($acSnaps as $snap) {
  $eq = $snap->entityEquipment;
  $name = substr($eq->custom_name ?? ($eq->equipmentType->name ?? 'Aire'),0,28);
  $currentMin = $snap->avg_daily_use_minutes;
  $currentKwh = $snap->calculated_kwh_period;
  $power = $snap->power_watts;
  $factorCarga = $eq->factor_carga ?? 0.7;
  $ef = $eq->eficiencia ?? 0.9;
  $qty = max(1,(int)$eq->quantity);

  // Heurística base: 150 min + (avgCddPerDay * 25) (cap 600) distinta para portátil (más ineficiente puede usar +10%)
  $base = 150 + ($avgCddPerDay * 25);
  if (str_contains(strtolower($name),'portátil')) { $base *= 1.1; }
  $suggestedMin = (int)min(600, round($base / 5) * 5); // múltiplo de 5

  // Recalcular kWh estimado con suggestedMin (misma potencia/factores)
  $hours = ($suggestedMin * $days)/60.0;
  $newKwh = ($power/1000.0) * $hours * $factorCarga / ($ef ?: 1.0) * $qty;

  $deltaMin = $suggestedMin - $currentMin;
  $TOTAL_CURRENT_KWH += $currentKwh;
  $TOTAL_NEW_KWH += $newKwh;

  $nota = '';
  if ($suggestedMin < $currentMin) $nota = 'Reducir';
  elseif ($suggestedMin > $currentMin) $nota = 'Aumentar';
  else $nota = 'Ok';

  printf("%-30s %8d %10.1f %10d %10d %15.1f %s\n", $name, $currentMin, $currentKwh, $suggestedMin, $deltaMin, $newKwh, $nota);
}

$other = $snapshots->sum('calculated_kwh_period') - $acSnaps->sum('calculated_kwh_period');
$newTotal = $TOTAL_NEW_KWH + $other;
$currentTotal = $snapshots->sum('calculated_kwh_period');

$real = $invoice->total_energy_consumed_kwh;

echo "\nTOTAL ACTUAL SNAPSHOTS: ".number_format($currentTotal,1)." kWh (".number_format(($currentTotal/$real)*100,1)."% real)\n";
echo "TOTAL NUEVO (Ajuste climático a aires): ".number_format($newTotal,1)." kWh (".number_format(($newTotal/$real)*100,1)."% real)\n";
$diffPct = ($newTotal/$currentTotal -1)*100;
echo "Variación total vs actual: ".number_format($diffPct,1)."%\n";

if ($newTotal > $real*1.15) echo "⚠ Sigue alto (>115% real). Requiere bajar factor_carga o potencia efectiva.\n";
elseif ($newTotal < $real*0.85) echo "⚠ Demasiado bajo (<85% real). Revisar si subestimamos minutos.\n";
else echo "✓ Dentro del rango razonable (85%-115%).\n";
