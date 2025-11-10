<?php
/**
 * Verifica datos climáticos y calcula días efectivos de uso de climatización
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\DailyWeatherLog;

$invoiceId = $argv[1] ?? 1;

$invoice = Invoice::with(['contract.supply.entity.locality'])->find($invoiceId);
if (!$invoice) {
    echo "Factura #{$invoiceId} no encontrada.\n";
    exit(1);
}

$locality = $invoice->contract->supply->entity->locality;
if (!$locality) {
    echo "La entidad no tiene localidad asignada.\n";
    exit(1);
}

echo "\n=== ANÁLISIS DE DÍAS EFECTIVOS POR TEMPERATURA ===\n";
echo "Factura: #{$invoice->id}\n";
echo "Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}\n";
echo "Localidad: {$locality->name}\n\n";

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;

// Obtener datos climáticos
$weather = DailyWeatherLog::where('locality_id', $locality->id)
    ->whereBetween('date', [$invoice->start_date, $invoice->end_date])
    ->selectRaw('
        COUNT(*) as total_days,
        SUM(CASE WHEN avg_temp_celsius > 24 THEN 1 ELSE 0 END) as dias_calor_24,
        SUM(CASE WHEN avg_temp_celsius > 26 THEN 1 ELSE 0 END) as dias_calor_26,
        SUM(CASE WHEN avg_temp_celsius > 28 THEN 1 ELSE 0 END) as dias_calor_28,
        SUM(CASE WHEN avg_temp_celsius < 18 THEN 1 ELSE 0 END) as dias_frio_18,
        SUM(CASE WHEN avg_temp_celsius < 16 THEN 1 ELSE 0 END) as dias_frio_16,
        SUM(CASE WHEN avg_temp_celsius BETWEEN 18 AND 24 THEN 1 ELSE 0 END) as dias_templados,
        AVG(avg_temp_celsius) as temp_media,
        MIN(avg_temp_celsius) as temp_min,
        MAX(avg_temp_celsius) as temp_max,
        SUM(cooling_degree_days) as total_cdd,
        SUM(heating_degree_days) as total_hdd
    ')
    ->first();

if (!$weather || $weather->total_days == 0) {
    echo "❌ No hay datos climáticos para este período.\n";
    exit(0);
}

echo "--- RESUMEN CLIMÁTICO ---\n";
echo "Días con datos: {$weather->total_days} / {$periodDays}\n";
echo "Temp media período: " . round($weather->temp_media, 1) . "°C\n";
echo "Rango: " . round($weather->temp_min, 1) . "°C - " . round($weather->temp_max, 1) . "°C\n";
echo "CDD total: " . round($weather->total_cdd, 1) . "\n";
echo "HDD total: " . round($weather->total_hdd, 1) . "\n\n";

echo "--- DISTRIBUCIÓN DE DÍAS POR TEMPERATURA ---\n";
echo "Días muy calurosos (>28°C):  {$weather->dias_calor_28} días (" . 
    round(($weather->dias_calor_28 / max(1, $weather->total_days)) * 100, 1) . "%)\n";
echo "Días calurosos (>26°C):      {$weather->dias_calor_26} días (" . 
    round(($weather->dias_calor_26 / max(1, $weather->total_days)) * 100, 1) . "%)\n";
echo "Días calor moderado (>24°C): {$weather->dias_calor_24} días (" . 
    round(($weather->dias_calor_24 / max(1, $weather->total_days)) * 100, 1) . "%)\n";
echo "Días templados (18-24°C):    {$weather->dias_templados} días (" . 
    round(($weather->dias_templados / max(1, $weather->total_days)) * 100, 1) . "%)\n";
echo "Días frescos (<18°C):        {$weather->dias_frio_18} días (" . 
    round(($weather->dias_frio_18 / max(1, $weather->total_days)) * 100, 1) . "%)\n";
echo "Días fríos (<16°C):          {$weather->dias_frio_16} días (" . 
    round(($weather->dias_frio_16 / max(1, $weather->total_days)) * 100, 1) . "%)\n\n";

// Calcular días efectivos sugeridos para climatización (refrigeración)
echo "--- DÍAS EFECTIVOS SUGERIDOS PARA CLIMATIZACIÓN ---\n\n";

// Umbral conservador: días donde probablemente usaste aire/ventilador
$umbralAire = 26; // >26°C casi seguro que usaste aire
$umbralVentilador = 24; // >24°C probablemente ventilador

$diasAire = $weather->dias_calor_26;
$diasVentilador = $weather->dias_calor_24;

echo "Escenario 1 - Conservador (>26°C para aires):\n";
echo "  Días efectivos aire: {$diasAire} / {$periodDays} (ratio: " . 
    round($diasAire / max(1, $periodDays), 2) . ")\n";
echo "  Descuento aplicado: " . round((1 - ($diasAire / max(1, $periodDays))) * 100, 1) . "%\n\n";

echo "Escenario 2 - Moderado (>24°C para ventiladores, >26°C para aires):\n";
echo "  Días efectivos ventilador: {$diasVentilador} / {$periodDays} (ratio: " . 
    round($diasVentilador / max(1, $periodDays), 2) . ")\n";
echo "  Días efectivos aire: {$diasAire} / {$periodDays} (ratio: " . 
    round($diasAire / max(1, $periodDays), 2) . ")\n";
echo "  Descuento ventilador: " . round((1 - ($diasVentilador / max(1, $periodDays))) * 100, 1) . "%\n";
echo "  Descuento aire: " . round((1 - ($diasAire / max(1, $periodDays))) * 100, 1) . "%\n\n";

echo "Escenario 3 - Agresivo (>28°C):\n";
echo "  Días efectivos: {$weather->dias_calor_28} / {$periodDays} (ratio: " . 
    round($weather->dias_calor_28 / max(1, $periodDays), 2) . ")\n";
echo "  Descuento aplicado: " . round((1 - ($weather->dias_calor_28 / max(1, $periodDays))) * 100, 1) . "%\n\n";

// Recomendación basada en CDD
$cddPerDay = $weather->total_cdd / max(1, $weather->total_days);
echo "--- RECOMENDACIÓN BASADA EN CDD ---\n";
echo "CDD promedio/día: " . round($cddPerDay, 2) . "\n";

if ($cddPerDay > 5) {
    echo "→ Período muy caluroso. Sugerir ratio aire: " . round($diasAire / max(1, $periodDays), 2) . "\n";
} elseif ($cddPerDay > 2) {
    echo "→ Período caluroso moderado. Sugerir ratio aire: " . 
        round(max(0.5, $diasAire / max(1, $periodDays)), 2) . "\n";
} else {
    echo "→ Período templado. Sugerir ratio ventilador: " . 
        round($diasVentilador / max(1, $periodDays), 2) . ", aire: 0.3-0.4\n";
}

echo "\n=== FIN ===\n";
