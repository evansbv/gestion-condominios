<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Actividad;
use App\Models\Vivienda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteFinancieroController extends Controller
{
    /**
     * Dashboard principal de finanzas
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $añoActual = Carbon::now()->year;

        // Obtener filtro de año
        $año = $request->get('año', $añoActual);

        // Solo admin y directorio ven datos completos
        $esAdmin = in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO']);

        // Actualizar mora en todos los aportes pendientes antes de calcular
        $this->actualizarMorasTodos();

        // Estadísticas globales
        $estadisticas = $this->obtenerEstadisticasGlobales($año);

        // Evolución mensual de morosidad (últimos 12 meses)
        $evolucionMensual = $this->obtenerEvolucionMensual();

        // Resumen por actividad
        $resumenActividades = $this->obtenerResumenActividades($año);

        // Top 10 morosos (solo para admin)
        $topMorosos = $esAdmin ? $this->obtenerTopMorosos(10) : [];

        return Inertia::render('Finanzas/Dashboard', [
            'estadisticas' => $estadisticas,
            'evolucionMensual' => $evolucionMensual,
            'resumenActividades' => $resumenActividades,
            'topMorosos' => $topMorosos,
            'esAdmin' => $esAdmin,
            'añoActual' => $añoActual,
            'añoSeleccionado' => $año
        ]);
    }

    /**
     * Vista personal de aportes del usuario logueado
     */
    public function misAportes(Request $request)
    {
        $user = $request->user();

        // Obtener vivienda del usuario
        $residente = $user->residente;

        if (!$residente) {
            return back()->withErrors(['error' => 'No tiene una vivienda asignada.']);
        }

        $vivienda = $residente->vivienda;

        // Obtener filtros
        $filtroTipo = $request->get('filtro_tipo', 'todos');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Construir query base
        $query = Aporte::where('vivienda_id', $vivienda->id)
            ->with(['actividad']);

        // Aplicar filtros
        switch ($filtroTipo) {
            case 'año_actual':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'mes_actual':
                $query->whereYear('created_at', Carbon::now()->year)
                      ->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'rango':
                if ($fechaInicio) {
                    $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
                }
                if ($fechaFin) {
                    $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
                }
                break;
        }

        // Obtener aportes filtrados
        $aportes = $query->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->map(function ($aporte) {
                $aporte->mora_actualizada = $aporte->calcularMora();
                $aporte->dias_mora = $aporte->estaVencido()
                    ? Carbon::now()->diffInDays($aporte->fecha_vencimiento)
                    : 0;
                return $aporte;
            });

        // Estadísticas personales
        $estadisticas = [
            'total_adeudado' => $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->sum(function($a) {
                return ($a->monto - $a->monto_pagado);
            }),
            'total_mora' => $aportes->whereIn('estado', ['VENCIDO', 'PARCIAL'])->sum('mora_actualizada'),
            'total_pagado' => $aportes->sum('monto_pagado'),
            'aportes_pendientes' => $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
            'aportes_pagados' => $aportes->where('estado', 'PAGADO')->count()
        ];

        return Inertia::render('Finanzas/MisAportes', [
            'aportes' => $aportes,
            'estadisticas' => $estadisticas,
            'vivienda' => $vivienda,
            'residente' => $residente,
            'filtros' => [
                'filtro_tipo' => $filtroTipo,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        ]);
    }

    /**
     * Exportar mis aportes a PDF
     */
    public function misAportesPDF(Request $request)
    {
        $user = $request->user();
        $residente = $user->residente;

        if (!$residente) {
            return back()->withErrors(['error' => 'No tiene una vivienda asignada.']);
        }

        $vivienda = $residente->vivienda;

        // Obtener filtros
        $filtroTipo = $request->get('filtro_tipo', 'todos');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Construir query base
        $query = Aporte::where('vivienda_id', $vivienda->id)
            ->with(['actividad']);

        // Aplicar filtros
        switch ($filtroTipo) {
            case 'año_actual':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'mes_actual':
                $query->whereYear('created_at', Carbon::now()->year)
                      ->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'rango':
                if ($fechaInicio) {
                    $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
                }
                if ($fechaFin) {
                    $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
                }
                break;
        }

        // Obtener aportes filtrados
        $aportes = $query->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->map(function ($aporte) {
                $aporte->mora_actualizada = $aporte->calcularMora();
                return $aporte;
            });

        // Estadísticas por estado
        $estadisticas = [
            'total_adeudado' => $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->sum(function($a) {
                return ($a->monto - $a->monto_pagado);
            }),
            'total_mora' => $aportes->whereIn('estado', ['VENCIDO', 'PARCIAL'])->sum('mora_actualizada'),
            'total_pagado' => $aportes->sum('monto_pagado'),
            'aportes_pendientes' => $aportes->whereIn('estado', ['PENDIENTE', 'PARCIAL'])->count(),
            'aportes_pagados' => $aportes->where('estado', 'PAGADO')->count(),
            'aportes_vencidos' => $aportes->where('estado', 'VENCIDO')->count(),
            'monto_pendiente' => $aportes->where('estado', 'PENDIENTE')->sum(function($a) {
                return ($a->monto - $a->monto_pagado);
            }),
            'monto_vencido' => $aportes->where('estado', 'VENCIDO')->sum(function($a) {
                return ($a->monto - $a->monto_pagado);
            }),
        ];

        // Preparar descripción del filtro
        $filtroDescripcion = 'Todos los aportes';
        if ($filtroTipo === 'año_actual') {
            $filtroDescripcion = 'Aportes del año ' . Carbon::now()->year;
        } elseif ($filtroTipo === 'mes_actual') {
            $filtroDescripcion = 'Aportes de ' . Carbon::now()->translatedFormat('F Y');
        } elseif ($filtroTipo === 'rango' && ($fechaInicio || $fechaFin)) {
            $inicio = $fechaInicio ? Carbon::parse($fechaInicio)->format('d/m/Y') : 'Inicio';
            $fin = $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : 'Actualidad';
            $filtroDescripcion = 'Aportes del ' . $inicio . ' al ' . $fin;
        }

        $pdf = PDF::loadView('reportes.mis-aportes-pdf', [
            'residente' => $residente,
            'vivienda' => $vivienda,
            'aportes' => $aportes,
            'estadisticas' => $estadisticas,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'filtro_descripcion' => $filtroDescripcion
        ]);

        return $pdf->download('mis-aportes-' . $vivienda->numero . '.pdf');
    }

    /**
     * Exportar mis aportes a CSV
     */
    public function misAportesCSV(Request $request)
    {
        $user = $request->user();
        $residente = $user->residente;

        if (!$residente) {
            return back()->withErrors(['error' => 'No tiene una vivienda asignada.']);
        }

        $vivienda = $residente->vivienda;

        // Obtener filtros
        $filtroTipo = $request->get('filtro_tipo', 'todos');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Construir query base
        $query = Aporte::where('vivienda_id', $vivienda->id)
            ->with(['actividad']);

        // Aplicar filtros
        switch ($filtroTipo) {
            case 'año_actual':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'mes_actual':
                $query->whereYear('created_at', Carbon::now()->year)
                      ->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'rango':
                if ($fechaInicio) {
                    $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
                }
                if ($fechaFin) {
                    $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
                }
                break;
        }

        // Obtener aportes filtrados
        $aportes = $query->orderBy('fecha_vencimiento', 'desc')
            ->get()
            ->map(function ($aporte) {
                $aporte->mora_actualizada = $aporte->calcularMora();
                return $aporte;
            });

        $filename = 'mis-aportes-' . $vivienda->numero . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($aportes) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'Fecha Aporte',
                'Fecha Vencimiento',
                'Actividad',
                'Monto (Bs)',
                'Monto Pagado (Bs)',
                'Monto Pendiente (Bs)',
                'Mora (Bs)',
                'Estado'
            ]);

            // Datos
            foreach ($aportes as $aporte) {
                fputcsv($file, [
                    $aporte->created_at->format('d/m/Y'),
                    $aporte->fecha_vencimiento->format('d/m/Y'),
                    $aporte->actividad ? $aporte->actividad->titulo : 'Sin actividad',
                    number_format($aporte->monto, 2),
                    number_format($aporte->monto_pagado, 2),
                    number_format($aporte->monto - $aporte->monto_pagado, 2),
                    number_format($aporte->mora_actualizada, 2),
                    $aporte->estado
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar reporte financiero a PDF
     */
    public function exportarPDF(Request $request)
    {
        $año = $request->get('año', Carbon::now()->year);

        $estadisticas = $this->obtenerEstadisticasGlobales($año);
        $resumenActividades = $this->obtenerResumenActividades($año);
        $topMorosos = $this->obtenerTopMorosos(10);

        $pdf = PDF::loadView('reportes.financiero-pdf', [
            'estadisticas' => $estadisticas,
            'resumenActividades' => $resumenActividades,
            'topMorosos' => $topMorosos,
            'año' => $año,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('reporte-financiero-' . $año . '.pdf');
    }

    /**
     * Exportar reporte financiero a CSV
     */
    public function exportarCSV(Request $request)
    {
        $año = $request->get('año', Carbon::now()->year);
        $resumenActividades = $this->obtenerResumenActividades($año);

        $filename = 'reporte-financiero-' . $año . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($resumenActividades) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, ['Actividad', 'Tipo', 'Presupuesto Aprobado', 'Total Recaudado', '% Pagado', 'Deuda Pendiente', 'Mora Acumulada', 'Estado']);

            // Datos
            foreach ($resumenActividades as $actividad) {
                fputcsv($file, [
                    $actividad['titulo'],
                    $actividad['tipo'],
                    number_format($actividad['presupuesto_aprobado'], 2),
                    number_format($actividad['total_recaudado'], 2),
                    number_format($actividad['porcentaje_pagado'], 2) . '%',
                    number_format($actividad['deuda_pendiente'], 2),
                    number_format($actividad['mora_total'], 2),
                    $actividad['estado']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtener estadísticas globales
     */
    private function obtenerEstadisticasGlobales($año)
    {
        // Aportes del año
        $aportesAño = Aporte::whereYear('created_at', $año)->get();

        $totalEsperado = $aportesAño->sum('monto');
        $totalRecaudado = $aportesAño->sum('monto_pagado');
        $totalAdeudado = $aportesAño->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->sum(function ($aporte) {
                return $aporte->monto - $aporte->monto_pagado;
            });

        $totalMora = $aportesAño->whereIn('estado', ['VENCIDO', 'PARCIAL'])->sum('monto_mora');

        $porcentajeMorosidad = $totalEsperado > 0
            ? round(($totalAdeudado / $totalEsperado) * 100, 2)
            : 0;

        $totalViviendas = Vivienda::where('activo', true)->count();
        $viviendasConDeuda = Aporte::whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->distinct('vivienda_id')
            ->count('vivienda_id');

        return [
            'porcentaje_morosidad' => $porcentajeMorosidad,
            'total_esperado' => round($totalEsperado, 2),
            'total_recaudado' => round($totalRecaudado, 2),
            'total_adeudado' => round($totalAdeudado, 2),
            'total_mora' => round($totalMora, 2),
            'total_viviendas' => $totalViviendas,
            'viviendas_con_deuda' => $viviendasConDeuda,
            'porcentaje_al_dia' => round(100 - $porcentajeMorosidad, 2)
        ];
    }

    /**
     * Obtener evolución mensual de morosidad (últimos 12 meses)
     */
    private function obtenerEvolucionMensual()
    {
        $meses = [];
        $datos = [];

        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = $fecha->translatedFormat('M Y');

            // Obtener aportes hasta ese mes
            $aportesHastaMes = Aporte::where('fecha_vencimiento', '<=', $fecha->endOfMonth())
                ->get();

            $totalEsperado = $aportesHastaMes->sum('monto');
            $totalRecaudado = $aportesHastaMes->sum('monto_pagado');
            $totalAdeudado = $totalEsperado - $totalRecaudado;

            $porcentajeMorosidad = $totalEsperado > 0
                ? round(($totalAdeudado / $totalEsperado) * 100, 2)
                : 0;

            $datos[] = $porcentajeMorosidad;
        }

        return [
            'labels' => $meses,
            'data' => $datos
        ];
    }

    /**
     * Obtener resumen por actividad
     */
    private function obtenerResumenActividades($año)
    {
        $actividades = Actividad::with('aportes')
            ->whereYear('created_at', $año)
            ->get();

        return $actividades->map(function ($actividad) {
            $totalRecaudado = $actividad->aportes->sum('monto_pagado');
            $deudaPendiente = $actividad->aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
                ->sum(function ($aporte) {
                    return $aporte->monto - $aporte->monto_pagado;
                });
            $moraTotal = $actividad->aportes->whereIn('estado', ['VENCIDO', 'PARCIAL'])
                ->sum('monto_mora');

            $presupuestoAprobado = $actividad->presupuesto_aprobado ?? 0;
            $porcentajePagado = $presupuestoAprobado > 0
                ? round(($totalRecaudado / $presupuestoAprobado) * 100, 2)
                : 0;

            return [
                'id' => $actividad->id,
                'titulo' => $actividad->titulo,
                'tipo' => $actividad->tipo,
                'presupuesto_aprobado' => round($presupuestoAprobado, 2),
                'total_recaudado' => round($totalRecaudado, 2),
                'porcentaje_pagado' => $porcentajePagado,
                'deuda_pendiente' => round($deudaPendiente, 2),
                'mora_total' => round($moraTotal, 2),
                'estado' => $actividad->estado
            ];
        })->sortByDesc('deuda_pendiente')->values();
    }

    /**
     * Obtener top morosos
     */
    private function obtenerTopMorosos($limit = 10)
    {
        $viviendas = Vivienda::with('aportes', 'residentes')
            ->get()
            ->map(function ($vivienda) {
                $aportesVencidos = $vivienda->aportes->whereIn('estado', ['VENCIDO', 'PARCIAL']);

                $deudaTotal = $aportesVencidos->sum(function ($aporte) {
                    return $aporte->monto - $aporte->monto_pagado;
                });

                $moraTotal = $aportesVencidos->sum('monto_mora');

                $diasMoraMaximo = $aportesVencidos->max(function ($aporte) {
                    return Carbon::now()->diffInDays($aporte->fecha_vencimiento);
                }) ?? 0;

                $propietario = $vivienda->residentes->where('tipo_residente', 'PROPIETARIO')->first();

                return [
                    'vivienda_numero' => $vivienda->numero,
                    'vivienda_direccion' => $vivienda->direccion,
                    'propietario' => $propietario ? $propietario->nombres . ' ' . $propietario->apellido_paterno : 'Sin propietario',
                    'deuda_total' => round($deudaTotal, 2),
                    'mora_total' => round($moraTotal, 2),
                    'dias_mora' => $diasMoraMaximo,
                    'aportes_pendientes' => $aportesVencidos->count()
                ];
            })
            ->filter(function ($item) {
                return $item['deuda_total'] > 0;
            })
            ->sortByDesc('deuda_total')
            ->take($limit)
            ->values();

        return $viviendas;
    }

    /**
     * Actualizar moras de todos los aportes pendientes
     */
    private function actualizarMorasTodos()
    {
        $aportesPendientes = Aporte::whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->get();

        foreach ($aportesPendientes as $aporte) {
            $aporte->actualizarMora();
        }
    }
}
