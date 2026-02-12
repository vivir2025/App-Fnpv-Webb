<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\EnvioMuestraWebController;
use App\Http\Controllers\BrigadaExportController;
use App\Http\Controllers\EncuestaExportController;
use App\Http\Controllers\FindriskExportController;
use App\Http\Controllers\AfinamientoExportController;
use App\Http\Controllers\TamizajeExportController;
use App\Http\Controllers\LogViewController;
use App\Http\Controllers\NotificationWebController;
use Illuminate\Support\Facades\Mail;
use App\Http\Middleware\ApiAuthentication;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\DashboardAccess;
use App\Http\Middleware\LaboratorioAccess;

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

    // Dashboard - solo para admin y jefes
    Route::middleware(DashboardAccess::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard/datos', [DashboardController::class, 'getDatos'])->name('dashboard.datos');
        });
    });
    
    // API Routes generales
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/sedes', [ApiController::class, 'getSedes'])->name('sedes');
    });
    
    // Rutas para visitas - accesible para todos los usuarios autenticados
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
    
    // Rutas de laboratorio - solo para admin y jefes
    Route::middleware(LaboratorioAccess::class)->group(function () {
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
            Route::post('/enviar-email/{id}', [EnvioMuestraWebController::class, 'enviarPorEmail'])->name('enviarEmail.post');
        });
    });

    // Rutas para exportación de brigadas
    Route::prefix('brigadas')->name('brigadas.')->group(function () {
        Route::get('/export/form', [BrigadaExportController::class, 'exportForm'])->name('export');
        Route::post('/export/excel', [BrigadaExportController::class, 'exportExcel'])->name('export.excel');
    });

    // Rutas para encuestas
    Route::get('encuestas/export', [EncuestaExportController::class, 'exportForm'])->name('encuestas.export');
    Route::get('encuestas/export/excel', [EncuestaExportController::class, 'exportExcel'])->name('encuestas.export.excel');
    
    // Rutas para FINDRISK
    Route::prefix('findrisk')->name('findrisk.')->group(function () {
        Route::get('/', [FindriskExportController::class, 'index'])->name('index');
        Route::get('/exportar', [FindriskExportController::class, 'index'])->name('export');
        Route::post('/exportar', [FindriskExportController::class, 'exportar'])->name('exportar');
    });

    // Rutas para exportación de afinamientos
    Route::prefix('afinamientos')->name('afinamientos.')->group(function () {
        Route::get('/export/form', [AfinamientoExportController::class, 'exportForm'])->name('export');
        Route::post('/export/excel', [AfinamientoExportController::class, 'exportExcel'])->name('export.excel');
    });

    // Rutas para exportación de tamizajes
    Route::prefix('tamizajes')->name('tamizajes.')->group(function () {
        Route::get('/export/form', [TamizajeExportController::class, 'exportForm'])->name('export');
        Route::post('/export/excel', [TamizajeExportController::class, 'exportExcel'])->name('export.excel');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ✅ RUTAS DE LOGS - SOLO PARA ADMINISTRADORES (FUERA DEL GRUPO PRINCIPAL)
Route::middleware([ApiAuthentication::class, AdminOnly::class])->group(function () {
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogViewController::class, 'index'])->name('index');
        Route::get('/stats', [LogViewController::class, 'getStats'])->name('stats');
        Route::get('/data', [LogViewController::class, 'getData'])->name('data');
        Route::get('/{id}', [LogViewController::class, 'show'])->name('show');
    });

    // ✅ RUTAS DE NOTIFICACIONES - SOLO PARA ADMINISTRADORES
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationWebController::class, 'index'])->name('index');
        Route::get('/stats', [NotificationWebController::class, 'getStats'])->name('stats');
        Route::get('/users', [NotificationWebController::class, 'getUsers'])->name('users');
        Route::post('/send-to-user', [NotificationWebController::class, 'sendToUser'])->name('send.user');
        Route::post('/send-to-all', [NotificationWebController::class, 'sendToAll'])->name('send.all');
        
        // 📋 NUEVAS RUTAS PARA LISTAR USUARIOS CON TOKENS
        Route::get('/users-with-tokens', [NotificationWebController::class, 'getUsersWithTokens'])->name('users.with.tokens');
        Route::get('/token-stats', [NotificationWebController::class, 'getTokenStats'])->name('token.stats');
        Route::get('/user/{userId}/tokens', [NotificationWebController::class, 'getUserTokens'])->name('user.tokens');
    });
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