<?php

namespace App\Services;

use App\Models\Aporte;
use App\Models\Vivienda;
use Illuminate\Support\Facades\DB;

class MoraService
{
    /**
     * Actualizar todas las moras vencidas
     */
    public function actualizarMorasVencidas()
    {
        $aportesVencidos = Aporte::whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->where('fecha_vencimiento', '<', now())
            ->get();

        $actualizados = 0;

        DB::transaction(function () use ($aportesVencidos, &$actualizados) {
            foreach ($aportesVencidos as $aporte) {
                $aporte->actualizarMora();
                $actualizados++;
            }
        });

        return [
            'success' => true,
            'message' => "{$actualizados} aportes actualizados con mora.",
            'cantidad' => $actualizados
        ];
    }

    /**
     * Obtener resumen de deuda de una vivienda
     */
    public function obtenerResumenDeuda(int $viviendaId)
    {
        $vivienda = Vivienda::findOrFail($viviendaId);

        $aportes = $vivienda->aportes;

        $totalPendiente = 0;
        $totalMora = 0;
        $totalPagado = 0;
        $aportesPendientes = 0;

        foreach ($aportes as $aporte) {
            $totalPagado += $aporte->monto_pagado;

            if ($aporte->estado !== 'PAGADO') {
                $montoPendiente = $aporte->monto - $aporte->monto_pagado;
                $totalPendiente += $montoPendiente;

                // Actualizar y obtener mora
                $aporte->actualizarMora();
                $totalMora += $aporte->monto_mora;

                $aportesPendientes++;
            }
        }

        return [
            'vivienda_id' => $viviendaId,
            'vivienda_numero' => $vivienda->numero,
            'total_pendiente' => round($totalPendiente, 2),
            'total_mora' => round($totalMora, 2),
            'total_pagado' => round($totalPagado, 2),
            'total_deuda' => round($totalPendiente + $totalMora, 2),
            'aportes_pendientes' => $aportesPendientes,
            'aportes_totales' => $aportes->count()
        ];
    }

    /**
     * Obtener todos los aportes vencidos del sistema
     */
    public function obtenerAportesVencidos()
    {
        return Aporte::with(['vivienda', 'actividad'])
            ->whereIn('estado', ['VENCIDO', 'PARCIAL'])
            ->where('fecha_vencimiento', '<', now())
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Calcular estadísticas de mora del condominio
     */
    public function obtenerEstadisticasMora()
    {
        $totalAportes = Aporte::count();
        $aportesPagados = Aporte::where('estado', 'PAGADO')->count();
        $aportesVencidos = Aporte::whereIn('estado', ['VENCIDO', 'PARCIAL'])->count();
        $aportesPendientes = Aporte::where('estado', 'PENDIENTE')->count();

        $totalMora = Aporte::whereIn('estado', ['VENCIDO', 'PARCIAL'])->sum('monto_mora');
        $totalPendiente = Aporte::whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->get()
            ->sum(function ($aporte) {
                return $aporte->monto - $aporte->monto_pagado;
            });

        return [
            'total_aportes' => $totalAportes,
            'aportes_pagados' => $aportesPagados,
            'aportes_vencidos' => $aportesVencidos,
            'aportes_pendientes' => $aportesPendientes,
            'total_mora' => round($totalMora, 2),
            'total_pendiente' => round($totalPendiente, 2),
            'total_deuda' => round($totalPendiente + $totalMora, 2),
            'porcentaje_pagados' => $totalAportes > 0 ? round(($aportesPagados / $totalAportes) * 100, 2) : 0
        ];
    }
}
