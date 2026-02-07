<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('codigo', 10)->unique();
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icono', 50)->default('fa-stethoscope');
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->decimal('costo_consulta', 10, 2)->default(0);
            $table->integer('duracion_consulta')->default(30);
            $table->boolean('requiere_cita_previa')->default(true);
            
            $table->timestamps();
            
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};