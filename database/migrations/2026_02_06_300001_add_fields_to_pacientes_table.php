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
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('genero', 20)->nullable()->after('fecha_nacimiento');
            $table->string('estado_civil', 50)->nullable()->after('genero');
            $table->string('ocupacion', 255)->nullable()->after('estado_civil');
            $table->string('nacionalidad', 100)->nullable()->after('ocupacion');
            $table->string('foto')->nullable()->after('nacionalidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['genero', 'estado_civil', 'ocupacion', 'nacionalidad', 'foto']);
        });
    }
};