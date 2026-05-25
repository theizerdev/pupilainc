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
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            // Controla si en estado 'en_consultorio' se muestra wizard por pasos o formulario directo
            // true = Wizard paso a paso (signos vitales → cuestionario → evaluación → estudios → tratamiento → reposo)
            // false = Formulario completo del estado 'en_consultorio'
            $table->boolean('usar_wizard_en_consultorio')->default(true)->after('estados_flujo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('especialidad_plantillas', function (Blueprint $table) {
            $table->dropColumn('usar_wizard_en_consultorio');
        });
    }
};
