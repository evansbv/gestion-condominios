<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vivienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'direccion',
        'tipo',
        'area_m2',
        'latitud',
        'longitud',
        'numero_habitantes',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'latitud' => 'decimal:8',
            'longitud' => 'decimal:8',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con residentes (1:N)
     */
    public function residentes()
    {
        return $this->hasMany(Residente::class);
    }

    /**
     * Relación con aportes (1:N)
     */
    public function aportes()
    {
        return $this->hasMany(Aporte::class);
    }

    /**
     * Obtener el propietario de la vivienda
     */
    public function propietario()
    {
        return $this->hasOne(Residente::class)
            ->where('tipo_residente', 'PROPIETARIO')
            ->where('activo', true);
    }

    /**
     * Obtener residentes activos
     */
    public function residentesActivos()
    {
        return $this->residentes()->where('activo', true);
    }

    /**
     * Obtener el saldo total de aportes pendientes
     */
    public function getSaldoPendienteAttribute()
    {
        return $this->aportes()
            ->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PARCIAL'])
            ->get()
            ->sum(function ($aporte) {
                return ($aporte->monto - $aporte->monto_pagado) + $aporte->monto_mora;
            });
    }
}
