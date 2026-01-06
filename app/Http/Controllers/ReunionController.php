<?php

namespace App\Http\Controllers;

use App\Models\Reunion;
use App\Models\Residente;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReunionController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $query = Reunion::with(['convocante', 'participantes']);

        // Filtro por estado
        if ($request->has('estado') && $request->estado != 'TODOS') {
            $query->where('estado', $request->estado);
        }

        // Filtro por año
        if ($request->has('anio') && $request->anio != 'TODOS') {
            $query->whereYear('fecha_reunion', $request->anio);
        }

        $reuniones = $query->orderBy('fecha_reunion', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Agregar información adicional        
        $reuniones->getCollection()->transform(function ($reunion) {
            $reunion->total_participantes = $reunion->participantes()->count();
            $reunion->participantes_asistieron = $reunion->participantes()
                ->wherePivot('asistio', true)
                ->count();
            return $reunion;
        });
        

        // Obtener años disponibles para el filtro
        $aniosDisponibles = Reunion::selectRaw('DISTINCT EXTRACT(YEAR FROM fecha_reunion) as anio')
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        return Inertia::render('Reuniones/Index', [
            'reuniones' => $reuniones,
            'aniosDisponibles' => $aniosDisponibles,
            'filters' => $request->only(['estado', 'anio'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Reuniones/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'fecha_convocatoria' => 'required|date',
            'fecha_reunion' => 'required|date|after:fecha_convocatoria',
            'lugar' => 'required|string|max:255',
            'orden_dia' => 'required|string',
            'enviar_convocatoria' => 'boolean',
        ]);

        $reunion = Reunion::create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'fecha_convocatoria' => $validated['fecha_convocatoria'],
            'fecha_reunion' => $validated['fecha_reunion'],
            'lugar' => $validated['lugar'],
            'orden_dia' => $validated['orden_dia'],
            'estado' => 'CONVOCADA',
            'convocada_por' => auth()->id(),
        ]);

        // Enviar convocatorias por email si se solicita
        if ($request->boolean('enviar_convocatoria')) {
            $this->enviarConvocatorias($reunion);
        }

        return redirect()->route('reuniones.index')
            ->with('success', 'Reunión creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reunion $reunion)
    {
        $reunion->load([
            'convocante',
            'participantes.residente.vivienda',
            'actividades'
        ]);

        // Contar participación
        $reunion->total_convocados = $reunion->participantes()->count();
        $reunion->total_asistieron = $reunion->participantes()
            ->wherePivot('asistio', true)
            ->count();

        return Inertia::render('Reuniones/Show', [
            'reunion' => $reunion
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reunion $reunion)
    {
        // No permitir editar reuniones ya realizadas
        if ($reunion->estado === 'REALIZADA') {
            return back()->withErrors([
                'error' => 'No se puede editar una reunión que ya fue realizada.'
            ]);
        }

        return Inertia::render('Reuniones/Edit', [
            'reunion' => $reunion
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reunion $reunion)
    {
        // No permitir editar reuniones ya realizadas
        if ($reunion->estado === 'REALIZADA') {
            return back()->withErrors([
                'error' => 'No se puede editar una reunión que ya fue realizada.'
            ]);
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'fecha_convocatoria' => 'required|date',
            'fecha_reunion' => 'required|date|after:fecha_convocatoria',
            'lugar' => 'required|string|max:255',
            'orden_dia' => 'required|string',
            'estado' => 'required|in:CONVOCADA,REALIZADA,CANCELADA',
        ]);

        $reunion->update($validated);

        return redirect()->route('reuniones.index')
            ->with('success', 'Reunión actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reunion $reunion)
    {
        // No permitir eliminar reuniones realizadas
        if ($reunion->estado === 'REALIZADA') {
            return back()->withErrors([
                'error' => 'No se puede eliminar una reunión que ya fue realizada.'
            ]);
        }

        // Eliminar participaciones
        $reunion->participantes()->detach();

        // Eliminar reunión
        $reunion->delete();

        return redirect()->route('reuniones.index')
            ->with('success', 'Reunión eliminada exitosamente.');
    }

    /**
     * Registrar participación en la reunión
     */
    public function registrarParticipacion(Request $request, Reunion $reunion)
    {
        $validated = $request->validate([
            'participantes' => 'required|array',
            'participantes.*.residente_id' => 'required|exists:residentes,id',
            'participantes.*.asistio' => 'required|boolean',
            'participantes.*.observaciones' => 'nullable|string',
        ]);

        // Sincronizar participantes
        $participantes = [];
        foreach ($validated['participantes'] as $participante) {
            $participantes[$participante['residente_id']] = [
                'asistio' => $participante['asistio'],
                'observaciones' => $participante['observaciones'] ?? null,
            ];
        }

        $reunion->participantes()->sync($participantes);

        return back()->with('success', 'Participación registrada exitosamente.');
    }

    /**
     * Registrar acta de la reunión
     */
    public function registrarActa(Request $request, Reunion $reunion)
    {
        $validated = $request->validate([
            'acta' => 'required|string',
            'acuerdos' => 'required|array',
            'acuerdos.*.descripcion' => 'required|string',
            'acuerdos.*.responsable_id' => 'nullable|exists:users,id',
            'acuerdos.*.fecha_limite' => 'nullable|date',
        ]);

        $reunion->update([
            'acta' => $validated['acta'],
            'acuerdos' => $validated['acuerdos'],
            'estado' => 'REALIZADA',
        ]);

        return redirect()->route('reuniones.show', $reunion)
            ->with('success', 'Acta registrada exitosamente.');
    }

    /**
     * Enviar convocatorias por email
     */
    public function enviarConvocatorias(Reunion $reunion)
    {
        // Obtener emails de todos los propietarios activos
        $destinatarios = User::whereIn('rol', ['PROPIETARIO', 'MIEMBRO_DIRECTORIO'])
            ->where('activo', true)
            ->pluck('email')
            ->toArray();

        if (empty($destinatarios)) {
            return back()->with('warning', 'No hay destinatarios disponibles para enviar la convocatoria.');
        }

        $this->emailService->enviarConvocatoria($reunion, $destinatarios);

        return back()->with('success', 'Convocatorias enviadas exitosamente a ' . count($destinatarios) . ' destinatarios.');
    }

    /**
     * Mostrar formulario para registrar participantes
     */
    public function participantes(Reunion $reunion)
    {
        $reunion->load(['participantes.residente.vivienda']);

        // Obtener todos los residentes activos
        $residentes = Residente::with('vivienda')
            ->where('activo', true)
            ->whereIn('tipo_residente', ['PROPIETARIO', 'INQUILINO'])
            ->get();

        // Marcar quienes ya están registrados
        $residentes->transform(function ($residente) use ($reunion) {
            $participacion = $reunion->participantes()
                ->where('residente_id', $residente->id)
                ->first();

            $residente->ya_registrado = $participacion !== null;
            $residente->asistio = $participacion ? $participacion->pivot->asistio : false;
            $residente->observaciones = $participacion ? $participacion->pivot->observaciones : null;

            return $residente;
        });

        return Inertia::render('Reuniones/Participantes', [
            'reunion' => $reunion,
            'residentes' => $residentes
        ]);
    }

    /**
     * Mostrar formulario para registrar acta
     */
    public function acta(Reunion $reunion)
    {
        $reunion->load(['participantes.residente.vivienda', 'convocante']);

        // Obtener usuarios que pueden ser responsables de acuerdos
        $responsables = User::whereIn('rol', ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])
            ->where('activo', true)
            ->get(['id', 'name', 'rol']);

        return Inertia::render('Reuniones/Acta', [
            'reunion' => $reunion,
            'responsables' => $responsables
        ]);
    }

    /**
     * Obtener calendario de reuniones
     */
    public function calendario(Request $request)
    {
        $anio = $request->get('anio', now()->year);
        $mes = $request->get('mes', now()->month);

        $reuniones = Reunion::whereYear('fecha_reunion', $anio)
            ->whereMonth('fecha_reunion', $mes)
            ->orderBy('fecha_reunion')
            ->get(['id', 'titulo', 'fecha_reunion', 'lugar', 'estado']);

        return Inertia::render('Reuniones/Calendario', [
            'reuniones' => $reuniones,
            'anio' => $anio,
            'mes' => $mes
        ]);
    }
}
