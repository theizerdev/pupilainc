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
        Schema::create('razas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Labrador, Persa, etc.
            $table->foreignId('especie_id')->constrained('especies')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->integer('tamano_promedio_cm')->nullable()->comment('Tamaño promedio en cm');
            $table->integer('peso_promedio_kg')->nullable()->comment('Peso promedio en kg');
            $table->integer('esperanza_vida_anios')->nullable()->comment('Esperanza de vida en años');
            $table->string('color')->default('#3B82F6')->comment('Color para UI');
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['especie_id', 'activo']);
            $table->index(['empresa_id', 'activo']);
            $table->unique(['nombre', 'especie_id', 'empresa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razas');
    }
};
