<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->nullable()->constrained('reuniones')
                ->comment('Reunión en la que se aprobó la actividad');
            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->enum('tipo', [
                'MANTENIMIENTO_CHURRASQUERA',
                'LIMPIEZA_ACERAS',
                'MANTENIMIENTO_CALLES',
                'JARDINERIA',
                'SEGURIDAD',
                'OTRO'
            ]);
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin');
            $table->decimal('presupuesto_aprobado', 10, 2);
            $table->decimal('presupuesto_ejecutado', 10, 2)->default(0);
            $table->enum('estado', ['PLANIFICADA', 'EN_PROGRESO', 'COMPLETADA', 'CANCELADA'])
                ->default('PLANIFICADA');
            $table->integer('porcentaje_avance')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
