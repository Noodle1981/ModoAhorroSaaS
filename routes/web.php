<?php

use Illuminate\Support\Facades\Route;

// Controladores de Autenticación
// (He quitado los imports duplicados para mayor claridad)
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Controladores Principales de la Aplicación
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\EntityEquipmentController;
use App\Http\Controllers\SolarController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UsageSnapshotController; // <-- IMPORT NUEVO AÑADIDO
use App\Http\Controllers\StandbySettingsController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SolarHeaterController;
use App\Http\Controllers\SolarPanelController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\ReplacementRecommendationController;
use App\Http\Controllers\SmartAlertController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SnapshotController;

// Controladores del Panel de Gestor (Desactivados)
// use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
// use App\Http\Controllers\Gestor\ClientController;
// use App\Http\Controllers\Gestor\PlanController;
// use App\Http\Controllers\Gestor\EquipmentTypeController;
/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

// --- Rutas Públicas ---
Route::get('/', function () {
    return view('welcome');
})->name('home');


// --- Rutas de Autenticación ---
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});


// --- RUTAS PROTEGIDAS (Requieren inicio de sesión) ---
Route::middleware(['auth'])->group(function () {

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión del Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- GESTIÓN ENERGÉTICA ---
    Route::resource('entities', EntityController::class);
    
    // Insights de Entidad (Análisis IA + ML)
    Route::get('/entities/{entity}/insights', [InsightsController::class, 'show'])->name('entities.insights');
    
    Route::resource('entities.supplies', SupplyController::class)->shallow();
    Route::resource('supplies.contracts', ContractController::class)->shallow();
    Route::resource('contracts.invoices', InvoiceController::class)->shallow();
    Route::resource('entities.equipment', EntityEquipmentController::class)->shallow();

    // ======================================================================
    // === NUEVAS RUTAS PARA LA CONFIRMACIÓN DE USO (SNAPSHOTS) ===
    // ======================================================================
    Route::get('/invoices/{invoice}/snapshots/create', [UsageSnapshotController::class, 'create'])->name('snapshots.create');
    // Vista resumen de snapshots (dashboard del período)
    Route::get('/invoices/{invoice}/snapshots', [UsageSnapshotController::class, 'show'])->name('snapshots.show');
    Route::post('/invoices/{invoice}/snapshots', [UsageSnapshotController::class, 'store'])->name('snapshots.store');
    // ======================================================================

    // --- ANÁLISIS E INTELIGENCIA ---
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::get('/entities/{entity}/report/improvements', [ReportController::class, 'improvements'])->name('entities.reports.improvements');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/solar', [SolarController::class, 'dashboard'])->name('solar.dashboard');

    // --- CONFIGURACIÓN DE STANDBY ---
    Route::get('/standby', [StandbySettingsController::class, 'index'])->name('standby.index');
    Route::patch('/standby/categories/{category}', [StandbySettingsController::class, 'updateCategory'])->name('standby.category.update');
    Route::post('/standby/equipment/bulk', [StandbySettingsController::class, 'bulkUpdateEquipment'])->name('standby.equipment.bulk');

    // --- CALEFÓN SOLAR ---
    Route::get('/solar-heater', [SolarHeaterController::class, 'index'])->name('solar-heater.index');
    Route::get('/solar-heater/{entity}/interest', [SolarHeaterController::class, 'showInterest'])->name('solar-heater.interest');
    Route::post('/solar-heater/{entity}/interest', [SolarHeaterController::class, 'storeInterest'])->name('solar-heater.interest.store');

    // --- PANELES SOLARES ---
    Route::get('/solar-panel', [SolarPanelController::class, 'index'])->name('solar-panel.index');
    Route::get('/solar-panel/{entity}/configure', [SolarPanelController::class, 'configure'])->name('solar-panel.configure');
    Route::post('/solar-panel/{entity}/configure', [SolarPanelController::class, 'storeConfig'])->name('solar-panel.configure.store');
    Route::get('/solar-panel/{entity}/simulate', [SolarPanelController::class, 'simulate'])->name('solar-panel.simulate');

    // Vacaciones (Modo Ahorro)
    Route::get('/vacations', [VacationController::class, 'index'])->name('vacations.index');
    Route::get('/vacations/create', [VacationController::class, 'create'])->name('vacations.create');
    Route::post('/vacations', [VacationController::class, 'store'])->name('vacations.store');
    Route::get('/vacations/{plan}/recommendations', [VacationController::class, 'recommendations'])->name('vacations.recommendations');
    Route::delete('/vacations/{plan}', [VacationController::class, 'destroy'])->name('vacations.destroy');

    // --- RECOMENDACIONES DE REEMPLAZO DE EQUIPOS ---
    Route::get('/replacement-recommendations', [ReplacementRecommendationController::class, 'index'])->name('replacement-recommendations.index');
    Route::post('/replacement-recommendations/generate', [ReplacementRecommendationController::class, 'generate'])->name('replacement-recommendations.generate');
    Route::post('/replacement-recommendations/{recommendation}/accept', [ReplacementRecommendationController::class, 'accept'])->name('replacement-recommendations.accept');
    Route::post('/replacement-recommendations/{recommendation}/reject', [ReplacementRecommendationController::class, 'reject'])->name('replacement-recommendations.reject');
    Route::post('/replacement-recommendations/{recommendation}/start-recovery', [ReplacementRecommendationController::class, 'startRecovery'])->name('replacement-recommendations.start-recovery');
    Route::post('/replacement-recommendations/{recommendation}/complete', [ReplacementRecommendationController::class, 'complete'])->name('replacement-recommendations.complete');
    Route::get('/replacement-recommendations/{recommendation}/export', [ReplacementRecommendationController::class, 'export'])->name('replacement-recommendations.export');
    Route::get('/replacement-recommendations-export-all', [ReplacementRecommendationController::class, 'exportAll'])->name('replacement-recommendations.export-all');

    // --- SMART ALERTS ---
    Route::get('/alerts', [SmartAlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [SmartAlertController::class, 'markAsRead'])->name('alerts.read');
    Route::post('/alerts/{alert}/dismiss', [SmartAlertController::class, 'dismiss'])->name('alerts.dismiss');
    Route::post('/alerts/{alert}/maintenance-complete', [MaintenanceController::class, 'completeFromAlert'])->name('alerts.maintenance-complete');

    // --- SNAPSHOTS: GESTIÓN DE CAMBIOS Y RECÁLCULOS ---
    Route::get('/entities/{entity}/snapshots/review-changes', [SnapshotController::class, 'reviewChanges'])->name('snapshots.review-changes');
    Route::post('/snapshots/{snapshot}/recalculate', [SnapshotController::class, 'recalculate'])->name('snapshots.recalculate');
    Route::post('/entities/{entity}/snapshots/recalculate-period/{date}', [SnapshotController::class, 'recalculatePeriod'])->name('snapshots.recalculate-period');
    Route::post('/entities/{entity}/snapshots/recalculate-all', [SnapshotController::class, 'recalculateAll'])->name('snapshots.recalculate-all');
    Route::post('/snapshots/{snapshot}/confirm', [SnapshotController::class, 'confirm'])->name('snapshots.confirm');
    Route::post('/entities/{entity}/snapshots/confirm-period/{date}', [SnapshotController::class, 'confirmPeriod'])->name('snapshots.confirm-period');

});


// --- RUTAS DE GESTOR (Desactivadas para simplificar el proyecto) ---
/*
Route::middleware(['auth', 'role:gestor'])->prefix('gestor')->name('gestor.')->group(function () {
    
    Route::get('/dashboard', [GestorDashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de Clientes
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{user}', [ClientController::class, 'show'])->name('clients.show');
    
    // Gestión de Catálogos
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::resource('equipment-types', EquipmentTypeController::class)->except(['show']);
});
*/