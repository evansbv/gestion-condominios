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
        Schema::create('aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->onDelete('cascade');
            $table->foreignId('vivienda_id')->constrained('viviendas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('monto_mora', 10, 2)->default(0)
                ->comment('Monto calculado de mora por retraso');
            $table->enum('estado', ['PENDIENTE', 'PAGADO', 'VENCIDO', 'PARCIAL'])
                ->default('PENDIENTE');
            $table->string('metodo_pago', 50)->nullable()
                ->comment('Efectivo, transferencia, cheque, etc.');
            $table->string('comprobante')->nullable()
                ->comment('Ruta del archivo de comprobante de pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aportes');
    }
};
