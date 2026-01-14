<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ActividadReporteController extends Controller
{
    /**
     * Tablero de actividades con visualización Kanban
     */
    public function tablero(Request $request)
    {
        $añoActual = Carbon::now()->year;
        $año = $request->get('año', $añoActual);
        $tipo = $request->get('tipo');

        // Obtener actividades del año
        $query = Actividad::with(['responsable', 'aportes'])
            ->whereYear('created_at', $año);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $actividades = $query->orderBy('fecha_inicio', 'desc')->get();

        // Agrupar por estado
        $actividadesPorEstado = [
            'PLANIFICADA' => $actividades->where('estado', 'PLANIFICADA')->values(),
            'EN_PROGRESO' => $actividades->where('estado', 'EN_PROGRESO')->values(),
            'COMPLETADA' => $actividades->where('estado', 'COMPLETADA')->values(),
            'CANCELADA' => $actividades->where('estado', 'CANCELADA')->values()
        ];

        // Estadísticas generales
        $estadisticas = $this->obtenerEstadisticas($actividades);

        // Distribución por tipo
        $distribucionPorTipo = $this->obtenerDistribucionPorTipo($actividades);

        // Tipos de actividad únicos
        $tipos = Actividad::distinct()->pluck('tipo')->filter();

        return Inertia::render('Actividades/Tablero', [
            'actividadesPorEstado' => $actividadesPorEstado,
            'estadisticas' => $estadisticas,
            'distribucionPorTipo' => $distribucionPorTipo,
            'tipos' => $tipos,
            'añoActual' => $añoActual,
            'añoSeleccionado' => $año,
            'tipoSeleccionado' => $tipo
        ]);
    }

    /**
     * Obtener estadísticas de actividades
     */
    private function obtenerEstadisticas($actividades)
    {
        $total = $actividades->count();
        $completadas = $actividades->where('estado', 'COMPLETADA')->count();
        $enProgreso = $actividades->where('estado', 'EN_PROGRESO')->count();
        $planificadas = $actividades->where('estado', 'PLANIFICADA')->count();
        $canceladas = $actividades->where('estado', 'CANCELADA')->count();

        // Calcular actividades completadas a tiempo
        $completadasATiempo = $actividades->filter(function ($actividad) {
            if ($actividad->estado !== 'COMPLETADA' || !$actividad->fecha_fin) {
                return false;
            }

            // Verificar si se completó antes o en la fecha programada
            $fechaFin = Carbon::parse($actividad->fecha_fin);
            $ultimaActualizacion = Carbon::parse($actividad->updated_at);

            return $ultimaActualizacion->lte($fechaFin);
        })->count();

        $porcentajeCompletadas = $total > 0 ? round(($completadas / $total) * 100, 2) : 0;
        $porcentajeATiempo = $completadas > 0 ? round(($completadasATiempo / $completadas) * 100, 2) : 0;

        // Presupuesto total
        $presupuestoTotal = $actividades->sum('presupuesto_aprobado');
        $presupuestoEjecutado = $actividades->sum('presupuesto_ejecutado');
        $porcentajeEjecucion = $presupuestoTotal > 0
            ? round(($presupuestoEjecutado / $presupuestoTotal) * 100, 2)
            : 0;

        return [
            'total' => $total,
            'completadas' => $completadas,
            'en_progreso' => $enProgreso,
            'planificadas' => $planificadas,
            'canceladas' => $canceladas,
            'completadas_a_tiempo' => $completadasATiempo,
            'porcentaje_completadas' => $porcentajeCompletadas,
            'porcentaje_a_tiempo' => $porcentajeATiempo,
            'presupuesto_total' => round($presupuestoTotal, 2),
            'presupuesto_ejecutado' => round($presupuestoEjecutado, 2),
            'porcentaje_ejecucion' => $porcentajeEjecucion
        ];
    }

    /**
     * Obtener distribución de actividades por tipo
     */
    private function obtenerDistribucionPorTipo($actividades)
    {
        $tipos = $actividades->groupBy('tipo')->map(function ($grupo, $tipo) {
            return [
                'tipo' => $tipo,
                'count' => $grupo->count(),
                'completadas' => $grupo->where('estado', 'COMPLETADA')->count(),
                'en_progreso' => $grupo->where('estado', 'EN_PROGRESO')->count(),
                'presupuesto' => round($grupo->sum('presupuesto_aprobado'), 2)
            ];
        })->values();

        return $tipos;
    }
}
