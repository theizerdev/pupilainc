<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes_fiscales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->onDelete('set null');
            
            // Datos fiscales obligatorios
            $table->enum('tipo_documento', ['V', 'J', 'E', 'G', 'P'])->default('V');
            $table->string('numero_documento', 20);
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            
            // Dirección fiscal completa
            $table->text('direccion_fiscal');
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            
            // Contacto
            $table->string('telefono', 20);
            $table->string('email')->nullable();
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->unique(['empresa_id', 'tipo_documento', 'numero_documento'], 'cliente_fiscal_unique');
            $table->index('paciente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_fiscales');
    }
};
