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
            // Hacer paciente_id nullable para permitir citas veterinarias con mascota_id
            $table->unsignedBigInteger('paciente_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Revertir: hacer paciente_id required nuevamente
            $table->unsignedBigInteger('paciente_id')->nullable(false)->change();
        });
    }
};
