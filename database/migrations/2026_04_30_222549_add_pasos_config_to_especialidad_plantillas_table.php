<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            // Configuración completa de pasos:
            // [{ key, nombre, icono, orden, activo, tipo: 'predefinido'|'formulario' }, ...]
            $table->json('pasos_config')->nullable()->after('pasos_habilitados');

            // Configuración completa de estados:
            // [{ key, nombre, color, orden, activo }, ...]
            $table->json('estados_config')->nullable()->after('estados_flujo');
        });
    }

    public function down(): void
    {
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            $table->dropColumn(['pasos_config', 'estados_config']);
        });
    }
};
