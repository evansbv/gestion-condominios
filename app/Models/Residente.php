<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Residente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vivienda_id',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'fecha_nacimiento',
        'telefono',
        'celular',
        'fotografia',
        'tipo_residente',
        'fecha_ingreso',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * Relación con usuario (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con vivienda (N:1)
     */
    public function vivienda()
    {
        return $this->belongsTo(Vivienda::class);
    }

    /**
     * Relación muchos a muchos con reuniones (participaciones)
     */
    public function reuniones()
    {
        return $this->belongsToMany(Reunion::class, 'participantes_reunion')
            ->withPivot('asistio', 'representado_por', 'observaciones')
            ->withTimestamps();
    }

    /**
     * Obtener nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    /**
     * Verificar si es propietario
     */
    public function esPropietario()
    {
        return $this->tipo_residente === 'PROPIETARIO';
    }

    /**
     * Verificar si es inquilino
     */
    public function esInquilino()
    {
        return $this->tipo_residente === 'INQUILINO';
    }
}
