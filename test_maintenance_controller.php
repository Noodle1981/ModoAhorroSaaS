<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simular lo que hace el controller
$user = App\Models\User::where('email', 'omardigital81@gmail.com')->first();

echo "=== SIMULACIÓN DEL CONTROLLER ===\n\n";
echo "Usuario: {$user->email}\n";
echo "Company ID: {$user->company_id}\n\n";

// Obtener equipos como lo hace el controller
$userEquipments = App\Models\EntityEquipment::whereHas('entity', function($query) use ($user) {
        $query->where('company_id', $user->company_id);
    })
    ->with(['equipmentType', 'entity'])
    ->get();

echo "Equipos encontrados: {$userEquipments->count()}\n\n";

if ($userEquipments->count() > 0) {
    $equipmentTypeIds = $userEquipments->pluck('equipment_type_id')->unique();
    echo "Equipment Type IDs únicos: " . $equipmentTypeIds->implode(', ') . "\n\n";
    
    $applicableTasks = App\Models\MaintenanceTask::whereIn('equipment_type_id', $equipmentTypeIds)->get();
    echo "Tareas aplicables encontradas: {$applicableTasks->count()}\n\n";
    
    if ($applicableTasks->count() > 0) {
        echo "Equipos CON tareas:\n";
        foreach ($userEquipments as $eq) {
            $tasks = $applicableTasks->where('equipment_type_id', $eq->equipment_type_id);
            if ($tasks->count() > 0) {
                echo "  ✅ {$eq->equipmentType->name} - {$tasks->count()} tarea(s)\n";
            }
        }
    }
} else {
    echo "❌ NO SE ENCONTRARON EQUIPOS\n";
    echo "\nVerificando entidades del usuario:\n";
    $entities = App\Models\Entity::where('company_id', $user->company_id)->get();
    echo "Entidades: {$entities->count()}\n";
    foreach ($entities as $entity) {
        echo "  - {$entity->name} (ID: {$entity->id})\n";
    }
}
