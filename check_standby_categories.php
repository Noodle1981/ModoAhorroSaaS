<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Categorías con standby activado ===\n";
$categories = \App\Models\EquipmentCategory::where('supports_standby', true)
    ->orderBy('id')
    ->get(['id', 'name', 'supports_standby']);

foreach ($categories as $cat) {
    echo sprintf("ID %d: %s (supports_standby: %s)\n", $cat->id, $cat->name, $cat->supports_standby ? 'true' : 'false');
}

echo "\n=== PC y Monitores en Entretenimiento ===\n";
$pcMonitors = \App\Models\EquipmentType::where('category_id', 5)
    ->where(function($q) {
        $q->where('name', 'like', '%PC%')
          ->orWhere('name', 'like', '%Monitor%');
    })
    ->get(['id', 'name', 'category_id', 'standby_power_watts']);

foreach ($pcMonitors as $eq) {
    echo sprintf("ID %d: %s (cat: %d, standby: %.1fW)\n", $eq->id, $eq->name, $eq->category_id, $eq->standby_power_watts);
}
