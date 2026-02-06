<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento_identidad');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            $table->string('nickname')->nullable();
            $table->date('fecha_nacimiento');
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
            $table->index('documento_identidad');
            $table->unique(['empresa_id', 'documento_identidad']);
        });

        Schema::create('tutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento_identidad')->nullable();
            $table->string('parentesco');
            $table->integer('edad')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps();

            $table->index('paciente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutores');
        Schema::dropIfExists('pacientes');
    }
};
