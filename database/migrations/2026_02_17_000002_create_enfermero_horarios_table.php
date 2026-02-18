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
        Schema::create('enfermero_horarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enfermero_id');
            $table->integer('dia_semana'); // 1=Lunes, 2=Martes, ..., 7=Domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_cita')->default(30); // Duración en minutos
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();

            // Índices
            $table->foreign('enfermero_id')->references('id')->on('enfermeros')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            $table->unique(['enfermero_id', 'dia_semana']);
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfermero_horarios');
    }
};