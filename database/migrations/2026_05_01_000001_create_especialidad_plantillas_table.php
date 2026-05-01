<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidad_plantillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            // Pasos habilitados en el ProcesoConsulta (JSON array de strings)
            // Ej: ["signos_vitales","cuestionario","evaluacion","estudios","tratamientos","reposo"]
            $table->json('pasos_habilitados')->nullable();

            // Estados del flujo kanban para esta especialidad (JSON array ordenado)
            // Ej: ["sala_espera","en_enfermeria","en_consultorio","en_gotas","dilatado","en_optica","finalizada"]
            $table->json('estados_flujo')->nullable();

            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');

            $table->timestamps();

            $table->index(['especialidad_id', 'activo']);
            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad_plantillas');
    }
};
