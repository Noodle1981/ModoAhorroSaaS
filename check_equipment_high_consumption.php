<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;

echo "=== EQUIPOS CON CONSUMO > 100 kWh ===\n\n";

$invoice = Invoice::find(1);
$periodDays = 65;

$snapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
    ->with('entityEquipment.equipmentType')
    ->get()
    ->sortByDesc('calculated_kwh_period');

foreach ($snapshots as $snapshot) {
    if ($snapshot->calculated_kwh_period > 100) {
        $equipment = $snapshot->entityEquipment;
        $type = $equipment->equipmentType;
        
        $name = $equipment->custom_name ?? $type->name;
        $power = $snapshot->power_watts;
        $minutes = $snapshot->avg_daily_use_minutes;
        $kwh = $snapshot->calculated_kwh_period;
        
        // Obtener factores
        $factorCarga = $equipment->factor_carga ?? 1.0;
        $eficiencia = $equipment->eficiencia ?? 1.0;
        
        // Calcular manualmente
        $horasPeriodo = ($minutes * $periodDays) / 60;
        $kwhSinFactores = ($power / 1000) * $horasPeriodo;
        $kwhConFactores = $kwhSinFactores * $factorCarga / $eficiencia;
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔴 {$name}\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  Potencia:        {$power} W\n";
        echo "  Minutos/día:     {$minutes} min\n";
        echo "  Horas totales:   " . number_format($horasPeriodo, 1) . " h ({$periodDays} días)\n";
        echo "  Factor carga:    {$factorCarga}\n";
        echo "  Eficiencia:      {$eficiencia}\n";
        echo "\n";
        echo "  📊 CÁLCULO:\n";
        echo "     Sin factores: " . number_format($kwhSinFactores, 2) . " kWh\n";
        echo "     × F.Carga:    " . number_format($kwhSinFactores * $factorCarga, 2) . " kWh\n";
        echo "     ÷ Eficiencia: " . number_format($kwhConFactores, 2) . " kWh\n";
        echo "     = GUARDADO:   " . number_format($kwh, 2) . " kWh\n";
        echo "\n";
        
        // Verificar si la potencia es razonable
        if ($power > 3000) {
            echo "  ⚠️  POTENCIA MUY ALTA (>{$power}W)\n";
        }
        if ($minutes > 600) {
            echo "  ⚠️  USO MUY INTENSIVO (>{$minutes} min/día = " . round($minutes/60, 1) . " horas/día)\n";
        }
        if ($eficiencia < 0.7) {
            echo "  ⚠️  EFICIENCIA MUY BAJA (<{$eficiencia})\n";
        }
        
        echo "\n";
    }
}

echo "\n💡 Para corregir:\n";
echo "1. Revisá la potencia en el inventario de equipos\n";
echo "2. Verificá los factores de carga y eficiencia\n";
echo "3. Ajustá los minutos de uso diario si es necesario\n\n";
