<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->foreignId('consulta_id')->nullable()->after('cita_id')->constrained('consultas')->nullOnDelete();
            $table->index('consulta_id');
        });
    }

    public function down(): void
    {
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropForeign(['consulta_id']);
            $table->dropColumn('consulta_id');
        });
    }
};
