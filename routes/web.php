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
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SolarController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UsageSnapshotController;
use App\Http\Controllers\HistoryController;



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
    Route::resource('entities.supplies', SupplyController::class)->shallow();
    Route::resource('supplies.contracts', ContractController::class)->shallow();

    // Grupo de rutas para Facturas y sus Snapshots de uso asociados
    Route::resource('contracts.invoices', InvoiceController::class)->shallow();
    Route::controller(UsageSnapshotController::class)->group(function () {
        Route::get('/invoices/{invoice}/snapshots/create', 'create')->name('snapshots.create');
        Route::post('/invoices/{invoice}/snapshots', 'store')->name('snapshots.store');
        Route::get('/invoices/{invoice}/snapshots/edit', 'edit')->name('snapshots.edit');
        Route::patch('/invoices/{invoice}/snapshots', 'update')->name('snapshots.update');
    });

    // Grupo de rutas para Equipos, incluyendo acciones personalizadas
    Route::get('/equipment/{equipment}/pre-destroy', [EntityEquipmentController::class, 'preDestroy'])->name('equipment.pre-destroy');
    Route::resource('entities.equipment', EntityEquipmentController::class)
    ->shallow()
    ->except(['create', 'store']);
    Route::get('/entities/{entity}/equipment/create', [EntityEquipmentController::class, 'create'])->name('entities.equipment.create');
Route::post('/entities/{entity}/equipment', [EntityEquipmentController::class, 'store'])->name('entities.equipment.store');

    // ======================================================================
    // === NUEVAS RUTAS PARA LA CONFIRMACIÓN DE USO (SNAPSHOTS) ===
    // ======================================================================
    Route::get('/invoices/{invoice}/snapshots/create', [UsageSnapshotController::class, 'create'])->name('snapshots.create');
    Route::post('/invoices/{invoice}/snapshots', [UsageSnapshotController::class, 'store'])->name('snapshots.store');
    Route::get('/invoices/{invoice}/snapshots/edit', [UsageSnapshotController::class, 'edit'])->name('snapshots.edit');
    Route::patch('/invoices/{invoice}/snapshots', [UsageSnapshotController::class, 'update'])->name('snapshots.update');
    // ======================================================================

    // ======================================================================
    // === API INTERNA PARA CÁLCULOS EN VIVO ===
    // ======================================================================
    Route::post('/api/snapshots/recalculate', [UsageSnapshotController::class, 'recalculate'])->name('api.snapshots.recalculate');

    // --- ANÁLISIS E INTELIGENCIA ---
    Route::get('/reports/replacement/{equipment}', [ReportController::class, 'replacementReport'])->name('reports.replacement');
    Route::get('/entities/{entity}/reports/full-replacement', [ReportController::class, 'fullReplacementReport'])->name('entities.reports.full-replacement');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/solar', [SolarController::class, 'dashboard'])->name('solar.dashboard');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index'); // <-- NUEVA RUTA
    Route::get('/entities/{entity}/report/improvements', [ReportController::class, 'improvements'])->name('entities.reports.improvements');


    // DATOS FICICACIÓN EN TIEMPO REAL
    Route::get('/supplies/{supply}/realtime', [SupplyController::class, 'realtimeDashboard'])->name('supplies.realtime');

});


