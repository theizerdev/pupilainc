<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Almacena los valores de los campos dinámicos de la plantilla
        Schema::table('consulta_evaluaciones', function (Blueprint $table) {
            $table->json('datos_dinamicos')->nullable()->after('observaciones_adicionales');
        });

        // Vincula cuestionarios a una especialidad específica (null = aplica a todas)
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->foreignId('especialidad_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('especialidades')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consulta_evaluaciones', function (Blueprint $table) {
            $table->dropColumn('datos_dinamicos');
        });

        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->dropForeign(['especialidad_id']);
            $table->dropColumn('especialidad_id');
        });
    }
};
