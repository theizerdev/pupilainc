<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baremos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            
            // Servicio
            $table->string('codigo', 20)->unique();
            $table->string('nombre_servicio');
            $table->text('descripcion')->nullable();
            
            // Costos
            $table->decimal('costo_usd', 10, 2);
            $table->decimal('costo_bs', 10, 2)->nullable(); // Se calcula automáticamente
            
            // Impuestos
            $table->boolean('aplica_iva')->default(true);
            $table->boolean('exento_iva')->default(false);
            
            // Duración estimada (minutos)
            $table->integer('duracion_minutos')->nullable();
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['empresa_id', 'especialidad_id']);
            $table->index('codigo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baremos');
    }
};
