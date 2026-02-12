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
            $table->string('estado_preconsulta')->default('pendiente')->after('estado'); // pendiente, enviado, completado, revisado
            $table->string('token_preconsulta')->nullable()->unique()->after('estado_preconsulta');
            $table->timestamp('fecha_envio_preconsulta')->nullable()->after('token_preconsulta');
            $table->timestamp('fecha_completado_preconsulta')->nullable()->after('fecha_envio_preconsulta');

            $table->index('estado_preconsulta');
            $table->index('token_preconsulta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['estado_preconsulta', 'token_preconsulta', 'fecha_envio_preconsulta', 'fecha_completado_preconsulta']);
        });
    }
};
