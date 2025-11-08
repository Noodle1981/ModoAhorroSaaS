<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Usuario test
$user = App\Models\User::where('email', 'test@example.com')->first();

if (!$user) {
    echo "❌ Usuario test no encontrado\n";
    exit;
}

echo "✅ Usuario: {$user->email}\n";
echo "   Company ID: {$user->company_id}\n";
echo "   Company: {$user->company->name}\n\n";

// Mover la entidad "Casa" a la compañía del usuario test
$entity = App\Models\Entity::where('name', 'Casa')->first();

if (!$entity) {
    echo "❌ Entidad 'Casa' no encontrada\n";
    exit;
}

echo "📦 Moviendo entidad '{$entity->name}' a Company ID {$user->company_id}...\n";
$entity->update(['company_id' => $user->company_id]);

echo "✅ ¡Listo! Entidad movida\n";
echo "🔧 Equipos: {$entity->equipments->count()}\n";
echo "\n🎯 Ahora recarga http://127.0.0.1:8000/maintenance\n";
