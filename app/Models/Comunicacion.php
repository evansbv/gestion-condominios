<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comunicacion extends Model
{
    use HasFactory;

    protected $table = 'comunicaciones';

    protected $fillable = [
        'tipo',
        'asunto',
        'contenido',
        'archivo_adjunto',
        'remitente_id',
        'fecha_envio',
        'prioridad',
        'estado',
        'reunion_relacionada_id',
        'actividad_relacionada_id',
        'adjuntos',
        'enviado_por_email',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
            'adjuntos' => 'array',
            'enviado_por_email' => 'boolean',
        ];
    }

    /**
     * Relación con remitente
     */
    public function remitente()
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    /**
     * Relación con reunión relacionada
     */
    public function reunionRelacionada()
    {
        return $this->belongsTo(Reunion::class, 'reunion_relacionada_id');
    }

    /**
     * Relación con actividad relacionada
     */
    public function actividadRelacionada()
    {
        return $this->belongsTo(Actividad::class, 'actividad_relacionada_id');
    }

    /**
     * Relación con destinatarios
     */
    public function destinatarios()
    {
        return $this->belongsToMany(User::class, 'destinatarios_comunicacion', 'comunicacion_id', 'user_id')
            ->withPivot('leido', 'fecha_lectura')
            ->withTimestamps();
    }

    /**
     * Relación con registros de destinatarios
     */
    public function destinatariosRegistros()
    {
        return $this->hasMany(DestinatarioComunicacion::class);
    }

    /**
     * Verificar si fue enviada
     */
    public function fueEnviada()
    {
        return $this->estado === 'ENVIADO';
    }

    /**
     * Verificar si es borrador
     */
    public function esBorrador()
    {
        return $this->estado === 'BORRADOR';
    }

    /**
     * Marcar como enviada
     */
    public function marcarComoEnviada()
    {
        $this->estado = 'ENVIADO';
        $this->fecha_envio = now();
        $this->save();
    }
}
