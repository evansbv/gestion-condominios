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
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Controllers\ActividadReporteController;
use App\Http\Controllers\AporteResidenteController;
use App\Http\Controllers\AporteActividadController;
use Inertia\Inertia;

// Ruta pública
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Rutas de autenticación
//Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
//});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Residentes - Accesible por ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO,PROPIETARIO')->group(function () {
        Route::resource('residentes', ResidenteController::class);
    });

    // Viviendas - Accesible por ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO,PROPIETARIO')->group(function () {
        Route::resource('viviendas', ViviendaController::class);
    });

    // Reuniones - Accesible por ADMINISTRADOR, MIEMBRO_DIRECTORIO y PROPIETARIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO,PROPIETARIO')->group(function () {
        // Rutas específicas primero
        Route::get('/reuniones/calendario/vista', [ReunionController::class, 'calendario'])->name('reuniones.calendario');

        // Rutas de exportación
        Route::get('/reuniones/export/listado', [ReunionController::class, 'exportListado'])->name('reuniones.exportListado');
        Route::get('/reuniones/{reunion}/export/convocatoria', [ReunionController::class, 'exportConvocatoria'])->name('reuniones.exportConvocatoria');
        Route::get('/reuniones/{reunion}/export/acta', [ReunionController::class, 'exportActa'])->name('reuniones.exportActa');

        // Resource y rutas con parámetros
        Route::resource('reuniones', ReunionController::class);
        Route::get('/reuniones/{reunion}/participantes', [ReunionController::class, 'participantes'])->name('reuniones.participantes');
        Route::post('/reuniones/{reunion}/participacion', [ReunionController::class, 'registrarParticipacion'])->name('reuniones.registrarParticipacion');
        Route::get('/reuniones/{reunion}/acta', [ReunionController::class, 'acta'])->name('reuniones.acta');
        Route::post('/reuniones/{reunion}/registrar-acta', [ReunionController::class, 'registrarActa'])->name('reuniones.registrarActa');
        Route::post('/reuniones/{reunion}/enviar-convocatorias', [ReunionController::class, 'enviarConvocatorias'])->name('reuniones.enviarConvocatorias');
    });

    // Actividades - Index y Tablero visibles para todos
    Route::get('actividades', [ActividadController::class, 'index'])->name('actividades.index');
    Route::get('actividades/tablero', [ActividadReporteController::class, 'tablero'])->name('actividades.tablero');

    // Actividades - Gestión solo para ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('actividades/create', [ActividadController::class, 'create'])->name('actividades.create');
        Route::post('actividades', [ActividadController::class, 'store'])->name('actividades.store');
        Route::get('actividades/{actividade}/edit', [ActividadController::class, 'edit'])->name('actividades.edit');
        Route::put('actividades/{actividade}', [ActividadController::class, 'update'])->name('actividades.update');
        Route::delete('actividades/{actividade}', [ActividadController::class, 'destroy'])->name('actividades.destroy');
    });

    // Actividades - Show visible para todos (debe estar al final para no capturar 'create')
    Route::get('actividades/{actividade}', [ActividadController::class, 'show'])->name('actividades.show');

    // Aportes - Index visible para todos
    Route::get('/aportes', [AporteController::class, 'index'])->name('aportes.index');

    // Rutas específicas (deben ir antes de las rutas con parámetros)
    Route::get('/aportes/estadisticas', [AporteController::class, 'estadisticas'])->name('aportes.estadisticas');
    Route::get('/aportes/estadisticas/pdf', [AporteController::class, 'estadisticasPDF'])->name('aportes.estadisticas.pdf');
    Route::get('/aportes/estadisticas/csv', [AporteController::class, 'estadisticasCSV'])->name('aportes.estadisticas.csv');

    // Gestión de aportes - Solo ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('/aportes/create', [AporteController::class, 'create'])->name('aportes.create');
        Route::post('/aportes', [AporteController::class, 'store'])->name('aportes.store');
        Route::get('/aportes/{aporte}/edit', [AporteController::class, 'edit'])->name('aportes.edit');
        Route::put('/aportes/{aporte}', [AporteController::class, 'update'])->name('aportes.update');
        Route::delete('/aportes/{aporte}', [AporteController::class, 'destroy'])->name('aportes.destroy');
        Route::post('/aportes/enviar-notificaciones', [AporteController::class, 'enviarNotificacionesVencidos'])->name('aportes.enviarNotificaciones');
    });

    // Aportes con parámetros (deben ir al final)
    Route::get('/aportes/{aporte}', [AporteController::class, 'show'])->name('aportes.show');
    Route::post('/aportes/{aporte}/registrar-pago', [AporteController::class, 'registrarPago'])->name('aportes.registrarPago');

    // Comunicaciones - Index visible para todos
    Route::get('/comunicaciones', [ComunicacionController::class, 'index'])->name('comunicaciones.index');

    // Gestión de comunicaciones - ADMINISTRADOR y MIEMBRO_DIRECTORIO
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO,PROPIETARIO')->group(function () {
        Route::get('/comunicaciones/create', [ComunicacionController::class, 'create'])->name('comunicaciones.create');
        Route::post('/comunicaciones', [ComunicacionController::class, 'store'])->name('comunicaciones.store');
        Route::get('/comunicaciones/estadisticas/general', [ComunicacionController::class, 'estadisticas'])->name('comunicaciones.estadisticas');
        Route::get('/comunicaciones/{comunicacion}/edit', [ComunicacionController::class, 'edit'])->name('comunicaciones.edit');
        Route::put('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'update'])->name('comunicaciones.update');
        Route::delete('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'destroy'])->name('comunicaciones.destroy');
        Route::post('/comunicaciones/{comunicacion}/enviar', [ComunicacionController::class, 'enviar'])->name('comunicaciones.enviar');
    });

    // Comunicaciones con parámetros (deben ir al final)
    Route::get('/comunicaciones/{comunicacion}', [ComunicacionController::class, 'show'])->name('comunicaciones.show');
    Route::post('/comunicaciones/{comunicacion}/marcar-leida', [ComunicacionController::class, 'marcarComoLeida'])->name('comunicaciones.marcarLeida');

    // Reportes Financieros - Dashboard visible para admin y directorio
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('/finanzas/dashboard', [ReporteFinancieroController::class, 'dashboard'])->name('finanzas.dashboard');
        Route::get('/finanzas/exportar-pdf', [ReporteFinancieroController::class, 'exportarPDF'])->name('finanzas.exportar-pdf');
        Route::get('/finanzas/exportar-csv', [ReporteFinancieroController::class, 'exportarCSV'])->name('finanzas.exportar-csv');
    });

    // Mis Aportes - Vista personal para todos los usuarios autenticados
    Route::get('/mis-aportes', [ReporteFinancieroController::class, 'misAportes'])->name('mis-aportes');
    Route::get('/mis-aportes/pdf', [ReporteFinancieroController::class, 'misAportesPDF'])->name('mis-aportes.pdf');
    Route::get('/mis-aportes/csv', [ReporteFinancieroController::class, 'misAportesCSV'])->name('mis-aportes.csv');

    // Reportes de Aportes - Visible para admin/directorio, pero los usuarios regulares ven solo sus datos
    Route::get('/reportes/aportes-por-residente', [AporteResidenteController::class, 'index'])->name('reportes.aportes-por-residente');
    Route::get('/reportes/aportes-por-residente/pdf', [AporteResidenteController::class, 'exportarPDF'])->name('reportes.aportes-por-residente.pdf');
    Route::get('/reportes/aportes-por-residente/csv', [AporteResidenteController::class, 'exportarCSV'])->name('reportes.aportes-por-residente.csv');

    // Reportes de Aportes por Actividad - Visible para admin/directorio
    Route::middleware('role:ADMINISTRADOR,MIEMBRO_DIRECTORIO')->group(function () {
        Route::get('/reportes/aportes-por-actividad', [AporteActividadController::class, 'index'])->name('reportes.aportes-por-actividad');
        Route::get('/reportes/aportes-por-actividad/pdf', [AporteActividadController::class, 'exportarPDF'])->name('reportes.aportes-por-actividad.pdf');
        Route::get('/reportes/aportes-por-actividad/csv', [AporteActividadController::class, 'exportarCSV'])->name('reportes.aportes-por-actividad.csv');
    });
});
