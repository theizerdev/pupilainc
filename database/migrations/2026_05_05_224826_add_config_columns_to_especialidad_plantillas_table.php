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
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            // Agregar columnas para configuración avanzada de pasos y estados
            $table->json('pasos_config')->nullable()->after('estados_flujo');
            $table->json('estados_config')->nullable()->after('pasos_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            $table->dropColumn(['pasos_config', 'estados_config']);
        });
    }
};
