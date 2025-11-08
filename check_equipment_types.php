<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$types = App\Models\EquipmentType::whereIn('id', [1, 24, 38, 45])->get(['id', 'name']);

foreach ($types as $type) {
    echo "{$type->id}: {$type->name}\n";
}
