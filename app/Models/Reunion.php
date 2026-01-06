<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reunion extends Model
{
    use HasFactory;

    protected $table = 'reuniones';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_convocatoria',
        'fecha_reunion',
        'lugar',
        'orden_dia',
        'estado',
        'acta',
        'acuerdos',
        'quorum',
        'convocada_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_convocatoria' => 'datetime',
            'fecha_reunion' => 'datetime',
            'acuerdos' => 'array',
        ];
    }

    /**
     * Relación con el usuario que convoca
     */
    public function convocante()
    {
        return $this->belongsTo(User::class, 'convocada_por');
    }

    /**
     * Relación con participantes
     */
    public function participantes()
    {
        return $this->hasMany(ParticipanteReunion::class);
    }

    /**
     * Relación muchos a muchos con residentes
     */
    public function residentes()
    {
        return $this->belongsToMany(Residente::class, 'participantes_reunion')
            ->withPivot('asistio', 'representado_por', 'observaciones')
            ->withTimestamps();
    }

    /**
     * Relación con actividades generadas
     */
    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    /**
     * Relación con comunicaciones relacionadas
     */
    public function comunicaciones()
    {
        return $this->hasMany(Comunicacion::class, 'reunion_relacionada_id');
    }

    /**
     * Verificar si la reunión ya fue realizada
     */
    public function fueRealizada()
    {
        return $this->estado === 'REALIZADA';
    }

    /**
     * Verificar si la reunión está pendiente
     */
    public function estaPendiente()
    {
        return $this->estado === 'CONVOCADA';
    }
}
