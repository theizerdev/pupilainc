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
        Schema::create('respuesta_preconsultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('cita_id')->nullable()->constrained('citas');
            $table->foreignId('pregunta_id')->constrained('preguntas');
            $table->text('respuesta')->nullable();
            $table->json('respuesta_multiple')->nullable(); // Para respuestas múltiples
            $table->string('token_unico'); // Token para acceso público
            $table->boolean('completado')->default(false);
            $table->timestamp('fecha_completado')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            $table->timestamps();

            $table->index(['token_unico', 'completado']);
            $table->index(['paciente_id', 'cita_id']);
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuesta_preconsultas');
    }
};
