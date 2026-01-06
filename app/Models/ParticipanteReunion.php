<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParticipanteReunion extends Model
{
    use HasFactory;

    protected $table = 'participantes_reunion';

    protected $fillable = [
        'reunion_id',
        'residente_id',
        'asistio',
        'representado_por',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
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
     * Relación con residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Relación con el representante
     */
    public function representante()
    {
        return $this->belongsTo(Residente::class, 'representado_por');
    }
}
