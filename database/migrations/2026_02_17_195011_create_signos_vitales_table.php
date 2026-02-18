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
        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->foreignId('enfermero_id')->constrained('enfermeros');
            $table->foreignId('paciente_id')->constrained('pacientes');
            
            // Signos vitales
            $table->decimal('presion_arterial_sistolica', 5, 1)->nullable();
            $table->decimal('presion_arterial_diastolica', 5, 1)->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('talla', 5, 1)->nullable();
            $table->decimal('imc', 4, 1)->nullable();
            $table->decimal('saturacion_oxigeno', 5, 1)->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Índices
            $table->index(['consulta_id', 'enfermero_id']);
            $table->index(['empresa_id', 'sucursal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signos_vitales');
    }
};