<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$invoice = \App\Models\Invoice::first();
$entity = $invoice->contract->supply->entity;
$equipments = $entity->equipments()->with(['equipmentType.equipmentCategory.calculationFactor'])->get();

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
$calculationService = app(\App\Services\EquipmentCalculationService::class);
$tariff = $calculationService->calculateAverageTariff($invoice);

echo "=== DEBUG CÁLCULO ===\n";
echo "Período: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')} ({$periodDays} días)\n";
echo "Consumo REAL factura: {$invoice->total_energy_consumed_kwh} kWh\n";
echo "Tarifa: " . round($tariff, 2) . " $/kWh\n\n";

$bulkCalc = $calculationService->calculateBulkConsumption($equipments, $periodDays, $tariff);

echo "Consumo CALCULADO: {$bulkCalc['total_kwh']} kWh\n";
echo "Precisión: " . round(($bulkCalc['total_kwh'] / $invoice->total_energy_consumed_kwh) * 100, 1) . "%\n\n";

echo "=== DETALLE POR EQUIPO (primeros 5) ===\n";
foreach (array_slice($bulkCalc['equipments_detail'], 0, 5) as $detail) {
    $eq = $equipments->firstWhere('id', $detail['equipment_id']);
    echo "\n{$detail['nombre']}:\n";
    echo "  Tipo proceso: {$detail['tipo_de_proceso']}\n";
    echo "  Factor carga: {$eq->factor_carga}\n";
    echo "  Eficiencia: {$eq->eficiencia}\n";
    echo "  kWh calculado: {$detail['calculation']['kwh_total']} kWh\n";
}

echo "\n=== PROBLEMA IDENTIFICADO ===\n";
echo "Si estamos calculando 123%, significa que:\n";
echo "1. Los factores están AUMENTANDO el consumo (no disminuyendo)\n";
echo "2. O las horas/potencias están sobrestimadas\n";
echo "3. O hay standby contabilizándose incorrectamente\n";
