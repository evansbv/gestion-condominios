<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DestinatarioComunicacion extends Model
{
    use HasFactory;

    protected $table = 'destinatarios_comunicacion';

    protected $fillable = [
        'comunicacion_id',
        'user_id',
        'leido',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'leido' => 'boolean',
            'fecha_lectura' => 'datetime',
        ];
    }

    /**
     * Relación con comunicación
     */
    public function comunicacion()
    {
        return $this->belongsTo(Comunicacion::class);
    }

    /**
     * Relación con usuario destinatario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Marcar como leído
     */
    public function marcarComoLeido()
    {
        if (!$this->leido) {
            $this->leido = true;
            $this->fecha_lectura = now();
            $this->save();
        }
    }
}
