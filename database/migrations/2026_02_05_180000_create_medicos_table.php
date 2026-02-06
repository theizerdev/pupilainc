<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento_identidad')->unique();
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->string('licencia_medica')->unique();
            $table->integer('anios_experiencia')->default(0);
            $table->enum('nivel_experiencia', ['Básico', 'Intermedio', 'Avanzado'])->default('Básico');
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
            $table->index('documento_identidad');
            $table->index('licencia_medica');
        });

        // Tabla pivote para médicos y especialidades
        Schema::create('medico_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->decimal('tarifa_consulta', 10, 2)->nullable();
            $table->json('horario_atencion')->nullable(); // JSON para almacenar horarios
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            $table->unique(['medico_id', 'especialidad_id']);
            $table->index(['medico_id', 'status']);
            $table->index(['especialidad_id', 'status']);
        });

        // Tabla pivote para médicos y subespecialidades
        Schema::create('medico_subespecialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->foreignId('subespecialidad_id')->constrained('subespecialidades')->onDelete('cascade');
            $table->decimal('tarifa_consulta', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            $table->unique(['medico_id', 'subespecialidad_id']);
            $table->index(['medico_id', 'status']);
            $table->index(['subespecialidad_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_subespecialidad');
        Schema::dropIfExists('medico_especialidad');
        Schema::dropIfExists('medicos');
    }
};