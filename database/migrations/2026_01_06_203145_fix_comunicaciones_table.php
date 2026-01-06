<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hacer fecha_envio nullable
        DB::statement("ALTER TABLE comunicaciones ALTER COLUMN fecha_envio DROP DEFAULT");
        DB::statement("ALTER TABLE comunicaciones ALTER COLUMN fecha_envio DROP NOT NULL");

        // Agregar columna archivo_adjunto si no existe
        Schema::table('comunicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('comunicaciones', 'archivo_adjunto')) {
                $table->string('archivo_adjunto')->nullable()->after('contenido');
            }
        });

        // Actualizar enum de tipo para incluir QUEJA
        DB::statement("ALTER TABLE comunicaciones DROP CONSTRAINT IF EXISTS comunicaciones_tipo_check");
        DB::statement("ALTER TABLE comunicaciones ADD CONSTRAINT comunicaciones_tipo_check CHECK (tipo IN ('CONVOCATORIA', 'COMUNICADO', 'RECLAMO', 'QUEJA', 'RECOMENDACION', 'NOTIFICACION'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        DB::statement("ALTER TABLE comunicaciones ALTER COLUMN fecha_envio SET DEFAULT CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE comunicaciones ALTER COLUMN fecha_envio SET NOT NULL");

        Schema::table('comunicaciones', function (Blueprint $table) {
            if (Schema::hasColumn('comunicaciones', 'archivo_adjunto')) {
                $table->dropColumn('archivo_adjunto');
            }
        });

        // Revertir enum de tipo
        DB::statement("ALTER TABLE comunicaciones DROP CONSTRAINT IF EXISTS comunicaciones_tipo_check");
        DB::statement("ALTER TABLE comunicaciones ADD CONSTRAINT comunicaciones_tipo_check CHECK (tipo IN ('CONVOCATORIA', 'COMUNICADO', 'RECLAMO', 'RECOMENDACION', 'NOTIFICACION'))");
    }
};
