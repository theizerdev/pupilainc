<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Quitar unique compuesto empresa_id + documento_identidad
            $table->dropUnique(['empresa_id', 'documento_identidad']);

            // Hacer nullable
            $table->string('documento_identidad')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('documento_identidad')->nullable(false)->change();
            $table->unique(['empresa_id', 'documento_identidad']);
        });
    }
};
