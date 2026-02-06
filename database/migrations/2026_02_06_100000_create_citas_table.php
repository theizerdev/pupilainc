<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('motivo');
            $table->enum('estado', ['pendiente', 'confirmada', 'en_curso', 'completada', 'cancelada', 'no_asistio'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->string('color', 20)->nullable();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['medico_id', 'fecha_inicio', 'fecha_fin']);
            $table->index(['paciente_id', 'fecha_inicio']);
            $table->index('estado');
            $table->index(['empresa_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
