<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$eq = App\Models\EntityEquipment::with('equipmentType')
    ->where('entity_id', 1)
    ->whereHas('equipmentType', function($q) {
        $q->where('name', 'LIKE', '%Lavarropas%');
    })
    ->first();

if ($eq) {
    echo "✅ Lavarropa encontrado:\n";
    echo "  Tipo: {$eq->equipmentType->name}\n";
    echo "  is_daily_use: " . ($eq->is_daily_use ? 'true' : 'false') . "\n";
    echo "  usage_days_per_week: {$eq->usage_days_per_week}\n";
    echo "  usage_weekdays: " . json_encode($eq->usage_weekdays) . "\n";
    echo "  minutes_per_session: {$eq->minutes_per_session}\n";
    echo "  avg_daily_use_minutes_override: {$eq->avg_daily_use_minutes_override}\n";
    
    // Calcular derivado esperado
    $expected = round(($eq->minutes_per_session * $eq->usage_days_per_week) / 7);
    echo "\n📊 Promedio derivado esperado: {$expected} min/día\n";
    
    if ($eq->avg_daily_use_minutes_override == $expected) {
        echo "✅ Promedio diario coincide con el derivado!\n";
    } else {
        echo "⚠️ Promedio diario NO coincide (actual: {$eq->avg_daily_use_minutes_override}, esperado: {$expected})\n";
    }
} else {
    echo "❌ No se encontró el lavarropas\n";
}
