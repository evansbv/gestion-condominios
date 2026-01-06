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
        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_convocatoria');
            $table->timestamp('fecha_reunion');
            $table->string('lugar')->nullable();
            $table->text('orden_dia')->comment('Orden del día');
            $table->enum('estado', ['CONVOCADA', 'REALIZADA', 'CANCELADA'])->default('CONVOCADA');
            $table->text('acta')->nullable()->comment('Acta de la reunión');
            $table->json('acuerdos')->nullable()->comment('Acuerdos y decisiones tomadas');
            $table->integer('quorum')->nullable()->comment('Número de participantes presentes');
            $table->foreignId('convocada_por')->constrained('users')->comment('Usuario que convoca');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reuniones');
    }
};
