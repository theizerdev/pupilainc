<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('plantilla_secciones')->cascadeOnDelete();

            $table->string('nombre_campo');  // Clave interna, ej: "ojo_derecho_av", "presion_sistolica"
            $table->string('etiqueta');      // Label visible, ej: "Ojo Derecho (AV)"
            $table->text('descripcion')->nullable();

            // Tipos: text, number, textarea, select, radio, checkbox, range, date
            $table->string('tipo')->default('text');

            // Para select/radio/checkbox: array de opciones ["20/20","20/40","20/200","CF","MM","PL","NPL"]
            $table->json('opciones')->nullable();

            // Validaciones básicas
            $table->boolean('obligatorio')->default(false);
            $table->string('valor_defecto')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('unidad')->nullable(); // Ej: "mmHg", "kg", "°C"

            // Para tipo range
            $table->decimal('min', 8, 2)->nullable();
            $table->decimal('max', 8, 2)->nullable();

            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);

            // Ancho en grid (1-12 columnas Bootstrap/Materialize)
            $table->tinyInteger('ancho_columnas')->default(6);

            $table->timestamps();

            $table->index(['seccion_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_campos');
    }
};
