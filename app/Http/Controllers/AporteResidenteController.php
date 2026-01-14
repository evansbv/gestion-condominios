<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Residente;
use App\Models\Vivienda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AporteResidenteController extends Controller
{
    /**
     * Dashboard de aportes por residente
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $esAdmin = in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO']);

        // Obtener filtros
        $residenteId = $request->get('residente_id');
        $viviendaId = $request->get('vivienda_id');
        $mes = $request->get('mes', Carbon::now()->month);
        $año = $request->get('año', Carbon::now()->year);
        $tipoResidente = $request->get('tipo_residente');

        // Si no es admin, filtrar por su vivienda
        if (!$esAdmin) {
            $residente = $user->residente;
            if ($residente) {
                $viviendaId = $residente->vivienda_id;
                $residenteId = $residente->id;
            }
        }

        // Obtener datos para filtros
        $residentes = $esAdmin ? Residente::with('vivienda')->where('activo', true)->get() : collect();
        $viviendas = $esAdmin ? Vivienda::where('activo', true)->get() : collect();

        // Construir query de aportes
        $query = Aporte::with(['actividad', 'vivienda', 'vivienda.residentes'])
            ->whereYear('created_at', $año);

        if ($mes) {
            $query->whereMonth('created_at', $mes);
        }

        if ($viviendaId) {
            $query->where('vivienda_id', $viviendaId);
        }

        $aportes = $query->get();

        // Agrupar aportes por residente y mes
        $aportesPorResidente = $this->agruparAportesPorResidente($aportes, $residenteId, $tipoResidente);

        // Evolución mensual (últimos 12 meses)
        $evolucionMensual = $this->obtenerEvolucionMensual($viviendaId, $residenteId);

        // Estadísticas generales
        $estadisticas = $this->calcularEstadisticas($aportesPorResidente);

        return Inertia::render('Reportes/AportesPorResidente', [
            'aportesPorResidente' => $aportesPorResidente,
            'evolucionMensual' => $evolucionMensual,
            'estadisticas' => $estadisticas,
            'residentes' => $residentes,
            'viviendas' => $viviendas,
            'filtros' => [
                'residente_id' => $residenteId,
                'vivienda_id' => $viviendaId,
                'mes' => $mes,
                'año' => $año,
                'tipo_residente' => $tipoResidente
            ],
            'esAdmin' => $esAdmin
        ]);
    }

    /**
     * Agrupar aportes por residente
     */
    private function agruparAportesPorResidente($aportes, $residenteIdFiltro = null, $tipoResidenteFiltro = null)
    {
        $agrupados = [];

        foreach ($aportes as $aporte) {
            $vivienda = $aporte->vivienda;
            if (!$vivienda) continue;

            // Obtener residentes de la vivienda
            $residentes = $vivienda->residentes;

            if ($tipoResidenteFiltro) {
                $residentes = $residentes->where('tipo_residente', $tipoResidenteFiltro);
            }

            if ($residenteIdFiltro) {
                $residentes = $residentes->where('id', $residenteIdFiltro);
            }

            foreach ($residentes as $residente) {
                if (!$residente->activo) continue;

                $key = $residente->id . '-' . $vivienda->id;

                if (!isset($agrupados[$key])) {
                    $agrupados[$key] = [
                        'residente_id' => $residente->id,
                        'residente_nombre' => $residente->nombres . ' ' . $residente->apellido_paterno . ' ' . $residente->apellido_materno,
                        'residente_tipo' => $residente->tipo_residente,
                        'residente_foto' => $residente->fotografia,
                        'vivienda_id' => $vivienda->id,
                        'vivienda_numero' => $vivienda->numero,
                        'vivienda_direccion' => $vivienda->direccion,
                        'vivienda_gps' => $vivienda->ubicacion_gps ?? null,
                        'total_aportado' => 0,
                        'total_pendiente' => 0,
                        'total_mora' => 0,
                        'aportes_pagados' => 0,
                        'aportes_pendientes' => 0,
                        'aportes' => []
                    ];
                }

                // Calcular mora actualizada
                $aporte->mora_actualizada = $aporte->calcularMora();

                $montoPendiente = $aporte->monto - $aporte->monto_pagado;

                $agrupados[$key]['total_aportado'] += $aporte->monto_pagado;
                $agrupados[$key]['total_pendiente'] += $montoPendiente;
                $agrupados[$key]['total_mora'] += $aporte->mora_actualizada;

                if ($aporte->estado === 'PAGADO') {
                    $agrupados[$key]['aportes_pagados']++;
                } else {
                    $agrupados[$key]['aportes_pendientes']++;
                }

                $agrupados[$key]['aportes'][] = [
                    'id' => $aporte->id,
                    'actividad' => $aporte->actividad ? $aporte->actividad->titulo : 'Sin actividad',
                    'monto' => $aporte->monto,
                    'monto_pagado' => $aporte->monto_pagado,
                    'monto_pendiente' => $montoPendiente,
                    'mora' => $aporte->mora_actualizada,
                    'estado' => $aporte->estado,
                    'fecha_vencimiento' => $aporte->fecha_vencimiento,
                    'fecha_pago' => $aporte->fecha_pago
                ];
            }
        }

        // Convertir a array y ordenar por deuda
        $resultado = array_values($agrupados);
        usort($resultado, function ($a, $b) {
            return $b['total_pendiente'] <=> $a['total_pendiente'];
        });

        return $resultado;
    }

    /**
     * Obtener evolución mensual de aportes
     */
    private function obtenerEvolucionMensual($viviendaId = null, $residenteId = null)
    {
        $labels = [];
        $dataPagado = [];
        $dataPendiente = [];

        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $labels[] = $fecha->translatedFormat('M Y');

            $query = Aporte::whereYear('created_at', $fecha->year)
                ->whereMonth('created_at', $fecha->month);

            if ($viviendaId) {
                $query->where('vivienda_id', $viviendaId);
            }

            $aportes = $query->get();

            // Si hay filtro de residente, solo contar aportes de su vivienda
            $totalPagado = $aportes->sum('monto_pagado');
            $totalPendiente = $aportes->sum(function ($aporte) {
                return $aporte->monto - $aporte->monto_pagado;
            });

            $dataPagado[] = round($totalPagado, 2);
            $dataPendiente[] = round($totalPendiente, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pagado',
                    'data' => $dataPagado,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Pendiente',
                    'data' => $dataPendiente,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ]
            ]
        ];
    }

    /**
     * Calcular estadísticas generales
     */
    private function calcularEstadisticas($aportesPorResidente)
    {
        $totalResidentes = count($aportesPorResidente);
        $totalAportado = array_sum(array_column($aportesPorResidente, 'total_aportado'));
        $totalPendiente = array_sum(array_column($aportesPorResidente, 'total_pendiente'));
        $totalMora = array_sum(array_column($aportesPorResidente, 'total_mora'));

        $residentesAlDia = array_filter($aportesPorResidente, function ($item) {
            return $item['total_pendiente'] == 0;
        });

        $residentesConDeuda = $totalResidentes - count($residentesAlDia);

        $porcentajeCumplimiento = ($totalAportado + $totalPendiente) > 0
            ? round(($totalAportado / ($totalAportado + $totalPendiente)) * 100, 2)
            : 0;

        return [
            'total_residentes' => $totalResidentes,
            'total_aportado' => round($totalAportado, 2),
            'total_pendiente' => round($totalPendiente, 2),
            'total_mora' => round($totalMora, 2),
            'residentes_al_dia' => count($residentesAlDia),
            'residentes_con_deuda' => $residentesConDeuda,
            'porcentaje_cumplimiento' => $porcentajeCumplimiento
        ];
    }

    /**
     * Exportar a PDF
     */
    public function exportarPDF(Request $request)
    {
        $user = $request->user();
        $esAdmin = in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO']);

        $residenteId = $request->get('residente_id');
        $viviendaId = $request->get('vivienda_id');
        $mes = $request->get('mes', Carbon::now()->month);
        $año = $request->get('año', Carbon::now()->year);
        $tipoResidente = $request->get('tipo_residente');

        if (!$esAdmin) {
            $residente = $user->residente;
            if ($residente) {
                $viviendaId = $residente->vivienda_id;
                $residenteId = $residente->id;
            }
        }

        $query = Aporte::with(['actividad', 'vivienda', 'vivienda.residentes'])
            ->whereYear('created_at', $año);

        if ($mes) {
            $query->whereMonth('created_at', $mes);
        }

        if ($viviendaId) {
            $query->where('vivienda_id', $viviendaId);
        }

        $aportes = $query->get();
        $aportesPorResidente = $this->agruparAportesPorResidente($aportes, $residenteId, $tipoResidente);
        $estadisticas = $this->calcularEstadisticas($aportesPorResidente);

        $pdf = PDF::loadView('reportes.aportes-residente-pdf', [
            'aportesPorResidente' => $aportesPorResidente,
            'estadisticas' => $estadisticas,
            'filtros' => compact('mes', 'año', 'tipoResidente'),
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'esAdmin' => $esAdmin
        ]);

        return $pdf->download('aportes-por-residente-' . $año . '-' . $mes . '.pdf');
    }

    /**
     * Exportar a CSV
     */
    public function exportarCSV(Request $request)
    {
        $user = $request->user();
        $esAdmin = in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO']);

        $residenteId = $request->get('residente_id');
        $viviendaId = $request->get('vivienda_id');
        $mes = $request->get('mes', Carbon::now()->month);
        $año = $request->get('año', Carbon::now()->year);
        $tipoResidente = $request->get('tipo_residente');

        if (!$esAdmin) {
            $residente = $user->residente;
            if ($residente) {
                $viviendaId = $residente->vivienda_id;
                $residenteId = $residente->id;
            }
        }

        $query = Aporte::with(['actividad', 'vivienda', 'vivienda.residentes'])
            ->whereYear('created_at', $año);

        if ($mes) {
            $query->whereMonth('created_at', $mes);
        }

        if ($viviendaId) {
            $query->where('vivienda_id', $viviendaId);
        }

        $aportes = $query->get();
        $aportesPorResidente = $this->agruparAportesPorResidente($aportes, $residenteId, $tipoResidente);

        $filename = 'aportes-por-residente-' . $año . '-' . $mes . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($aportesPorResidente) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'Residente',
                'Tipo',
                'Vivienda',
                'Dirección',
                'Total Aportado (Bs)',
                'Total Pendiente (Bs)',
                'Mora Acumulada (Bs)',
                'Aportes Pagados',
                'Aportes Pendientes',
                '% Cumplimiento'
            ]);

            // Datos
            foreach ($aportesPorResidente as $item) {
                $totalDebe = $item['total_aportado'] + $item['total_pendiente'];
                $porcentajeCumplimiento = $totalDebe > 0
                    ? round(($item['total_aportado'] / $totalDebe) * 100, 2)
                    : 0;

                fputcsv($file, [
                    $item['residente_nombre'],
                    $item['residente_tipo'],
                    $item['vivienda_numero'],
                    $item['vivienda_direccion'],
                    number_format($item['total_aportado'], 2),
                    number_format($item['total_pendiente'], 2),
                    number_format($item['total_mora'], 2),
                    $item['aportes_pagados'],
                    $item['aportes_pendientes'],
                    $porcentajeCumplimiento . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
