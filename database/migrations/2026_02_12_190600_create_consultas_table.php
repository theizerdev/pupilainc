<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();

            // Relaciones principales
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('medicos');
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades');

            // Control de proceso
            $table->dateTime('fecha_consulta');
            $table->boolean('preconsulta')->default(false);
            $table->string('estado')->default('sala_espera'); // sala_espera, en_consultorio, en_consultorio_optometrista, en_gotas, en_optica, en_estudio, finalizada

            // Datos clínicos (JSON/Texto)
            $table->text('motivo_consulta')->nullable();
            $table->text('enfermedad_actual')->nullable();
            $table->json('antecedentes')->nullable();
            $table->json('agudeza_visual')->nullable();
            $table->json('refraccion')->nullable();
            $table->json('biomicroscopia')->nullable();
            $table->json('pio')->nullable();
            $table->json('dilatacion_pupilar')->nullable();
            $table->json('fondo_ojo')->nullable();
            $table->json('diagnosticos')->nullable();
            $table->text('plan_tratamiento')->nullable();
            $table->json('medicamentos')->nullable();
            $table->text('observaciones')->nullable();

            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index(['medico_id', 'estado']);
            $table->index('cita_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
