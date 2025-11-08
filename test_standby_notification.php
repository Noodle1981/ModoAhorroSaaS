<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\SmartAlert;
use Illuminate\Support\Facades\Session;

echo "=== Test: Flujo de Notificación Standby ===\n\n";

// Simular usuario
$user = User::first();
if (!$user) {
    echo "❌ No hay usuarios en BD. Ejecutá el seeder primero.\n";
    exit(1);
}

echo "✓ Usuario: {$user->name} (ID {$user->id})\n";
echo "✓ Compañía: {$user->company->name} (ID {$user->company_id})\n";

// Verificar entidades
$entities = $user->company->entities;
echo "✓ Entidades: {$entities->count()}\n";

if ($entities->isEmpty()) {
    echo "❌ No hay entidades para generar alerta.\n";
    exit(1);
}

$firstEntity = $entities->first();
echo "✓ Primera entidad: {$firstEntity->name} (ID {$firstEntity->id})\n\n";

// Limpiar alertas de standby existentes
SmartAlert::where('type', 'standby_pending')->delete();
echo "✓ Alertas standby anteriores eliminadas.\n";

// Simular sesión SIN confirmación
Session::forget('standby_confirmed_at');
echo "✓ Sesión limpiada (standby NO confirmado).\n\n";

// Contar equipos en categorías clave
$equipmentCount = \App\Models\EntityEquipment::whereHas('entity', function($q) use ($user) {
        $q->where('company_id', $user->company_id);
    })
    ->whereHas('equipmentType.equipmentCategory', function($q) {
        $q->whereIn('id', [1, 4, 5, 12]); // Climatización, Cocina, Entretenimiento, Seguridad
    })
    ->count();

echo "📊 Equipos en categorías standby: {$equipmentCount}\n\n";

// Simular creación de alerta (similar al dashboard)
$exists = SmartAlert::where('type', 'standby_pending')
    ->where('entity_id', $firstEntity->id)
    ->active()
    ->exists();

if (!$exists) {
    SmartAlert::create([
        'entity_id' => $firstEntity->id,
        'invoice_id' => null,
        'type' => 'standby_pending',
        'severity' => 'info',
        'title' => 'Configurá la gestión de Standby',
        'description' => sprintf(
            'Tenés %d equipos en categorías que suelen consumir en modo standby. Revisá y confirmá tu configuración para optimizar tu consumo.',
            $equipmentCount
        ),
        'data' => ['equipment_count' => $equipmentCount],
    ]);
    echo "✅ Alerta standby_pending creada correctamente.\n\n";
} else {
    echo "ℹ️  Alerta ya existía.\n\n";
}

// Verificar alerta
$alert = SmartAlert::where('type', 'standby_pending')
    ->where('entity_id', $firstEntity->id)
    ->active()
    ->first();

if ($alert) {
    echo "=== Detalles de la alerta ===\n";
    echo "ID: {$alert->id}\n";
    echo "Título: {$alert->title}\n";
    echo "Descripción: {$alert->description}\n";
    echo "Icono: {$alert->icon}\n";
    echo "Color: {$alert->color_class}\n";
    echo "Leída: " . ($alert->is_read ? 'Sí' : 'No') . "\n";
    echo "Descartada: " . ($alert->is_dismissed ? 'Sí' : 'No') . "\n";
    echo "Creada: {$alert->created_at->diffForHumans()}\n\n";
    
    echo "✅ La alerta debería aparecer en:\n";
    echo "   - Dashboard (sección Alertas Recientes)\n";
    echo "   - Campanita de notificaciones (contador)\n";
    echo "   - /alerts (centro de alertas)\n\n";
} else {
    echo "❌ No se encontró la alerta.\n\n";
}

// Simular confirmación
echo "=== Simulando confirmación de standby ===\n";
Session::put('standby_confirmed_at', \Carbon\Carbon::now()->toDateTimeString());
echo "✓ Session standby_confirmed_at establecida.\n";

// Descartar alerta tras confirmación
SmartAlert::where('type', 'standby_pending')
    ->where('entity_id', $firstEntity->id)
    ->active()
    ->update([
        'is_dismissed' => true,
        'dismissed_at' => now(),
    ]);

echo "✓ Alerta descartada automáticamente.\n\n";

// Verificar estado final
$alertaDescartada = SmartAlert::where('type', 'standby_pending')
    ->where('entity_id', $firstEntity->id)
    ->first();

if ($alertaDescartada && $alertaDescartada->is_dismissed) {
    echo "✅ Flujo completo: La alerta fue descartada tras confirmación.\n";
    echo "   Dismissed at: {$alertaDescartada->dismissed_at}\n";
} else {
    echo "❌ La alerta no fue descartada correctamente.\n";
}

echo "\n=== Test completado ===\n";
