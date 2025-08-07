<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\EnvioMuestraWebController;
use App\Http\Controllers\BrigadaExportController;
use App\Http\Controllers\EncuestaExportController;
use App\Http\Middleware\ApiAuthentication;

// Ruta raíz
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Rutas de autenticación (públicas)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Rutas protegidas con autenticación
Route::middleware(ApiAuthentication::class)->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/datos', [DashboardController::class, 'getDatos'])->name('dashboard.datos');
        Route::get('/sedes', [ApiController::class, 'getSedes'])->name('sedes');
    });
    
    // Rutas para visitas
    Route::prefix('visitas')->name('visitas.')->group(function () {
        Route::get('/buscar', [VisitaController::class, 'buscarForm'])->name('buscar');
        Route::post('/buscar', [VisitaController::class, 'buscar'])->name('buscar.submit');
        Route::get('/{id}', [VisitaController::class, 'show'])->name('show');
        Route::get('/{id}/print', [VisitaController::class, 'printView'])->name('print');
        Route::get('/{id}/pdf', [VisitaController::class, 'generarPDF'])->name('pdf');
        // Rutas para exportación
        Route::get('/export/form', [VisitaController::class, 'exportForm'])->name('export');
        Route::post('/export/excel', [VisitaController::class, 'exportExcel'])->name('export.excel');
    });
    
    Route::prefix('laboratorio')->name('laboratorio.')->group(function () {
        Route::get('/', [EnvioMuestraWebController::class, 'index'])->name('index');
        Route::get('/sede/{sedeId}', [EnvioMuestraWebController::class, 'listarPorSede'])->name('sede');
        Route::get('/ver/{id}', [EnvioMuestraWebController::class, 'ver'])->name('ver');
        Route::get('/crear', [EnvioMuestraWebController::class, 'crear'])->name('crear');
        Route::post('/guardar', [EnvioMuestraWebController::class, 'guardar'])->name('guardar');
        Route::get('/editar/{id}', [EnvioMuestraWebController::class, 'editar'])->name('editar');
        Route::put('/actualizar/{id}', [EnvioMuestraWebController::class, 'actualizar'])->name('actualizar');
        Route::delete('/eliminar/{id}', [EnvioMuestraWebController::class, 'eliminar'])->name('eliminar');
        Route::post('/buscar-paciente', [EnvioMuestraWebController::class, 'buscarPaciente'])->name('buscar-paciente');
        Route::get('/detalle-pdf/{id}', [EnvioMuestraWebController::class, 'generarPdf'])->name('detallePdf');
        Route::get('/enviar-email/{id}', [EnvioMuestraWebController::class, 'enviarPorEmail'])->name('enviarEmail');
    });
    // Rutas para exportación de brigadas
    Route::prefix('brigadas')->name('brigadas.')->group(function () {
        Route::get('/export/form', [BrigadaExportController::class, 'exportForm'])->name('export');
        Route::post('/export/excel', [BrigadaExportController::class, 'exportExcel'])->name('export.excel');
    });

    // En routes/web.php o donde tengas tus rutas
    Route::get('encuestas/export', [EncuestaExportController::class, 'exportForm'])->name('encuestas.export');
    Route::get('encuestas/export/excel', [EncuestaExportController::class, 'exportExcel'])->name('encuestas.export.excel');

        

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Ruta para manejar errores 404 (opcional)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

Route::get('/test-email', function () {
    try {
        Mail::raw('Prueba de correo desde Laravel', function($message) {
            $message->to('tecnologia@nacerparavivir.org')
                    ->subject('Prueba de correo');
        });
        
        return 'Correo enviado correctamente';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});