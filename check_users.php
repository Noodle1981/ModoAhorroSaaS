<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USUARIOS EN EL SISTEMA ===\n\n";

$users = App\Models\User::with('company')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Company ID: {$user->company_id}\n";
    echo "Company Name: {$user->company->name}\n";
    echo "---\n";
}

echo "\n=== ENTIDADES Y EQUIPOS ===\n\n";

$entities = App\Models\Entity::with('company', 'equipments')->get();
foreach ($entities as $entity) {
    echo "Entity: {$entity->name} (ID: {$entity->id})\n";
    echo "Company ID: {$entity->company_id}\n";
    echo "Company Name: {$entity->company->name}\n";
    echo "Equipos: {$entity->equipments->count()}\n";
    echo "---\n";
}
