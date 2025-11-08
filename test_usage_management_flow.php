<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\SmartAlert;
use App\Models\User;
use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Http\Controllers\UsageSettingsController;
use App\Http\Controllers\UsageSnapshotController;

echo "=== Test Gestión de Uso ===\n";
$user = User::first();
if(!$user){ echo "No user"; exit(1);} 
Auth()->login($user);

// Reset alertas usage
SmartAlert::whereIn('type',[ 'usage_pending','usage_recommendation_available','usage_new_equipment'])->delete();
Session::forget('usage_confirmed_at');

// Forzar ejecución del dashboard manageUsageAlert (simulación ligera)
// Creamos manualmente la alerta pending si no existe
$entityIds = $user->company?->entities?->pluck('id') ?? collect();
if($entityIds->isNotEmpty()){
    $firstEntityId = $entityIds->first();
    SmartAlert::create([
        'entity_id'=>$firstEntityId,
        'invoice_id'=>null,
        'type'=>'usage_pending',
        'severity'=>'info',
        'title'=>'Configurá la frecuencia de uso',
        'description'=>'Alerta inicial de prueba',
        'data'=>[]
    ]);
}

$pending = SmartAlert::where('type','usage_pending')->active()->count();
echo "Alertas usage_pending iniciales: {$pending}\n";

// Confirmar gestión de uso
$usageController = app(UsageSettingsController::class);
$response = $usageController->confirm(new Request());

$pendingAfter = SmartAlert::where('type','usage_pending')->active()->count();
$recsAvailable = SmartAlert::where('type','usage_recommendation_available')->active()->count();
echo "usage_pending activas tras confirmar: {$pendingAfter}\n";
echo "usage_recommendation_available activas: {$recsAvailable}\n";

// Obtener recomendaciones JSON
$recsJson = $usageController->recommendations();
$data = $recsJson->getData(true);
$sample = $data['equipments'][0] ?? null;
echo "Total equipos con recomendaciones: ".count($data['equipments'])."\n";
if($sample){
    echo "Ejemplo: {$sample['name']} sugerido => ".($sample['suggested']['is_daily_use']?'diario':($sample['suggested']['usage_days_per_week'].'/sem'))." ({$sample['reason']})\n";
}

// Aplicar recomendaciones
$usageController->applyRecommendations(new Request());
$recsAfterApply = SmartAlert::where('type','usage_recommendation_available')->active()->count();
echo "usage_recommendation_available activas tras aplicar: {$recsAfterApply}\n";

// Probar gating de snapshots: necesitamos una invoice existente
$invoice = Invoice::first();
if($invoice){
    $snapController = app(UsageSnapshotController::class);
    // Crear request de snapshot store con un equipo
    $equipment = EntityEquipment::first();
    if($equipment){
        $req = new Request([
            'equipments' => [[
                'entity_equipment_id' => $equipment->id,
                'avg_daily_use_minutes' => 120,
            ]]
        ]);
        $respStore = $snapController->store($req, $invoice);
        echo "Snapshot store ejecutado (HTTP redirect esperado).\n";
    } else {
        echo "No equipment para snapshot test.\n";
    }
} else {
    echo "No invoice para snapshot test.\n";
}

echo "=== Fin test Gestión de Uso ===\n";