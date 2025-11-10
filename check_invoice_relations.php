<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::first();

if (!$invoice) {
    echo "No hay facturas\n";
    exit;
}

echo "Invoice ID: {$invoice->id}\n";
echo "Contract ID: {$invoice->contract_id}\n";

$contract = $invoice->contract;
echo "Contract exists: " . ($contract ? "Yes" : "No") . "\n";

if ($contract) {
    echo "Supply ID: {$contract->supply_id}\n";
    $supply = $contract->supply;
    echo "Supply exists: " . ($supply ? "Yes" : "No") . "\n";
    
    if ($supply) {
        echo "Entity ID: {$supply->entity_id}\n";
        $entity = $supply->entity;
        echo "Entity exists: " . ($entity ? "Yes" : "No") . "\n";
        
        if ($entity) {
            echo "Entity Name: {$entity->name}\n";
        }
    }
}
