<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('especialidad_plantillas')->cascadeOnDelete();
            $table->string('nombre');           // Ej: "Agudeza Visual", "Examen Cardiovascular"
            $table->string('icono')->nullable(); // Ej: "fa-eye", "fa-heart"
            $table->string('color')->nullable(); // Color de cabecera de sección
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['plantilla_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_secciones');
    }
};
