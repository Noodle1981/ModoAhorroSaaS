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
use App\Http\Controllers\UsageSnapshotController; // <-- IMPORT NUEVO AÑADIDO



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
    Route::resource('contracts.invoices', InvoiceController::class)->shallow();
    Route::resource('entities.equipment', EntityEquipmentController::class)->shallow();

    // ======================================================================
    // === NUEVAS RUTAS PARA LA CONFIRMACIÓN DE USO (SNAPSHOTS) ===
    // ======================================================================
    Route::get('/invoices/{invoice}/snapshots/create', [UsageSnapshotController::class, 'create'])->name('snapshots.create');
    Route::post('/invoices/{invoice}/snapshots', [UsageSnapshotController::class, 'store'])->name('snapshots.store');
    // ======================================================================

    // --- ANÁLISIS E INTELIGENCIA ---
    Route::get('/entities/{entity}/report/improvements', [ReportController::class, 'improvements'])->name('entities.reports.improvements');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/solar', [SolarController::class, 'dashboard'])->name('solar.dashboard');

});


