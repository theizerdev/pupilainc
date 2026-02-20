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
        Schema::create('consulta_estudios', function (Blueprint $table) {
            $table->id();
            
            // Relación con consulta
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            
            // Paso 3: Laboratorios y procedimientos
            $table->enum('tipo_estudio', ['imagen', 'laboratorio', 'otros']);
            $table->string('nombre_estudio');
            $table->text('indicaciones')->nullable();
            $table->integer('orden')->default(0);
            
            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Índices
            $table->index(['consulta_id', 'tipo_estudio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_estudios');
    }
};
