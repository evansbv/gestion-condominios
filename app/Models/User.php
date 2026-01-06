<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    /**
     * Relación con residente (1:1)
     */
    public function residente()
    {
        return $this->hasOne(Residente::class);
    }

    /**
     * Relación con reuniones convocadas
     */
    public function reunionesConvocadas()
    {
        return $this->hasMany(Reunion::class, 'convocada_por');
    }

    /**
     * Relación con actividades como responsable
     */
    public function actividadesResponsable()
    {
        return $this->hasMany(Actividad::class, 'responsable_id');
    }

    /**
     * Relación con comunicaciones enviadas
     */
    public function comunicacionesEnviadas()
    {
        return $this->hasMany(Comunicacion::class, 'remitente_id');
    }

    /**
     * Relación con comunicaciones recibidas
     */
    public function comunicacionesRecibidas()
    {
        return $this->belongsToMany(Comunicacion::class, 'destinatarios_comunicacion', 'user_id', 'comunicacion_id')
            ->withPivot('leido', 'fecha_lectura')
            ->withTimestamps();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return $this->rol === $role;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->rol, $roles);
    }
}
