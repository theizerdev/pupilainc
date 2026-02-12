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
        // Tabla de Consultorios
        Schema::create('consultorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable(); // Ej: Piso 1, Pasillo A
            $table->string('descripcion')->nullable();
            $table->boolean('status')->default(true);
            
            // Multitenancy
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla de Asignaciones de Consultorios (Diaria/Por turno)
        Schema::create('consultorio_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultorio_id');
            $table->unsignedBigInteger('medico_id');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable(); // Opcional, si se asigna por turnos
            $table->time('hora_fin')->nullable();    // Opcional
            
            // Multitenancy
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            
            $table->foreign('consultorio_id')->references('id')->on('consultorios')->onDelete('cascade');
            $table->foreign('medico_id')->references('id')->on('medicos')->onDelete('cascade');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');

            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['fecha', 'empresa_id', 'sucursal_id']);
            $table->index(['medico_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultorio_asignaciones');
        Schema::dropIfExists('consultorios');
    }
};
