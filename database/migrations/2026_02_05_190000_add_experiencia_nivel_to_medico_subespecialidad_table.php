<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_subespecialidad', function (Blueprint $table) {
            $table->integer('experiencia_anios')->default(0)->after('tarifa_consulta');
            $table->enum('nivel_experiencia', ['Básico', 'Intermedio', 'Avanzado'])->default('Básico')->after('experiencia_anios');
            $table->json('horario_atencion')->nullable()->after('nivel_experiencia');
        });
    }

    public function down(): void
    {
        Schema::table('medico_subespecialidad', function (Blueprint $table) {
            $table->dropColumn(['experiencia_anios', 'nivel_experiencia', 'horario_atencion']);
        });
    }
};