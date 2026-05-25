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
        Schema::table('consulta_gotas', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->after('tiempo_espera')
                  ->comment('Estado de la gota: pendiente, aplicada, completada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consulta_gotas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
