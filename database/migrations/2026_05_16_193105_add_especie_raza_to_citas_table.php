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
        Schema::table('citas', function (Blueprint $table) {
            // Agregar especie_id y raza_id para filtrado rápido
            $table->unsignedBigInteger('especie_id')->nullable()->after('mascota_id');
            $table->foreign('especie_id')->references('id')->on('especies')->onDelete('set null');

            $table->unsignedBigInteger('raza_id')->nullable()->after('especie_id');
            $table->foreign('raza_id')->references('id')->on('razas')->onDelete('set null');

            // Índices para búsquedas rápidas
            $table->index(['especie_id']);
            $table->index(['raza_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['especie_id']);
            $table->dropForeign(['raza_id']);
            $table->dropColumn(['especie_id', 'raza_id']);
        });
    }
};
