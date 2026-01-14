<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AporteActividadController extends Controller
{
    /**
     * Dashboard de aportes por actividad
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $esAdmin = in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO']);

        // Obtener filtros
        $actividadId = $request->get('actividad_id');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $estado = $request->get('estado');

        // Obtener todas las actividades para el selector
        $actividades = Actividad::orderBy('created_at', 'desc')->get();

        // Si hay filtro de actividad, obtener detalles
        $actividadSeleccionada = null;
        $aportes = collect();
        $distribucionPorResidente = [];
        $estadisticas = [
            'total_recaudado' => 0,
            'total_pendiente' => 0,
            'total_mora' => 0,
            'numero_aportantes' => 0,
            'aportes_pagados' => 0,
            'aportes_pendientes' => 0
        ];

        if ($actividadId) {
            $actividadSeleccionada = Actividad::with(['responsable', 'reunion'])->find($actividadId);

            // Construir query de aportes
            $query = Aporte::with(['vivienda.residentes', 'actividad'])
                ->where('actividad_id', $actividadId);

            if ($fechaInicio) {
                $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
            }

            if ($fechaFin) {
                $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
            }

            if ($estado) {
                $query->where('estado', $estado);
            }

            $aportes = $query->orderBy('created_at', 'desc')->get()->map(function ($aporte) {
                $aporte->mora_actualizada = $aporte->calcularMora();
                return $aporte;
            });

            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticas($aportes);

            // Distribución por residente
            $distribucionPorResidente = $this->obtenerDistribucionPorResidente($aportes);
        }

        return Inertia::render('Reportes/AportesPorActividad', [
            'actividades' => $actividades,
            'actividadSeleccionada' => $actividadSeleccionada,
            'aportes' => $aportes,
            'distribucionPorResidente' => $distribucionPorResidente,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'actividad_id' => $actividadId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado
            ],
            'esAdmin' => $esAdmin
        ]);
    }

    /**
     * Calcular estadísticas de aportes
     */
    private function calcularEstadisticas($aportes)
    {
        $totalRecaudado = $aportes->sum('monto_pagado');
        $totalPendiente = $aportes->sum(function ($aporte) {
            return $aporte->monto - $aporte->monto_pagado;
        });
        $totalMora = $aportes->sum('mora_actualizada');

        $aportantesPagaron = $aportes->filter(function ($aporte) {
            return $aporte->monto_pagado > 0;
        })->pluck('vivienda_id')->unique()->count();

        $aportesPagados = $aportes->where('estado', 'PAGADO')->count();
        $aportesPendientes = $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count();

        return [
            'total_recaudado' => round($totalRecaudado, 2),
            'total_pendiente' => round($totalPendiente, 2),
            'total_mora' => round($totalMora, 2),
            'numero_aportantes' => $aportantesPagaron,
            'aportes_pagados' => $aportesPagados,
            'aportes_pendientes' => $aportesPendientes
        ];
    }

    /**
     * Obtener distribución de aportes por residente
     */
    private function obtenerDistribucionPorResidente($aportes)
    {
        $distribucion = [];

        foreach ($aportes as $aporte) {
            $vivienda = $aporte->vivienda;
            if (!$vivienda) continue;

            // Obtener propietario principal de la vivienda
            $propietario = $vivienda->residentes->where('tipo_residente', 'PROPIETARIO')->first();

            if (!$propietario) {
                $propietario = $vivienda->residentes->first();
            }

            if (!$propietario) continue;

            $key = $propietario->id;

            if (!isset($distribucion[$key])) {
                $distribucion[$key] = [
                    'residente_id' => $propietario->id,
                    'residente_nombre' => $propietario->nombres . ' ' . $propietario->apellido_paterno,
                    'vivienda_numero' => $vivienda->numero,
                    'total_aportado' => 0,
                    'total_pendiente' => 0
                ];
            }

            $distribucion[$key]['total_aportado'] += $aporte->monto_pagado;
            $distribucion[$key]['total_pendiente'] += ($aporte->monto - $aporte->monto_pagado);
        }

        // Ordenar por total aportado
        $resultado = array_values($distribucion);
        usort($resultado, function ($a, $b) {
            return $b['total_aportado'] <=> $a['total_aportado'];
        });

        return $resultado;
    }

    /**
     * Exportar a PDF
     */
    public function exportarPDF(Request $request)
    {
        $actividadId = $request->get('actividad_id');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $estado = $request->get('estado');

        if (!$actividadId) {
            return back()->withErrors(['error' => 'Debe seleccionar una actividad']);
        }

        $actividadSeleccionada = Actividad::with(['responsable', 'reunion'])->find($actividadId);

        $query = Aporte::with(['vivienda.residentes', 'actividad'])
            ->where('actividad_id', $actividadId);

        if ($fechaInicio) {
            $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
        }

        if ($fechaFin) {
            $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $aportes = $query->orderBy('created_at', 'desc')->get()->map(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            return $aporte;
        });

        $estadisticas = $this->calcularEstadisticas($aportes);
        $distribucionPorResidente = $this->obtenerDistribucionPorResidente($aportes);

        $pdf = PDF::loadView('reportes.aportes-actividad-pdf', [
            'actividad' => $actividadSeleccionada,
            'aportes' => $aportes,
            'distribucionPorResidente' => $distribucionPorResidente,
            'estadisticas' => $estadisticas,
            'filtros' => compact('fechaInicio', 'fechaFin', 'estado'),
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i')
        ]);

        $filename = 'aportes-actividad-' . $actividadSeleccionada->id . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Exportar a CSV
     */
    public function exportarCSV(Request $request)
    {
        $actividadId = $request->get('actividad_id');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $estado = $request->get('estado');

        if (!$actividadId) {
            return back()->withErrors(['error' => 'Debe seleccionar una actividad']);
        }

        $actividadSeleccionada = Actividad::find($actividadId);

        $query = Aporte::with(['vivienda.residentes'])
            ->where('actividad_id', $actividadId);

        if ($fechaInicio) {
            $query->where('created_at', '>=', Carbon::parse($fechaInicio)->startOfDay());
        }

        if ($fechaFin) {
            $query->where('created_at', '<=', Carbon::parse($fechaFin)->endOfDay());
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $aportes = $query->orderBy('created_at', 'desc')->get()->map(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            return $aporte;
        });

        $filename = 'aportes-actividad-' . $actividadId . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($aportes, $actividadSeleccionada) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'Actividad',
                'Vivienda',
                'Residente',
                'Fecha Aporte',
                'Fecha Vencimiento',
                'Fecha Pago',
                'Monto (Bs)',
                'Monto Pagado (Bs)',
                'Monto Pendiente (Bs)',
                'Mora (Bs)',
                'Estado'
            ]);

            // Datos
            foreach ($aportes as $aporte) {
                $vivienda = $aporte->vivienda;
                $propietario = $vivienda ? $vivienda->residentes->where('tipo_residente', 'PROPIETARIO')->first() : null;
                $residenteNombre = $propietario
                    ? $propietario->nombres . ' ' . $propietario->apellido_paterno
                    : 'N/A';

                fputcsv($file, [
                    $actividadSeleccionada->titulo,
                    $vivienda ? $vivienda->numero : 'N/A',
                    $residenteNombre,
                    $aporte->created_at->format('d/m/Y'),
                    $aporte->fecha_vencimiento->format('d/m/Y'),
                    $aporte->fecha_pago ? $aporte->fecha_pago->format('d/m/Y') : 'No pagado',
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
}
