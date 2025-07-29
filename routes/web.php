<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\ApiAuthentication;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Rutas protegidas - usando la clase directamente en lugar del alias
Route::middleware(ApiAuthentication::class)->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/datos', [DashboardController::class, 'getDatos']);
    
    // API
    Route::get('/api/sedes', [ApiController::class, 'getSedes'])->name('api.sedes');
    
    // Rutas para visitas
    Route::get('/visitas/buscar', [VisitaController::class, 'buscarForm'])->name('visitas.buscar');
    Route::post('/visitas/buscar', [VisitaController::class, 'buscar'])->name('visitas.buscar.submit');
    Route::get('/visitas/{id}', [VisitaController::class, 'show'])->name('visitas.show');
    
    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
