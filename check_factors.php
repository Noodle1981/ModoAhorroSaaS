<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$equipments = \App\Models\EntityEquipment::with('equipmentType')->take(5)->get();

echo "Verificando factores asignados:\n\n";

foreach ($equipments as $eq) {
    echo "Equipo: " . ($eq->custom_name ?? $eq->equipmentType->name ?? 'Sin nombre') . "\n";
    echo "  tipo_de_proceso: " . ($eq->tipo_de_proceso ?? 'NULL') . "\n";
    echo "  factor_carga: " . ($eq->factor_carga ?? 'NULL') . "\n";
    echo "  eficiencia: " . ($eq->eficiencia ?? 'NULL') . "\n";
    echo "\n";
}
