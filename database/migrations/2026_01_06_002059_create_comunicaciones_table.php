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
        Schema::create('comunicaciones', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['CONVOCATORIA', 'COMUNICADO', 'RECLAMO', 'RECOMENDACION', 'NOTIFICACION']);
            $table->string('asunto');
            $table->text('contenido');
            $table->foreignId('remitente_id')->constrained('users');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->enum('prioridad', ['BAJA', 'MEDIA', 'ALTA', 'URGENTE'])->default('MEDIA');
            $table->enum('estado', ['BORRADOR', 'ENVIADO', 'LEIDO', 'RESPONDIDO', 'ARCHIVADO'])
                ->default('BORRADOR');
            $table->foreignId('reunion_relacionada_id')->nullable()->constrained('reuniones');
            $table->foreignId('actividad_relacionada_id')->nullable()->constrained('actividades');
            $table->json('adjuntos')->nullable()->comment('URLs de archivos adjuntos');
            $table->boolean('enviado_por_email')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicaciones');
    }
};
