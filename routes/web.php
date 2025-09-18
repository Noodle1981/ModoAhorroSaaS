<?php

use Illuminate\Support\Facades\Route;

// Controladores de Autenticación
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Controladores Principales de la Aplicación
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\EntityEquipmentController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SolarController;
use App\Http\Controllers\ReportController;

// Controladores del Panel de Gestor
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\ClientController;
use App\Http\Controllers\Gestor\PlanController;
use App\Http\Controllers\Gestor\EquipmentTypeController;


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

    // --- ANÁLISIS E INTELIGENCIA ---
    Route::get('/entities/{entity}/report/improvements', [ReportController::class, 'improvements'])->name('entities.reports.improvements');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/solar', [SolarController::class, 'dashboard'])->name('solar.dashboard');

});


// --- RUTAS DE GESTOR (Requieren rol específico) ---
Route::middleware(['auth', 'role:gestor'])->prefix('gestor')->name('gestor.')->group(function () {
    
    Route::get('/dashboard', [GestorDashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de Clientes
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{user}', [ClientController::class, 'show'])->name('clients.show');
    
    // Gestión de Catálogos
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::resource('equipment-types', EquipmentTypeController::class)->except(['show']);
    // Podríamos añadir más gestores de catálogos aquí en el futuro (ej: utility-companies, maintenance-tasks)
});
