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
        // Hacer paciente_id nullable en consultas (para soportar citas veterinarias)
        Schema::table('consultas', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable()->change();
        });

        // Hacer paciente_id nullable en respuesta_preconsultas (para soportar preconsultas de mascotas)
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios - hacer paciente_id NOT NULL nuevamente
        Schema::table('consultas', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable(false)->change();
        });

        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable(false)->change();
        });
    }
};
