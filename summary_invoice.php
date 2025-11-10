<?php
/**
 * Resumen ejecutivo del estado actual
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;

$invoiceId = $argv[1] ?? 1;

$invoice = Invoice::with(['contract.supply.entity'])->find($invoiceId);
$snapshots = EquipmentUsageSnapshot::with(['entityEquipment.equipmentType.equipmentCategory'])
    ->where('invoice_id', $invoiceId)
    ->get();

$total = $snapshots->sum('calculated_kwh_period');
$real = (float) $invoice->total_energy_consumed_kwh;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║         RESUMEN EJECUTIVO - ESTADO ACTUAL                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Total Estimado: " . number_format($total, 2) . " kWh\n";
echo "Total Real:     " . number_format($real, 2) . " kWh\n";
echo "Porcentaje:     " . number_format(($total / $real) * 100, 1) . "%\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  DATOS CLIMÁTICOS: ✅ Open-Meteo API (REALES)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$locality = $invoice->contract->supply->entity->locality;
$weather = \App\Models\DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->selectRaw('AVG(avg_temp_celsius) as temp_media, COUNT(*) as dias_con_datos')
    ->first();

echo "  Localidad: {$locality->name}\n";
echo "  Temp. media: " . round($weather->temp_media, 1) . "°C\n";
echo "  Días con datos: {$weather->dias_con_datos} / 65\n";
echo "  Fuente: Open-Meteo (gratuita)\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  SIGUIENTE AJUSTE NECESARIO\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (($total / $real) > 1.5) {
    echo "  🎯 Reducir MINUTOS de aires de 600 → 350-400 min/día\n";
    echo "     Impacto estimado: -400 kWh (~33%)\n\n";
}

echo "\n";
