<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE MANTENIMIENTO ===\n\n";

// 1. Equipos del usuario
$user = App\Models\User::where('email', 'omardigital81@gmail.com')->first();
echo "Usuario: {$user->email} (Company ID: {$user->company_id})\n\n";

$equipments = App\Models\EntityEquipment::whereHas('entity', function($q) use ($user) {
    $q->where('company_id', $user->company_id);
})->with(['equipmentType', 'entity'])->get();

echo "📦 Equipos encontrados: {$equipments->count()}\n\n";

foreach ($equipments->take(5) as $eq) {
    echo "  - {$eq->equipmentType->name} en {$eq->entity->name}\n";
    echo "    Equipment Type ID: {$eq->equipment_type_id}\n";
}

// 2. Tareas de mantenimiento
echo "\n🔧 Tareas de Mantenimiento:\n";
$tasks = App\Models\MaintenanceTask::all();
echo "  Total: {$tasks->count()}\n\n";

foreach ($tasks as $task) {
    echo "  - {$task->task_name} (Equipment Type ID: {$task->equipment_type_id})\n";
}

// 3. Verificar si hay match
echo "\n🎯 Match entre equipos y tareas:\n";
$equipmentTypeIds = $equipments->pluck('equipment_type_id')->unique();
$taskTypeIds = $tasks->pluck('equipment_type_id')->unique();

echo "  Equipment Type IDs de tus equipos: " . $equipmentTypeIds->implode(', ') . "\n";
echo "  Equipment Type IDs con tareas: " . $taskTypeIds->implode(', ') . "\n";

$matches = $equipmentTypeIds->intersect($taskTypeIds);
echo "  IDs en común: " . ($matches->count() > 0 ? $matches->implode(', ') : 'NINGUNO ❌') . "\n";
