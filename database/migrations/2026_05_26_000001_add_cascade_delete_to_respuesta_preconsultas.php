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
        // Para MySQL, necesitamos eliminar y recrear la clave foránea
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            // Eliminar la clave foránea existente
            $table->dropForeign(['cita_id']);
            
            // Recrear la clave foránea con cascade delete
            $table->foreign('cita_id')
                  ->references('id')
                  ->on('citas')
                  ->onDelete('cascade');
        });

        // También asegurarnos que paciente_id tenga cascade
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a las restricciones originales sin cascade
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropForeign(['cita_id']);
            $table->foreign('cita_id')
                  ->references('id')
                  ->on('citas')
                  ->onDelete('restrict');
        });

        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('restrict');
        });
    }
};
