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
        Schema::table('baremos', function (Blueprint $table) {
            // Agregar columna categoria_id después de especialidad_id
            $table->foreignId('categoria_id')->nullable()->after('especialidad_id')->constrained('categorias')->onDelete('set null');
            
            // Agregar índices
            $table->index(['empresa_id', 'categoria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baremos', function (Blueprint $table) {
            // Eliminar la clave foránea y la columna
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
};
