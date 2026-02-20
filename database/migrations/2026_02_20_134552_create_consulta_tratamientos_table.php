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
        Schema::create('consulta_tratamientos', function (Blueprint $table) {
            $table->id();
            
            // Relación con consulta
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            
            // Paso 4: Tratamiento
            $table->string('medicamento');
            $table->text('indicaciones');
            $table->integer('orden')->default(0);
            
            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Índices
            $table->index('consulta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_tratamientos');
    }
};
