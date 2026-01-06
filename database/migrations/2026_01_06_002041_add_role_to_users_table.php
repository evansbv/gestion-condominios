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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['RESIDENTE', 'PROPIETARIO', 'INQUILINO', 'MIEMBRO_DIRECTORIO', 'ADMINISTRADOR'])
                ->default('RESIDENTE')
                ->after('email');
            $table->boolean('activo')->default(true)->after('password');
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo', 'ultimo_acceso']);
        });
    }
};
