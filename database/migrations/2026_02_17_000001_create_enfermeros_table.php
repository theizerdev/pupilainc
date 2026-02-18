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
        Schema::create('enfermeros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento_identidad')->unique();
            $table->string('genero')->nullable();
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->string('licencia_enfermeria')->unique();
            $table->integer('anios_experiencia')->default(0);
            $table->string('nivel_experiencia'); // Básico, Intermedio, Avanzado
            $table->string('tipo_enfermero'); // General, Especialista, Supervisor, Jefe de Servicio
            $table->string('especialidad_enfermeria')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();

            // Índices
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
            $table->index('tipo_enfermero');
            $table->index('nivel_experiencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfermeros');
    }
};