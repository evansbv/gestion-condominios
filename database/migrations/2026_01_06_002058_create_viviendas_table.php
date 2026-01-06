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
        Schema::create('viviendas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique()->comment('Número o identificador de la vivienda');
            $table->string('direccion');
            $table->enum('tipo', ['CASA', 'DEPARTAMENTO', 'DUPLEX', 'OTRO'])->default('CASA');
            $table->decimal('area_m2', 10, 2)->nullable()->comment('Área en metros cuadrados');
            $table->decimal('latitud', 10, 8)->comment('Coordenada GPS - Latitud');
            $table->decimal('longitud', 11, 8)->comment('Coordenada GPS - Longitud');
            $table->integer('numero_habitantes')->default(0);
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viviendas');
    }
};
