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
        Schema::create('especies', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Perro, Gato, Ave, etc.
            $table->string('nombre_cientifico')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('icono')->nullable()->comment('Clase de icono o emoji');
            $table->string('color')->default('#3B82F6')->comment('Color para UI');
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'activo']);
            $table->unique(['nombre', 'empresa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especies');
    }
};
