<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::find(3);

if ($invoice) {
    echo "Factura 3 existe\n";
    echo "Entity ID: " . $invoice->contract->supply->entity_id . "\n";
    echo "Equipos en entidad: " . $invoice->contract->supply->entity->equipments()->count() . "\n";
} else {
    echo "Factura 3 no existe\n";
}
