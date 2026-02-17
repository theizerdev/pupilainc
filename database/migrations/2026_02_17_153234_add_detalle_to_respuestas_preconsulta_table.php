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
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->text('detalle')->nullable()->after('respuesta_multiple');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respuesta_preconsultas', function (Blueprint $table) {
            $table->dropColumn('detalle');
        });
    }
};
