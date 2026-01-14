<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Actividad;
use App\Models\Vivienda;
use App\Services\MoraService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AporteController extends Controller
{
    protected $moraService;
    protected $emailService;

    public function __construct(MoraService $moraService, EmailService $emailService)
    {
        $this->moraService = $moraService;
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Actualizar moras antes de mostrar
        $this->moraService->actualizarMorasVencidas();

        $user = auth()->user();

        // Si es residente o propietario, mostrar solo sus aportes
        if (in_array($user->rol, ['RESIDENTE', 'PROPIETARIO'])) {
            $residente = $user->residente;

            if (!$residente) {
                return Inertia::render('Aportes/Index', [
                    'aportes' => [],
                    'filters' => []
                ]);
            }

            $query = Aporte::with(['actividad', 'vivienda'])
                ->where('vivienda_id', $residente->vivienda_id);
        } else {
            // Admin y directorio ven todos
            $query = Aporte::with(['actividad', 'vivienda']);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado != 'TODOS') {
            $query->where('estado', $request->estado);
        }

        // Filtro por vivienda (solo para admin/directorio)
        if ($request->has('vivienda_id') && $request->vivienda_id != 'TODOS') {
            if (in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
                $query->where('vivienda_id', $request->vivienda_id);
            }
        }

        // Filtro por actividad
        if ($request->has('actividad_id') && $request->actividad_id != 'TODOS') {
            $query->where('actividad_id', $request->actividad_id);
        }

        $aportes = $query->orderBy('fecha_vencimiento', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calcular mora actualizada para cada aporte
        $aportes->getCollection()->transform(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            $aporte->total_adeudado = ($aporte->monto - $aporte->monto_pagado) + $aporte->mora_actualizada;
            return $aporte;
        });

        // Obtener listas para filtros (solo para admin/directorio)
        $viviendas = [];
        $actividades = [];

        if (in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            $viviendas = Vivienda::where('activo', true)
                ->orderBy('numero')
                ->get(['id', 'numero', 'direccion']);

            $actividades = Actividad::orderBy('fecha_inicio', 'desc')
                ->get(['id', 'titulo', 'tipo']);
        }

        return Inertia::render('Aportes/Index', [
            'aportes' => $aportes,
            'viviendas' => $viviendas,
            'actividades' => $actividades,
            'filters' => $request->only(['estado', 'vivienda_id', 'actividad_id'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $viviendas = Vivienda::where('activo', true)
            ->orderBy('numero')
            ->get();

        $actividades = Actividad::whereIn('estado', ['PLANIFICADA', 'EN_PROGRESO'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return Inertia::render('Aportes/Create', [
            'viviendas' => $viviendas,
            'actividades' => $actividades
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'actividad_id' => 'required|exists:actividades,id',
            'vivienda_id' => 'required|exists:viviendas,id',
            'monto' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        Aporte::create([
            'actividad_id' => $validated['actividad_id'],
            'vivienda_id' => $validated['vivienda_id'],
            'monto' => $validated['monto'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
            'estado' => 'PENDIENTE',
            'monto_pagado' => 0,
            'monto_mora' => 0,
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('aportes.index')
            ->with('success', 'Aporte creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aporte $aporte)
    {
        // Verificar permiso
        $user = auth()->user();

        if (in_array($user->rol, ['RESIDENTE', 'PROPIETARIO'])) {
            $residente = $user->residente;

            if (!$residente || $residente->vivienda_id !== $aporte->vivienda_id) {
                abort(403, 'No tiene permiso para ver este aporte.');
            }
        }

        $aporte->load(['actividad', 'vivienda']);

        // Calcular mora actualizada
        $aporte->mora_actualizada = $aporte->calcularMora();
        $aporte->total_adeudado = ($aporte->monto - $aporte->monto_pagado) + $aporte->mora_actualizada;

        return Inertia::render('Aportes/Show', [
            'aporte' => $aporte
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aporte $aporte)
    {
        $aporte->load(['actividad', 'vivienda']);

        // Calcular mora actualizada
        $aporte->mora_actualizada = $aporte->calcularMora();
        $aporte->total_adeudado = ($aporte->monto - $aporte->monto_pagado) + $aporte->mora_actualizada;

        $viviendas = Vivienda::where('activo', true)
            ->orderBy('numero')
            ->get();

        $actividades = Actividad::whereIn('estado', ['PLANIFICADA', 'EN_PROGRESO', 'COMPLETADA'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return Inertia::render('Aportes/Edit', [
            'aporte' => $aporte,
            'viviendas' => $viviendas,
            'actividades' => $actividades
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aporte $aporte)
    {
        $validated = $request->validate([
            'actividad_id' => 'required|exists:actividades,id',
            'vivienda_id' => 'required|exists:viviendas,id',
            'monto' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        // No permitir editar si ya está pagado
        if ($aporte->estado === 'PAGADO') {
            return back()->withErrors([
                'error' => 'No se puede editar un aporte que ya ha sido pagado.'
            ]);
        }

        $aporte->update($validated);

        return redirect()->route('aportes.index')
            ->with('success', 'Aporte actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aporte $aporte)
    {
        // No permitir eliminar si está pagado o parcialmente pagado
        if (in_array($aporte->estado, ['PAGADO', 'PARCIAL'])) {
            return back()->withErrors([
                'error' => 'No se puede eliminar un aporte con pagos registrados.'
            ]);
        }

        $aporte->delete();

        return redirect()->route('aportes.index')
            ->with('success', 'Aporte eliminado exitosamente.');
    }

    /**
     * Registrar un pago para el aporte
     */
    public function registrarPago(Request $request, Aporte $aporte)
    {
        //dd($aporte);
        $validated = $request->validate([
            'monto_pago' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:EFECTIVO,TRANSFERENCIA,DEPOSITO,TARJETA,OTRO',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones' => 'nullable|string',
        ]);

        // Actualizar mora antes de procesar el pago
        $moraActualizada = $aporte->calcularMora();
        $aporte->monto_mora = $moraActualizada;

        // Calcular totales
        $montoPendiente = $aporte->monto - $aporte->monto_pagado;
        $totalAdeudado = $montoPendiente + $aporte->monto_mora;
        $montoPago = $validated['monto_pago'];

        // Validar que el pago no exceda el total adeudado
        if ($montoPago > $totalAdeudado) {
            return back()->withErrors([
                'monto_pago' => 'El monto del pago no puede exceder el total adeudado (Bs. ' . number_format($totalAdeudado, 2) . ').'
            ]);
        }
        //dd($aporte);
        // Manejar comprobante si existe
        $comprobantePath = $aporte->comprobante; // Mantener comprobante anterior si existe
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');
        }

        // Actualizar aporte
        $nuevoMontoPagado = $aporte->monto_pagado + $montoPago;
        $aporte->monto_pagado = $nuevoMontoPagado;
        $aporte->fecha_pago = $validated['fecha_pago'];
        $aporte->metodo_pago = $validated['metodo_pago'];
        $aporte->comprobante = $comprobantePath;
        $aporte->observaciones = $validated['observaciones'] ?? $aporte->observaciones;

        // Determinar nuevo estado
        if ($nuevoMontoPagado >= ($aporte->monto + $aporte->monto_mora)) {
            $aporte->estado = 'PAGADO';
        } else {
            $aporte->estado = 'PARCIAL';
        }

        $aporte->save();

        // Enviar notificación por email
        if ($aporte->vivienda->propietario && $aporte->vivienda->propietario->user) {
            $this->emailService->enviarNotificacionPago($aporte, $aporte->vivienda->propietario->user->email);
        }

        return redirect()->route('aportes.show', $aporte)
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Enviar notificaciones de pagos vencidos
     */
    public function enviarNotificacionesVencidos()
    {
        $this->moraService->actualizarMorasVencidas();
        $this->emailService->enviarNotificacionesPagosVencidos();

        return back()->with('success', 'Notificaciones enviadas exitosamente.');
    }

    /**
     * Obtener estadísticas de aportes
     */
    public function estadisticas()
    {
        $user = auth()->user();

        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            abort(403, 'No tiene permisos para ver estas estadísticas.');
        }

        // Obtener todos los aportes con relaciones
        $aportes = Aporte::with(['actividad', 'vivienda'])->get();

        // Calcular mora actualizada para cada aporte
        $aportes = $aportes->map(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            return $aporte;
        });

        // Estadísticas globales
        $estadisticasGlobales = [
            'total_esperado' => $aportes->sum('monto'),
            'total_pagado' => $aportes->sum('monto_pagado'),
            'total_pendiente' => $aportes->sum(function($a) {
                return $a->monto - $a->monto_pagado;
            }),
            'total_mora' => $aportes->sum('mora_actualizada'),
            'total_aportes' => $aportes->count(),
            'aportes_pagados' => $aportes->where('estado', 'PAGADO')->count(),
            'aportes_pendientes' => $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
        ];

        // Estadísticas por actividad
        $estadisticasPorActividad = [];
        $actividades = Actividad::all();

        foreach ($actividades as $actividad) {
            $aportesActividad = $aportes->where('actividad_id', $actividad->id);

            if ($aportesActividad->count() > 0) {
                $totalEsperado = $aportesActividad->sum('monto');
                $totalPagado = $aportesActividad->sum('monto_pagado');
                $totalPendiente = $aportesActividad->sum(function($a) {
                    return $a->monto - $a->monto_pagado;
                });
                $totalMora = $aportesActividad->sum('mora_actualizada');

                $estadisticasPorActividad[] = [
                    'actividad_id' => $actividad->id,
                    'actividad_titulo' => $actividad->titulo,
                    'actividad_tipo' => $actividad->tipo,
                    'total_esperado' => round($totalEsperado, 2),
                    'total_pagado' => round($totalPagado, 2),
                    'total_pendiente' => round($totalPendiente, 2),
                    'total_mora' => round($totalMora, 2),
                    'porcentaje_pagado' => $totalEsperado > 0 ? round(($totalPagado / $totalEsperado) * 100, 2) : 0,
                    'numero_aportes' => $aportesActividad->count(),
                    'aportes_pagados' => $aportesActividad->where('estado', 'PAGADO')->count(),
                    'aportes_pendientes' => $aportesActividad->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
                ];
            }
        }

        // Estadísticas por vivienda
        $estadisticasPorVivienda = [];
        $viviendas = Vivienda::where('activo', true)->get();

        foreach ($viviendas as $vivienda) {
            $aportesVivienda = $aportes->where('vivienda_id', $vivienda->id);

            if ($aportesVivienda->count() > 0) {
                $totalPagado = $aportesVivienda->sum('monto_pagado');
                $totalPendiente = $aportesVivienda->sum(function($a) {
                    return $a->monto - $a->monto_pagado;
                });
                $totalMora = $aportesVivienda->sum('mora_actualizada');

                $estadisticasPorVivienda[] = [
                    'vivienda_id' => $vivienda->id,
                    'vivienda_numero' => $vivienda->numero,
                    'vivienda_direccion' => $vivienda->direccion,
                    'total_pagado' => round($totalPagado, 2),
                    'total_pendiente' => round($totalPendiente, 2),
                    'total_mora' => round($totalMora, 2),
                    'aportes_pagados' => $aportesVivienda->where('estado', 'PAGADO')->count(),
                    'aportes_pendientes' => $aportesVivienda->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
                ];
            }
        }

        // Ordenar por total pendiente (mayor a menor)
        usort($estadisticasPorVivienda, function($a, $b) {
            return $b['total_pendiente'] <=> $a['total_pendiente'];
        });

        return Inertia::render('Aportes/Estadisticas', [
            'estadisticasGlobales' => $estadisticasGlobales,
            'estadisticasPorActividad' => $estadisticasPorActividad,
            'estadisticasPorVivienda' => $estadisticasPorVivienda
        ]);
    }

    /**
     * Exportar estadísticas a PDF
     */
    public function estadisticasPDF()
    {
        $user = auth()->user();

        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            abort(403, 'No tiene permisos para exportar estas estadísticas.');
        }

        // Obtener todos los aportes con relaciones
        $aportes = Aporte::with(['actividad', 'vivienda'])->get();

        // Calcular mora actualizada para cada aporte
        $aportes = $aportes->map(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            return $aporte;
        });

        // Estadísticas globales
        $estadisticasGlobales = [
            'total_esperado' => $aportes->sum('monto'),
            'total_pagado' => $aportes->sum('monto_pagado'),
            'total_pendiente' => $aportes->sum(function($a) {
                return $a->monto - $a->monto_pagado;
            }),
            'total_mora' => $aportes->sum('mora_actualizada'),
            'total_aportes' => $aportes->count(),
            'aportes_pagados' => $aportes->where('estado', 'PAGADO')->count(),
            'aportes_pendientes' => $aportes->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
        ];

        // Estadísticas por actividad
        $estadisticasPorActividad = [];
        $actividades = Actividad::all();

        foreach ($actividades as $actividad) {
            $aportesActividad = $aportes->where('actividad_id', $actividad->id);

            if ($aportesActividad->count() > 0) {
                $totalEsperado = $aportesActividad->sum('monto');
                $totalPagado = $aportesActividad->sum('monto_pagado');
                $totalPendiente = $aportesActividad->sum(function($a) {
                    return $a->monto - $a->monto_pagado;
                });
                $totalMora = $aportesActividad->sum('mora_actualizada');

                $estadisticasPorActividad[] = [
                    'actividad_id' => $actividad->id,
                    'actividad_titulo' => $actividad->titulo,
                    'actividad_tipo' => $actividad->tipo,
                    'total_esperado' => round($totalEsperado, 2),
                    'total_pagado' => round($totalPagado, 2),
                    'total_pendiente' => round($totalPendiente, 2),
                    'total_mora' => round($totalMora, 2),
                    'porcentaje_pagado' => $totalEsperado > 0 ? round(($totalPagado / $totalEsperado) * 100, 2) : 0,
                    'numero_aportes' => $aportesActividad->count(),
                    'aportes_pagados' => $aportesActividad->where('estado', 'PAGADO')->count(),
                    'aportes_pendientes' => $aportesActividad->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
                ];
            }
        }

        // Estadísticas por vivienda
        $estadisticasPorVivienda = [];
        $viviendas = Vivienda::where('activo', true)->get();

        foreach ($viviendas as $vivienda) {
            $aportesVivienda = $aportes->where('vivienda_id', $vivienda->id);

            if ($aportesVivienda->count() > 0) {
                $totalPagado = $aportesVivienda->sum('monto_pagado');
                $totalPendiente = $aportesVivienda->sum(function($a) {
                    return $a->monto - $a->monto_pagado;
                });
                $totalMora = $aportesVivienda->sum('mora_actualizada');

                $estadisticasPorVivienda[] = [
                    'vivienda_id' => $vivienda->id,
                    'vivienda_numero' => $vivienda->numero,
                    'vivienda_direccion' => $vivienda->direccion,
                    'total_pagado' => round($totalPagado, 2),
                    'total_pendiente' => round($totalPendiente, 2),
                    'total_mora' => round($totalMora, 2),
                    'aportes_pagados' => $aportesVivienda->where('estado', 'PAGADO')->count(),
                    'aportes_pendientes' => $aportesVivienda->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
                ];
            }
        }

        // Ordenar por total pendiente
        usort($estadisticasPorVivienda, function($a, $b) {
            return $b['total_pendiente'] <=> $a['total_pendiente'];
        });

        $pdf = \PDF::loadView('reportes.estadisticas-aportes-pdf', [
            'estadisticasGlobales' => $estadisticasGlobales,
            'estadisticasPorActividad' => $estadisticasPorActividad,
            'estadisticasPorVivienda' => $estadisticasPorVivienda,
            'fecha_generacion' => \Carbon\Carbon::now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('estadisticas-aportes.pdf');
    }

    /**
     * Exportar estadísticas a CSV
     */
    public function estadisticasCSV()
    {
        $user = auth()->user();

        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            abort(403, 'No tiene permisos para exportar estas estadísticas.');
        }

        // Obtener todos los aportes con relaciones
        $aportes = Aporte::with(['actividad', 'vivienda'])->get();

        // Calcular mora actualizada para cada aporte
        $aportes = $aportes->map(function ($aporte) {
            $aporte->mora_actualizada = $aporte->calcularMora();
            return $aporte;
        });

        // Estadísticas por actividad
        $estadisticasPorActividad = [];
        $actividades = Actividad::all();

        foreach ($actividades as $actividad) {
            $aportesActividad = $aportes->where('actividad_id', $actividad->id);

            if ($aportesActividad->count() > 0) {
                $totalEsperado = $aportesActividad->sum('monto');
                $totalPagado = $aportesActividad->sum('monto_pagado');
                $totalPendiente = $aportesActividad->sum(function($a) {
                    return $a->monto - $a->monto_pagado;
                });
                $totalMora = $aportesActividad->sum('mora_actualizada');

                $estadisticasPorActividad[] = [
                    'actividad_titulo' => $actividad->titulo,
                    'actividad_tipo' => $actividad->tipo,
                    'total_esperado' => round($totalEsperado, 2),
                    'total_pagado' => round($totalPagado, 2),
                    'total_pendiente' => round($totalPendiente, 2),
                    'total_mora' => round($totalMora, 2),
                    'porcentaje_pagado' => $totalEsperado > 0 ? round(($totalPagado / $totalEsperado) * 100, 2) : 0,
                    'numero_aportes' => $aportesActividad->count(),
                    'aportes_pagados' => $aportesActividad->where('estado', 'PAGADO')->count(),
                    'aportes_pendientes' => $aportesActividad->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])->count(),
                ];
            }
        }

        $filename = 'estadisticas-aportes-por-actividad.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($estadisticasPorActividad) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'Actividad',
                'Tipo',
                'Total Esperado (Bs)',
                'Total Pagado (Bs)',
                'Total Pendiente (Bs)',
                'Mora Acumulada (Bs)',
                '% Pagado',
                'Total Aportes',
                'Aportes Pagados',
                'Aportes Pendientes'
            ]);

            // Datos
            foreach ($estadisticasPorActividad as $item) {
                fputcsv($file, [
                    $item['actividad_titulo'],
                    $item['actividad_tipo'],
                    number_format($item['total_esperado'], 2),
                    number_format($item['total_pagado'], 2),
                    number_format($item['total_pendiente'], 2),
                    number_format($item['total_mora'], 2),
                    $item['porcentaje_pagado'] . '%',
                    $item['numero_aportes'],
                    $item['aportes_pagados'],
                    $item['aportes_pendientes']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
