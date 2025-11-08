<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Creando tareas de mantenimiento para TODOS los aires acondicionados...\n\n";

// Buscar TODOS los tipos de aire acondicionado
$acTypes = App\Models\EquipmentType::where('name', 'like', '%Aire%Acondicionado%')->get();

echo "✅ Tipos de aire encontrados: {$acTypes->count()}\n";
foreach ($acTypes as $ac) {
    echo "  - ID {$ac->id}: {$ac->name}\n";
}

foreach ($acTypes as $ac) {
    // Tarea 1: Limpieza de filtro
    $task1 = App\Models\MaintenanceTask::updateOrCreate([
        'equipment_type_id' => $ac->id,
        'name' => 'Limpieza de filtro',
    ], [
        'description' => 'Remover y limpiar filtro para mejorar eficiencia y calidad del aire.',
        'recommended_frequency_days' => 30,
        'recommended_season' => 'all',
        'maintenance_type' => 'filter_clean',
    ]);

    // Tarea 2: Limpieza profunda
    $task2 = App\Models\MaintenanceTask::updateOrCreate([
        'equipment_type_id' => $ac->id,
        'name' => 'Limpieza profunda',
    ], [
        'description' => 'Limpieza profunda de serpentinas y bandeja de drenaje.',
        'recommended_frequency_days' => 180,
        'recommended_season' => 'all',
        'maintenance_type' => 'deep_clean',
    ]);

    echo "\n✅ Tareas creadas para: {$ac->name}\n";
    echo "   - Limpieza de filtro (cada 30 días)\n";
    echo "   - Limpieza profunda (cada 180 días)\n";
}

echo "\n🎯 ¡Listo! Ahora recarga http://127.0.0.1:8000/maintenance\n";
