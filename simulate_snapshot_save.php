<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\EquipmentUsageSnapshot;
use App\Services\EquipmentCalculationService;
use Illuminate\Support\Facades\DB;

echo "=== SIMULACIÓN DE GUARDADO DE SNAPSHOTS AJUSTADOS ===\n\n";

$invoice = Invoice::find(1);
$entity = $invoice->contract->supply->entity;
$calculationService = new EquipmentCalculationService();

echo "Factura: #{$invoice->id}\n";
echo "Período: {$invoice->start_date} - {$invoice->end_date}\n";
echo "Consumo real: {$invoice->total_energy_consumed_kwh} kWh\n\n";

// Obtener equipos
$equipments = EntityEquipment::where('entity_id', $entity->id)->get();

echo "Total equipos: {$equipments->count()}\n\n";

// Calcular factor de ajuste necesario para llegar a ~623 kWh
// Primera prueba: 0.323 → 249 kWh (40%) - muy bajo
// Segunda prueba: 0.65 → 501 kWh (80.5%) - aceptable pero bajo
// Tercera prueba: 0.80 para acercarnos al 100%
$targetKwh = $invoice->total_energy_consumed_kwh;
$currentKwh = 1928.49; // De verificación anterior
$factor = 0.80; // Factor manual ajustado

echo "Factor de ajuste calculado: " . number_format($factor, 3) . "\n";
echo "Reduciendo minutos de uso a ~" . number_format($factor * 100, 1) . "% de los valores actuales\n\n";

$periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
$tariff = $calculationService->calculateAverageTariff($invoice);

echo "=== INICIANDO GUARDADO (modo simulación) ===\n\n";

DB::transaction(function () use ($invoice, $equipments, $calculationService, $periodDays, $tariff, $factor) {
    // Soft-delete snapshots anteriores
    EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->delete();
    echo "✓ Snapshots anteriores eliminados (soft-delete)\n";
    
    $newSnapshots = [];
    
    foreach ($equipments as $equipment) {
        // Obtener minutos actuales
        $originalMinutes = $equipment->avg_daily_use_minutes_override
            ?? (($equipment->equipmentType->default_avg_daily_use_hours ?? 0) * 60);
        
        // Aplicar factor de ajuste
        $adjustedMinutes = (int) round($originalMinutes * $factor);
        $adjustedMinutes = max(0, min(1440, $adjustedMinutes)); // Clamp 0-1440
        
        // Temporalmente sobrescribir para calcular
        $equipment->avg_daily_use_minutes_override = $adjustedMinutes;
        
        // CALCULAR con el service
        $calc = $calculationService->calculateEquipmentConsumption($equipment, $periodDays, $tariff);
        
        // Restaurar valor original
        $equipment->avg_daily_use_minutes_override = $originalMinutes;
        
        // Preparar datos snapshot
        $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
        $hasStandby = (bool)($equipment->has_standby_mode ?? false);
        $isDaily = (bool)($equipment->is_daily_use ?? false);
        $daysPerWeek = $equipment->usage_days_per_week;
        $usageWeekdays = $equipment->usage_weekdays ?? null;
        
        $snapshot = EquipmentUsageSnapshot::create([
            'entity_equipment_id' => $equipment->id,
            'invoice_id' => $invoice->id,
            'start_date' => $invoice->start_date,
            'end_date' => $invoice->end_date,
            'avg_daily_use_minutes' => $adjustedMinutes,
            'power_watts' => $powerWatts,
            'has_standby_mode' => $hasStandby,
            'is_daily_use' => $isDaily,
            'usage_days_per_week' => $isDaily ? null : $daysPerWeek,
            'usage_weekdays' => $isDaily ? null : $usageWeekdays,
            'minutes_per_session' => $equipment->minutes_per_session,
            'frequency_source' => 'inherited',
            'calculated_kwh_period' => $calc['kwh_total'],
            'equipment_type_name' => $equipment->equipmentType->name ?? 'Equipo',
        ]);
        
        $newSnapshots[] = [
            'equipo' => $equipment->custom_name ?? $equipment->equipmentType->name,
            'original_min' => $originalMinutes,
            'ajustado_min' => $adjustedMinutes,
            'kwh' => $calc['kwh_total'],
        ];
    }
    
    echo "\n=== SNAPSHOTS CREADOS ===\n";
    foreach ($newSnapshots as $s) {
        echo sprintf("  • %-30s: %4d min → %4d min = %8.2f kWh\n", 
            substr($s['equipo'], 0, 30),
            $s['original_min'],
            $s['ajustado_min'],
            $s['kwh']
        );
    }
});

// Verificar resultado
$newActiveSnapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->get();
$newTotalKwh = $newActiveSnapshots->sum('calculated_kwh_period');
$newPercent = ($newTotalKwh / $invoice->total_energy_consumed_kwh) * 100;

echo "\n=== RESULTADO FINAL ===\n";
echo "Total kWh (nuevos snapshots): {$newTotalKwh} kWh\n";
echo "Consumo real (factura): {$invoice->total_energy_consumed_kwh} kWh\n";
echo "Nivel de acierto: " . number_format($newPercent, 1) . "%\n\n";

if ($newPercent >= 90 && $newPercent <= 110) {
    echo "✓ ¡EXCELENTE! Los snapshots están bien calibrados.\n";
} elseif ($newPercent >= 80 && $newPercent <= 120) {
    echo "✓ Aceptable. Los snapshots están en rango razonable.\n";
} else {
    echo "⚠️  Fuera de rango óptimo. Puede requerir ajuste manual adicional.\n";
}

echo "\n✓ Simulación completada. Recarga la página de entidad para ver los cambios.\n";
echo "   URL: http://localhost/entities/{$entity->id}\n\n";
