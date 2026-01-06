<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\ViviendaController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AporteController;
use App\Http\Controllers\ComunicacionController;
use Inertia\Inertia;

// Ruta pública
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Residentes - Accesible por ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::resource('residentes', ResidenteController::class);
    });

    // Viviendas - Accesible por ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::resource('viviendas', ViviendaController::class);
    });

    // Reuniones - Accesible por ADMINISTRADOR, MIEMBRO_DIRECTORIO y PROPIETARIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO,PROPIETARIO')->group(function () {
        Route::resource('reuniones', ReunionController::class);
        Route::get('/reuniones/{reunion}/participantes', [ReunionController::class, 'participantes'])->name('reuniones.participantes');
        Route::post('/reuniones/{reunion}/participacion', [ReunionController::class, 'registrarParticipacion'])->name('reuniones.registrarParticipacion');
        Route::get('/reuniones/{reunion}/acta', [ReunionController::class, 'acta'])->name('reuniones.acta');
        Route::post('/reuniones/{reunion}/registrar-acta', [ReunionController::class, 'registrarActa'])->name('reuniones.registrarActa');
        Route::post('/reuniones/{reunion}/enviar-convocatorias', [ReunionController::class, 'enviarConvocatorias'])->name('reuniones.enviarConvocatorias');
        Route::get('/reuniones/calendario/vista', [ReunionController::class, 'calendario'])->name('reuniones.calendario');
    });

    // Actividades - Visible para todos
    Route::resource('actividades', ActividadController::class)->only(['index', 'show']);

    // Actividades - Gestión solo para ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::resource('actividades', ActividadController::class)->except(['index', 'show']);
    });

    // Aportes - Todos pueden ver sus propios aportes
    Route::get('/aportes', [AporteController::class, 'index'])->name('aportes.index');
    Route::get('/aportes/estadisticas', [AporteController::class, 'estadisticas'])->name('aportes.estadisticas');
    Route::get('/aportes/{aporte}', [AporteController::class, 'show'])->name('aportes.show');
    Route::post('/aportes/{aporte}/registrar-pago', [AporteController::class, 'registrarPago'])->name('aportes.registrarPago');

    // Gestión de aportes - Solo ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('/aportes/create', [AporteController::class, 'create'])->name('aportes.create');
        Route::post('/aportes', [AporteController::class, 'store'])->name('aportes.store');
        Route::get('/aportes/{aporte}/edit', [AporteController::class, 'edit'])->name('aportes.edit');
        Route::put('/aportes/{aporte}', [AporteController::class, 'update'])->name('aportes.update');
        Route::delete('/aportes/{aporte}', [AporteController::class, 'destroy'])->name('aportes.destroy');
        Route::post('/aportes/enviar-notificaciones', [AporteController::class, 'enviarNotificacionesVencidos'])->name('aportes.enviarNotificaciones');
    });

    // Comunicaciones - Todos pueden ver
    Route::get('/comunicaciones', [ComunicacionController::class, 'index'])->name('comunicaciones.index');
    Route::get('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'show'])->name('comunicaciones.show');
    Route::post('/comunicaciones/{comunicacion}/marcar-leida', [ComunicacionController::class, 'marcarComoLeida'])->name('comunicaciones.marcarLeida');

    // Gestión de comunicaciones - ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('/comunicaciones/create', [ComunicacionController::class, 'create'])->name('comunicaciones.create');
        Route::post('/comunicaciones', [ComunicacionController::class, 'store'])->name('comunicaciones.store');
        Route::get('/comunicaciones/{comunicacion}/edit', [ComunicacionController::class, 'edit'])->name('comunicaciones.edit');
        Route::put('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'update'])->name('comunicaciones.update');
        Route::delete('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'destroy'])->name('comunicaciones.destroy');
        Route::post('/comunicaciones/{comunicacion}/enviar', [ComunicacionController::class, 'enviar'])->name('comunicaciones.enviar');
        Route::get('/comunicaciones/estadisticas/general', [ComunicacionController::class, 'estadisticas'])->name('comunicaciones.estadisticas');
    });
});
