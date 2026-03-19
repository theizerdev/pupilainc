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
        Schema::table('exchange_rates', function (Blueprint $table) {
            // Agregar columna pais_id después de id
            $table->foreignId('pais_id')->after('id')->nullable()->constrained('pais')->onDelete('cascade');
            
            // Agregar índice para búsquedas por país y fecha
            $table->index(['pais_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            // Eliminar la clave foránea y la columna
            $table->dropForeign(['pais_id']);
            $table->dropColumn('pais_id');
        });
    }
};
