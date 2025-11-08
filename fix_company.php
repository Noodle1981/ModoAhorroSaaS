<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Encontrar el usuario actualmente logueado (por email)
$currentUserEmail = 'omardigital81@gmail.com'; // Cambiar si es otro

$user = App\Models\User::where('email', $currentUserEmail)->first();

if (!$user) {
    echo "❌ Usuario no encontrado: {$currentUserEmail}\n";
    exit;
}

echo "✅ Usuario encontrado: {$user->email}\n";
echo "   Company ID: {$user->company_id}\n";
echo "   Company: {$user->company->name}\n\n";

// Mover la entidad "Casa" a la compañía del usuario
$entity = App\Models\Entity::where('name', 'Casa')->first();

if (!$entity) {
    echo "❌ Entidad 'Casa' no encontrada\n";
    exit;
}

echo "📦 Entidad encontrada: {$entity->name}\n";
echo "   Company ID actual: {$entity->company_id}\n";

if ($entity->company_id !== $user->company_id) {
    $entity->update(['company_id' => $user->company_id]);
    echo "✅ Entidad movida a la compañía del usuario\n";
} else {
    echo "✅ La entidad ya está en la compañía correcta\n";
}

echo "\n🔧 Equipos de esta entidad: {$entity->equipments->count()}\n";
echo "✅ ¡Listo! Ahora deberías ver los equipos en /maintenance\n";
