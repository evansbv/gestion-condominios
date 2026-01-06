<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Aporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'actividad_id',
        'vivienda_id',
        'monto',
        'fecha_vencimiento',
        'fecha_pago',
        'monto_pagado',
        'monto_mora',
        'estado',
        'metodo_pago',
        'comprobante',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'monto_mora' => 'decimal:2',
            'fecha_vencimiento' => 'date',
            'fecha_pago' => 'date',
        ];
    }

    /**
     * Relación con actividad
     */
    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    /**
     * Relación con vivienda
     */
    public function vivienda()
    {
        return $this->belongsTo(Vivienda::class);
    }

    /**
     * Calcular la mora del aporte
     */
    public function calcularMora()
    {
        if ($this->estado === 'PAGADO' || Carbon::now()->lte($this->fecha_vencimiento)) {
            return 0;
        }

        $diasRetraso = Carbon::now()->diffInDays($this->fecha_vencimiento);
        $diasCalculo = config('app.mora_dias_calculo', env('MORA_DIAS_CALCULO', 30));
        $periodosMora = floor($diasRetraso / $diasCalculo);

        if ($periodosMora === 0) {
            return 0;
        }

        $porcentajeMora = config('app.mora_porcentaje', env('MORA_PORCENTAJE', 5)) / 100;
        $montoPendiente = $this->monto - $this->monto_pagado;
        $mora = $montoPendiente * $porcentajeMora * $periodosMora;

        return round($mora, 2);
    }

    /**
     * Actualizar la mora del aporte
     */
    public function actualizarMora()
    {
        $moraCalculada = $this->calcularMora();

        if ($moraCalculada > 0) {
            $this->monto_mora = $moraCalculada;

            if ($this->monto_pagado > 0 && $this->monto_pagado < $this->monto) {
                $this->estado = 'PARCIAL';
            } elseif ($this->monto_pagado == 0) {
                $this->estado = 'VENCIDO';
            }

            $this->save();
        }
    }

    /**
     * Verificar si el aporte está vencido
     */
    public function estaVencido()
    {
        return Carbon::now()->gt($this->fecha_vencimiento) && $this->estado !== 'PAGADO';
    }

    /**
     * Obtener el saldo pendiente (monto pendiente + mora)
     */
    public function getSaldoPendienteAttribute()
    {
        return ($this->monto - $this->monto_pagado) + $this->monto_mora;
    }

    /**
     * Verificar si está pagado completamente
     */
    public function estaPagado()
    {
        return $this->estado === 'PAGADO';
    }
}
