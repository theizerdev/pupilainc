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
            $table->boolean('notificado')->default(false)->after('tiempo_espera')
                  ->comment('Indica si se envió notificación cuando el timer llegó a cero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consulta_gotas', function (Blueprint $table) {
            $table->dropColumn('notificado');
        });
    }
};
