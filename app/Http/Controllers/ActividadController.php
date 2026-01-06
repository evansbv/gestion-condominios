<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Reunion;
use App\Models\User;
use App\Models\Vivienda;
use App\Models\Aporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActividadController extends Controller
{
    /**
     * Display a listing of the resource (Tablero Público).
     */
    public function index(Request $request)
    {
        $query = Actividad::with(['responsable', 'reunion']);

        // Filtro por estado
        if ($request->has('estado') && $request->estado != 'TODOS') {
            $query->where('estado', $request->estado);
        } else {
            // Por defecto mostrar solo activas
            $query->whereIn('estado', ['PLANIFICADA', 'EN_PROGRESO']);
        }

        // Filtro por tipo
        if ($request->has('tipo') && $request->tipo != 'TODOS') {
            $query->where('tipo', $request->tipo);
        }

        // Ordenar por fecha de inicio
        $actividades = $query->orderBy('fecha_inicio', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Agregar información de aportes para cada actividad
        $actividades->getCollection()->transform(function ($actividad) {
            $actividad->total_recaudado = $actividad->totalRecaudado;
            $actividad->total_pendiente = $actividad->totalPendiente;
            return $actividad;
        });

        return Inertia::render('Actividades/Index', [
            'actividades' => $actividades,
            'filters' => $request->only(['estado', 'tipo'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $reuniones = Reunion::where('estado', 'REALIZADA')
            ->orderBy('fecha_reunion', 'desc')
            ->get();

        $usuarios = User::whereIn('rol', ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])
            ->where('activo', true)
            ->get();

        return Inertia::render('Actividades/Create', [
            'reuniones' => $reuniones,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reunion_id' => 'nullable|exists:reuniones,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'tipo' => 'required|in:MANTENIMIENTO_CHURRASQUERA,LIMPIEZA_ACERAS,MANTENIMIENTO_CALLES,JARDINERIA,SEGURIDAD,OTRO',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'presupuesto_aprobado' => 'required|numeric|min:0',
            'responsable_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
            'generar_aportes' => 'boolean',
            'monto_por_vivienda' => 'nullable|numeric|min:0',
            'dias_vencimiento' => 'nullable|integer|min:1',
        ]);

        $actividad = Actividad::create([
            'reunion_id' => $validated['reunion_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'presupuesto_aprobado' => $validated['presupuesto_aprobado'],
            'presupuesto_ejecutado' => 0,
            'estado' => 'PLANIFICADA',
            'porcentaje_avance' => 0,
            'responsable_id' => $validated['responsable_id'] ?? auth()->id(),
            'observaciones' => $validated['observaciones'],
        ]);

        // Generar aportes automáticamente si se solicita
        if ($request->boolean('generar_aportes') && $request->filled('monto_por_vivienda')) {
            $this->generarAportes(
                $actividad,
                $request->monto_por_vivienda,
                $request->dias_vencimiento ?? 30
            );
        }

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Actividad $actividad)
    {
        $actividad->load(['responsable', 'reunion', 'aportes.vivienda']);

        // Calcular estadísticas
        $actividad->total_recaudado = $actividad->totalRecaudado;
        $actividad->total_pendiente = $actividad->totalPendiente;

        return Inertia::render('Actividades/Show', [
            'actividad' => $actividad
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actividad $actividad)
    {
        $reuniones = Reunion::where('estado', 'REALIZADA')
            ->orderBy('fecha_reunion', 'desc')
            ->get();

        $usuarios = User::whereIn('rol', ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])
            ->where('activo', true)
            ->get();

        return Inertia::render('Actividades/Edit', [
            'actividad' => $actividad,
            'reuniones' => $reuniones,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Actividad $actividad)
    {
        $validated = $request->validate([
            'reunion_id' => 'nullable|exists:reuniones,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'tipo' => 'required|in:MANTENIMIENTO_CHURRASQUERA,LIMPIEZA_ACERAS,MANTENIMIENTO_CALLES,JARDINERIA,SEGURIDAD,OTRO',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'presupuesto_aprobado' => 'required|numeric|min:0',
            'presupuesto_ejecutado' => 'nullable|numeric|min:0',
            'estado' => 'required|in:PLANIFICADA,EN_PROGRESO,COMPLETADA,CANCELADA',
            'porcentaje_avance' => 'nullable|integer|min:0|max:100',
            'responsable_id' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        $actividad->update($validated);

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Actividad $actividad)
    {
        // Verificar que no tenga aportes pagados
        $aportesPagados = $actividad->aportes()->where('estado', 'PAGADO')->count();

        if ($aportesPagados > 0) {
            return back()->withErrors([
                'error' => 'No se puede eliminar una actividad con aportes pagados.'
            ]);
        }

        // Eliminar aportes pendientes
        $actividad->aportes()->delete();

        // Eliminar actividad
        $actividad->delete();

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad eliminada exitosamente.');
    }

    /**
     * Generar aportes para todas las viviendas
     */
    private function generarAportes(Actividad $actividad, float $monto, int $diasVencimiento)
    {
        $viviendas = Vivienda::where('activo', true)->get();
        $fechaVencimiento = now()->addDays($diasVencimiento);

        foreach ($viviendas as $vivienda) {
            Aporte::create([
                'actividad_id' => $actividad->id,
                'vivienda_id' => $vivienda->id,
                'monto' => $monto,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'PENDIENTE',
                'monto_pagado' => 0,
                'monto_mora' => 0,
            ]);
        }
    }
}
