<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\Vivienda;
use App\Models\Reunion;
use App\Models\Actividad;
use App\Models\Aporte;
use App\Models\Comunicacion;
use App\Services\MoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $moraService;

    public function __construct(MoraService $moraService)
    {
        $this->moraService = $moraService;
    }

    /**
     * Mostrar dashboard principal
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Estadísticas generales
        $estadisticas = [
            'total_viviendas' => Vivienda::where('activo', true)->count(),
            'total_residentes' => Residente::where('activo', true)->count(),
            'reuniones_pendientes' => Reunion::where('estado', 'CONVOCADA')
                ->where('fecha_reunion', '>=', now())
                ->count(),
            'actividades_activas' => Actividad::whereIn('estado', ['PLANIFICADA', 'EN_PROGRESO'])->count(),
        ];

        // Estadísticas de aportes y mora
        $estadisticasMora = $this->moraService->obtenerEstadisticasMora();

        // Últimas comunicaciones
        if (in_array($user->rol, ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO', ])) {
            $ultimasComunicaciones = $query = Comunicacion::with(['remitente', 'destinatarios'])
            ->latest('fecha_envio')
            ->take(5)
            ->get();
        } else {
        $ultimasComunicaciones = Comunicacion::with('remitente')
            //->where('estado', 'ENVIADO')
            ->where(function ($q) use ($user) {
                $q->whereHas('destinatarios', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                })
                ->orWhereHas('remitente', function ($sub) use ($user) {
                    // Aquí NO usas where('user_id', ...) porque la tabla es users
                    $sub->where('id', $user->id);   // ← clave: comparas el id del usuario con la PK de users
                });
            })
            ->latest('fecha_envio')
            ->take(5)
            ->get();
        }
        // Próximas reuniones
        $proximasReuniones = Reunion::with('convocante')
            ->where('estado', 'CONVOCADA')
            ->where('fecha_reunion', '>=', now())
            ->orderBy('fecha_reunion', 'asc')
            ->take(3)
            ->get();

        // Actividades recientes
        $actividadesRecientes = Actividad::with('responsable')
            ->latest()
            ->take(5)
            ->get();

        // Si es residente, obtener datos específicos de su vivienda
        $datosVivienda = null;
        if ($user->residente) {
            $datosVivienda = $this->moraService->obtenerResumenDeuda($user->residente->vivienda_id);
        }

        return Inertia::render('Dashboard', [
            'estadisticas' => $estadisticas,
            'estadisticasMora' => $estadisticasMora,
            'ultimasComunicaciones' => $ultimasComunicaciones,
            'proximasReuniones' => $proximasReuniones,
            'actividadesRecientes' => $actividadesRecientes,
            'datosVivienda' => $datosVivienda,
        ]);
    }
}
