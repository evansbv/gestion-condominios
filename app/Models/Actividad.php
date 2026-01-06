<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividades';

    protected $fillable = [
        'reunion_id',
        'titulo',
        'descripcion',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'presupuesto_aprobado',
        'presupuesto_ejecutado',
        'estado',
        'porcentaje_avance',
        'responsable_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'presupuesto_aprobado' => 'decimal:2',
            'presupuesto_ejecutado' => 'decimal:2',
            'porcentaje_avance' => 'integer',
        ];
    }

    /**
     * Relación con reunión
     */
    public function reunion()
    {
        return $this->belongsTo(Reunion::class);
    }

    /**
     * Relación con responsable
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Relación con aportes
     */
    public function aportes()
    {
        return $this->hasMany(Aporte::class);
    }

    /**
     * Relación con comunicaciones relacionadas
     */
    public function comunicaciones()
    {
        return $this->hasMany(Comunicacion::class, 'actividad_relacionada_id');
    }

    /**
     * Verificar si la actividad está completada
     */
    public function estaCompletada()
    {
        return $this->estado === 'COMPLETADA';
    }

    /**
     * Verificar si la actividad está en progreso
     */
    public function estaEnProgreso()
    {
        return $this->estado === 'EN_PROGRESO';
    }

    /**
     * Obtener el total recaudado de aportes
     */
    public function getTotalRecaudadoAttribute()
    {
        return $this->aportes()->sum('monto_pagado');
    }

    /**
     * Obtener el total pendiente de aportes
     */
    public function getTotalPendienteAttribute()
    {
        return $this->aportes()
            ->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->get()
            ->sum(function ($aporte) {
                return $aporte->monto - $aporte->monto_pagado;
            });
    }
}
