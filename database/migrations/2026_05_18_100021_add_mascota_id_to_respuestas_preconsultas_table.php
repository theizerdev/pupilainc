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
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            // Relación con mascota (nullable para mantener compatibilidad con pacientes humanos)
            $table->unsignedBigInteger('mascota_id')->nullable()->after('paciente_id');
            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('set null');

            // Índice para búsquedas eficientes
            $table->index(['mascota_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropForeign(['mascota_id']);
            $table->dropIndex(['mascota_id']);
            $table->dropColumn('mascota_id');
        });
    }
};
