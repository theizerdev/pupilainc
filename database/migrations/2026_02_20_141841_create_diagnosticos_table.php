<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique(); // Código CIE-10
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['codigo', 'activo']);
            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
