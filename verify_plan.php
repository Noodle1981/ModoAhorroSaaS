<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Plan;
use App\Models\Entity;

echo "=== VERIFICACIÓN DE RESTRICCIONES DEL PLAN ===\n\n";

// Obtener el primer usuario (el que probablemente estés usando)
$user = User::with(['company.entities', 'company.subscription.plan'])->first();

if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit;
}

echo "👤 Usuario: {$user->name} ({$user->email})\n";
echo "🏢 Compañía: " . ($user->company ? $user->company->name : "Sin compañía") . "\n\n";

if (!$user->subscription || !$user->subscription->plan) {
    echo "❌ El usuario NO tiene suscripción o plan asignado\n";
    echo "   Esto impedirá crear entidades.\n";
    exit;
}

$plan = $user->subscription->plan;
$entities = $user->company ? $user->company->entities : collect();

echo "📋 Plan actual: {$plan->name}\n";
echo "💰 Precio: \${$plan->price}\n";
echo "🏠 Máximo de entidades: " . ($plan->max_entities ?? 'Ilimitado') . "\n";
echo "📦 Tipos permitidos: " . implode(', ', $plan->allowed_entity_types) . "\n\n";

echo "--- Estado actual ---\n";
echo "Entidades creadas: {$entities->count()}\n";

if ($entities->count() > 0) {
    echo "\nEntidades existentes:\n";
    foreach ($entities as $entity) {
        echo "  • {$entity->name} (Tipo: {$entity->type})\n";
    }
}

echo "\n--- Verificación de restricciones ---\n";

// Verificar si puede crear más entidades
$canCreateMore = is_null($plan->max_entities) || $entities->count() < $plan->max_entities;
echo "¿Puede crear más entidades? " . ($canCreateMore ? "✅ SÍ" : "❌ NO") . "\n";

// Verificar tipos permitidos
if (count($plan->allowed_entity_types) === 1) {
    echo "Restricción de tipo: Solo puede crear '{$plan->allowed_entity_types[0]}'\n";
} else {
    echo "Puede crear tipos: " . implode(', ', $plan->allowed_entity_types) . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
