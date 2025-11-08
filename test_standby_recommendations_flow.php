<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\Session;
use App\Models\SmartAlert;
use App\Http\Controllers\StandbySettingsController;
use Illuminate\Http\Request;
use App\Models\User;

echo "=== Test recomendaciones standby ===\n";

$user = User::first();
if (!$user) { echo "No user"; exit(1);} 
Auth()->login($user);

// Reset
SmartAlert::whereIn('type', ['standby_recommendation_available','standby_new_equipment'])->delete();
Session::forget('standby_confirmed_at');

echo "Confirmando configuración...\n";
$controller = app(StandbySettingsController::class);
$response = $controller->confirm(new Request());

$available = SmartAlert::where('type','standby_recommendation_available')->active()->count();
echo "Alertas recommendation_available activas: {$available}\n";

// Simular apply
echo "Aplicando recomendaciones...\n";
$response2 = $controller->applyRecommendations(new Request());
$availableAfter = SmartAlert::where('type','standby_recommendation_available')->active()->count();
echo "Alertas recommendation_available activas tras aplicar: {$availableAfter}\n";

echo "=== Fin test ===\n";
