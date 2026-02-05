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
        Schema::create('subespecialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->text('descripcion')->nullable();
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->decimal('costo_consulta', 10, 2)->nullable();
            $table->integer('duracion_consulta')->default(30);
            $table->boolean('requiere_cita_previa')->default(true);
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->timestamps();
            
            $table->index(['especialidad_id', 'status']);
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subespecialidades');
    }
};