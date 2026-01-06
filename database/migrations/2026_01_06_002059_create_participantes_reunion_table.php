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
        Schema::create('participantes_reunion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reunion_id')->constrained('reuniones')->onDelete('cascade');
            $table->foreignId('residente_id')->constrained('residentes')->onDelete('cascade');
            $table->boolean('asistio')->default(false);
            $table->foreignId('representado_por')->nullable()->constrained('residentes')
                ->comment('ID del residente que lo representa si no asistió');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes_reunion');
    }
};
