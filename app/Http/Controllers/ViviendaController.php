<?php

namespace App\Http\Controllers;

use App\Models\Vivienda;
use App\Services\MoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViviendaController extends Controller
{
    protected $moraService;

    public function __construct(MoraService $moraService)
    {
        $this->moraService = $moraService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //dd(auth()->user()->rol);
        //dd($request);
        if(auth()->user()->rol!='PROPIETARIO'){   
            $query = Vivienda::with(['residentes' => function($q) {
                $q->where('activo', true);
            }])->where('activo', true);
            //dd($query);
            // Búsqueda
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%");
                });
            }

            // Filtro por tipo
            if ($request->has('tipo') && $request->tipo != 'TODOS') {
                $query->where('tipo', $request->tipo);
            }

            // Vista de mapa o lista
            $vistaMode = $request->get('vista', 'lista'); // 'lista' o 'mapa'

            if ($vistaMode === 'mapa') {
                // Para el mapa, traer todas las viviendas con coordenadas
                $viviendas = $query->get();
            } else {
                // Para lista, usar paginación
                $viviendas = $query->paginate(15)->withQueryString();
            }
            //dd($viviendas);
        }else{
            // Si es PROPIETARIO, mostrar solo su vivienda
            $query = Vivienda::with(['residentes' => function($q) {
                $q->where('activo', true);
            }])->whereHas('residentes', function($q) {
                $q->where('user_id', auth()->id())
                  ->where('activo', true);
            })->where('activo', true);
            //dd($query);

            // Búsqueda
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%");
                });
            }

            // Filtro por tipo
            if ($request->has('tipo') && $request->tipo != 'TODOS') {
                $query->where('tipo', $request->tipo);
            }

            // Vista de mapa o lista
            $vistaMode = $request->get('vista', 'lista'); // 'lista' o 'mapa'

            if ($vistaMode === 'mapa') {
                // Para el mapa, traer todas las viviendas con coordenadas
                $viviendas = $query->get();
            } else {
                // Para lista, usar paginación
                $viviendas = $query->paginate(15)->withQueryString();
            }

            $vistaMode = 'lista'; // Forzar vista de lista para propietarios
            //dd($viviendas);
        }
        return Inertia::render('Viviendas/Index', [
            'viviendas' => $viviendas,
            'filters' => $request->only(['search', 'tipo']),
            'vistaMode' => $vistaMode
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Viviendas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:viviendas,numero',
            'direccion' => 'required|string|max:255',
            'tipo' => 'required|in:CASA,DEPARTAMENTO,DUPLEX,OTRO',
            'area_m2' => 'nullable|numeric|min:0',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'numero_habitantes' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
        ]);

        Vivienda::create(array_merge($validated, ['activo' => true]));

        return redirect()->route('viviendas.index')
            ->with('success', 'Vivienda creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vivienda $vivienda)
    {
        $vivienda->load(['residentes.user', 'aportes.actividad']);

        // Obtener resumen de deuda
        $resumenDeuda = $this->moraService->obtenerResumenDeuda($vivienda->id);

        return Inertia::render('Viviendas/Show', [
            'vivienda' => $vivienda,
            'resumenDeuda' => $resumenDeuda
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vivienda $vivienda)
    {
        return Inertia::render('Viviendas/Edit', [
            'vivienda' => $vivienda
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vivienda $vivienda)
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:viviendas,numero,' . $vivienda->id,
            'direccion' => 'required|string|max:255',
            'tipo' => 'required|in:CASA,DEPARTAMENTO,DUPLEX,OTRO',
            'area_m2' => 'nullable|numeric|min:0',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'numero_habitantes' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $vivienda->update($validated);

        return redirect()->route('viviendas.index')
            ->with('success', 'Vivienda actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vivienda $vivienda)
    {
        // Verificar que no tenga residentes activos
        if ($vivienda->residentesActivos()->count() > 0) {
            return back()->withErrors([
                'error' => 'No se puede desactivar una vivienda con residentes activos.'
            ]);
        }

        // Soft delete
        $vivienda->update(['activo' => false]);

        return redirect()->route('viviendas.index')
            ->with('success', 'Vivienda desactivada exitosamente.');
    }
}
