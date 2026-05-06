<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->text('nota');
            $table->enum('tipo', ['manual', 'sistema'])->default('manual');
            $table->string('estado_consulta')->nullable(); // estado al momento de la nota
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales');
            $table->timestamps();

            $table->index(['consulta_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_notas');
    }
};
