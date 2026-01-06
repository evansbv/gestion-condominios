<?php

namespace App\Http\Controllers;

use App\Models\Comunicacion;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ComunicacionController extends Controller
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
        $user = auth()->user();

        // Admin y directorio ven todas, otros solo las enviadas a ellos
        if (in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            $query = Comunicacion::with(['remitente', 'destinatarios']);
        } else {
            $query = Comunicacion::with(['remitente', 'destinatarios'])
                ->where('estado', 'ENVIADO')
                ->whereHas('destinatarios', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        }

        // Filtro por tipo
        if ($request->has('tipo') && $request->tipo != 'TODOS') {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por prioridad
        if ($request->has('prioridad') && $request->prioridad != 'TODOS') {
            $query->where('prioridad', $request->prioridad);
        }

        // Filtro por estado (solo para admin/directorio)
        if ($request->has('estado') && $request->estado != 'TODOS') {
            if (in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
                $query->where('estado', $request->estado);
            }
        }

        $comunicaciones = $query->orderBy('fecha_envio', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Agregar información de lectura para el usuario actual
        $comunicaciones->getCollection()->transform(function ($comunicacion) use ($user) {
            $destinatario = $comunicacion->destinatarios()
                ->where('user_id', $user->id)
                ->first();

            $comunicacion->leida = $destinatario ? $destinatario->pivot->leido : false;
            $comunicacion->fecha_lectura = $destinatario ? $destinatario->pivot->fecha_lectura : null;
            $comunicacion->total_destinatarios = $comunicacion->destinatarios()->count();
            $comunicacion->total_leidas = $comunicacion->destinatarios()
                ->wherePivot('leido', true)
                ->count();

            return $comunicacion;
        });

        return Inertia::render('Comunicaciones/Index', [
            'comunicaciones' => $comunicaciones,
            'filters' => $request->only(['tipo', 'prioridad', 'estado'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener usuarios para seleccionar destinatarios
        $usuarios = User::where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);

        return Inertia::render('Comunicaciones/Create', [
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:COMUNICADO,CONVOCATORIA,QUEJA,RECOMENDACION',
            'asunto' => 'required|string|max:200',
            'contenido' => 'required|string',
            'fecha_envio' => 'nullable|date',
            'prioridad' => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            'archivo_adjunto' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*' => 'exists:users,id',
            'enviar_email' => 'boolean',
            'guardar_como_borrador' => 'boolean',
        ]);

        // Manejar archivo adjunto si existe
        $archivoPath = null;
        if ($request->hasFile('archivo_adjunto')) {
            $archivoPath = $request->file('archivo_adjunto')->store('comunicaciones', 'public');
        }

        // Determinar estado
        $estado = $request->boolean('guardar_como_borrador') ? 'BORRADOR' : 'ENVIADO';
        $fechaEnvio = $estado === 'ENVIADO' ? ($validated['fecha_envio'] ?? now()) : null;

        // Crear comunicación
        $comunicacion = Comunicacion::create([
            'tipo' => $validated['tipo'],
            'asunto' => $validated['asunto'],
            'contenido' => $validated['contenido'],
            'prioridad' => $validated['prioridad'],
            'archivo_adjunto' => $archivoPath,
            'remitente_id' => auth()->id(),
            'fecha_envio' => $fechaEnvio,
            'estado' => $estado,
            'enviado_por_email' => false,
        ]);

        // Asociar destinatarios
        $comunicacion->destinatarios()->attach($validated['destinatarios'], [
            'leido' => false,
            'fecha_lectura' => null,
        ]);

        // Enviar por email si se solicita y no es borrador
        if ($request->boolean('enviar_email') && $estado === 'ENVIADO') {
            $emails = User::whereIn('id', $validated['destinatarios'])->pluck('email')->toArray();
            $this->emailService->enviarComunicacion($comunicacion, $emails);
            $comunicacion->update(['enviado_por_email' => true]);
        }

        return redirect()->route('comunicaciones.index')
            ->with('success', $estado === 'BORRADOR' ? 'Borrador guardado exitosamente.' : 'Comunicación enviada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comunicacion $comunicacion)
    {
        $user = auth()->user();

        // Verificar permiso
        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            if ($comunicacion->estado !== 'ENVIADO') {
                abort(403, 'No tiene permiso para ver esta comunicación.');
            }

            $esDestinatario = $comunicacion->destinatarios()
                ->where('user_id', $user->id)
                ->exists();

            if (!$esDestinatario) {
                abort(403, 'No tiene permiso para ver esta comunicación.');
            }
        }

        $comunicacion->load(['remitente', 'destinatarios']);

        // Marcar como leída para el usuario actual si es destinatario
        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            $destinatario = $comunicacion->destinatarios()
                ->where('user_id', $user->id)
                ->first();

            if ($destinatario && !$destinatario->pivot->leido) {
                $comunicacion->destinatarios()->updateExistingPivot($user->id, [
                    'leido' => true,
                    'fecha_lectura' => now(),
                ]);
            }
        }

        // Agregar estadísticas de lectura
        $comunicacion->total_destinatarios = $comunicacion->destinatarios()->count();
        $comunicacion->total_leidas = $comunicacion->destinatarios()
            ->wherePivot('leido', true)
            ->count();

        return Inertia::render('Comunicaciones/Show', [
            'comunicacion' => $comunicacion
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comunicacion $comunicacion)
    {
        // Solo se pueden editar borradores
        if ($comunicacion->estado !== 'BORRADOR') {
            return back()->withErrors([
                'error' => 'Solo se pueden editar comunicaciones en estado borrador.'
            ]);
        }

        // Solo el remitente o admin pueden editar
        if ($comunicacion->remitente_id !== auth()->id() && auth()->user()->rol !== 'ADMINISTRADOR') {
            abort(403, 'No tiene permiso para editar esta comunicación.');
        }

        $comunicacion->load('destinatarios');

        $usuarios = User::where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);

        return Inertia::render('Comunicaciones/Edit', [
            'comunicacion' => $comunicacion,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comunicacion $comunicacion)
    {
        // Solo se pueden editar borradores
        if ($comunicacion->estado !== 'BORRADOR') {
            return back()->withErrors([
                'error' => 'Solo se pueden editar comunicaciones en estado borrador.'
            ]);
        }

        // Solo el remitente o admin pueden editar
        if ($comunicacion->remitente_id !== auth()->id() && auth()->user()->rol !== 'ADMINISTRADOR') {
            abort(403, 'No tiene permiso para editar esta comunicación.');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:COMUNICADO,CONVOCATORIA,QUEJA,RECOMENDACION',
            'asunto' => 'required|string|max:200',
            'contenido' => 'required|string',
            'fecha_envio' => 'nullable|date',
            'prioridad' => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            'archivo_adjunto' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*' => 'exists:users,id',
            'enviar_email' => 'boolean',
            'guardar_como_borrador' => 'boolean',
        ]);

        // Manejar archivo adjunto si existe
        if ($request->hasFile('archivo_adjunto')) {
            // Eliminar archivo anterior si existe
            if ($comunicacion->archivo_adjunto) {
                Storage::disk('public')->delete($comunicacion->archivo_adjunto);
            }
            $validated['archivo_adjunto'] = $request->file('archivo_adjunto')->store('comunicaciones', 'public');
        }

        // Determinar estado y fecha
        $estado = $request->boolean('guardar_como_borrador') ? 'BORRADOR' : 'ENVIADO';
        $fechaEnvio = $estado === 'ENVIADO' ? ($validated['fecha_envio'] ?? now()) : null;

        // Actualizar comunicación
        $comunicacion->update([
            'tipo' => $validated['tipo'],
            'asunto' => $validated['asunto'],
            'contenido' => $validated['contenido'],
            'prioridad' => $validated['prioridad'],
            'archivo_adjunto' => $validated['archivo_adjunto'] ?? $comunicacion->archivo_adjunto,
            'estado' => $estado,
            'fecha_envio' => $fechaEnvio,
        ]);

        // Actualizar destinatarios
        $comunicacion->destinatarios()->sync(
            collect($validated['destinatarios'])->mapWithKeys(function ($userId) {
                return [$userId => ['leido' => false, 'fecha_lectura' => null]];
            })
        );

        // Enviar por email si se solicita y se está enviando (no es borrador)
        if ($request->boolean('enviar_email') && $estado === 'ENVIADO') {
            $emails = User::whereIn('id', $validated['destinatarios'])->pluck('email')->toArray();
            $this->emailService->enviarComunicacion($comunicacion, $emails);
            $comunicacion->update(['enviado_por_email' => true]);
        }

        $mensaje = $estado === 'ENVIADO' ? 'Comunicación enviada exitosamente.' : 'Cambios guardados exitosamente.';
        return redirect()->route('comunicaciones.index')
            ->with('success', $mensaje);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comunicacion $comunicacion)
    {
        // Solo se pueden eliminar borradores
        if ($comunicacion->estado !== 'BORRADOR') {
            return back()->withErrors([
                'error' => 'Solo se pueden eliminar comunicaciones en estado borrador.'
            ]);
        }

        // Solo el remitente o admin pueden eliminar
        if ($comunicacion->remitente_id !== auth()->id() && auth()->user()->rol !== 'ADMINISTRADOR') {
            abort(403, 'No tiene permiso para eliminar esta comunicación.');
        }

        // Eliminar archivo adjunto si existe
        if ($comunicacion->archivo_adjunto) {
            Storage::disk('public')->delete($comunicacion->archivo_adjunto);
        }

        // Eliminar relaciones
        $comunicacion->destinatarios()->detach();

        // Eliminar comunicación
        $comunicacion->delete();

        return redirect()->route('comunicaciones.index')
            ->with('success', 'Comunicación eliminada exitosamente.');
    }

    /**
     * Enviar comunicación borrador
     */
    public function enviar(Request $request, Comunicacion $comunicacion)
    {
        // Solo se pueden enviar borradores
        if ($comunicacion->estado !== 'BORRADOR') {
            return back()->withErrors([
                'error' => 'Esta comunicación ya ha sido enviada.'
            ]);
        }

        $validated = $request->validate([
            'enviar_email' => 'boolean',
        ]);

        // Actualizar estado
        $comunicacion->update([
            'estado' => 'ENVIADO',
            'fecha_envio' => now(),
        ]);

        // Enviar por email si se solicita
        if ($request->boolean('enviar_email')) {
            $emails = $comunicacion->destinatarios()->pluck('email')->toArray();
            $this->emailService->enviarComunicacion($comunicacion, $emails);
            $comunicacion->update(['enviado_por_email' => true]);
        }

        return redirect()->route('comunicaciones.show', $comunicacion)
            ->with('success', 'Comunicación enviada exitosamente.');
    }

    /**
     * Marcar comunicación como leída
     */
    public function marcarComoLeida(Comunicacion $comunicacion)
    {
        $user = auth()->user();

        $comunicacion->destinatarios()->updateExistingPivot($user->id, [
            'leido' => true,
            'fecha_lectura' => now(),
        ]);

        return back()->with('success', 'Comunicación marcada como leída.');
    }

    /**
     * Obtener estadísticas de comunicaciones
     */
    public function estadisticas()
    {
        $user = auth()->user();

        if (!in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'])) {
            abort(403, 'No tiene permiso para ver las estadísticas.');
        }

        $totalComunicaciones = Comunicacion::where('estado', 'ENVIADO')->count();
        $porTipo = Comunicacion::where('estado', 'ENVIADO')
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->get();

        $porPrioridad = Comunicacion::where('estado', 'ENVIADO')
            ->selectRaw('prioridad, COUNT(*) as total')
            ->groupBy('prioridad')
            ->get();

        $comunicacionesRecientes = Comunicacion::with(['remitente', 'destinatarios'])
            ->where('estado', 'ENVIADO')
            ->orderBy('fecha_envio', 'desc')
            ->limit(10)
            ->get();

        // Agregar tasa de lectura
        $comunicacionesRecientes->transform(function ($comunicacion) {
            $total = $comunicacion->destinatarios()->count();
            $leidas = $comunicacion->destinatarios()->wherePivot('leido', true)->count();
            $comunicacion->tasa_lectura = $total > 0 ? round(($leidas / $total) * 100, 2) : 0;
            return $comunicacion;
        });

        return Inertia::render('Comunicaciones/Estadisticas', [
            'totalComunicaciones' => $totalComunicaciones,
            'porTipo' => $porTipo,
            'porPrioridad' => $porPrioridad,
            'comunicacionesRecientes' => $comunicacionesRecientes
        ]);
    }
}
