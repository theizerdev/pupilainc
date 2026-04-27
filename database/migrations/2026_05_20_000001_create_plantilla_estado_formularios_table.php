<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_estado_formularios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('especialidad_plantillas')->cascadeOnDelete();
            // Clave del estado: en_gotas, dilatado, en_enfermeria, etc.
            $table->string('estado');
            $table->string('titulo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['plantilla_id', 'estado']);
            $table->index(['plantilla_id', 'activo']);
        });

        // Las secciones de estos formularios reutilizan plantilla_secciones
        // pero necesitan saber a qué estado_formulario pertenecen
        Schema::table('plantilla_secciones', function (Blueprint $table) {
            $table->foreignId('estado_formulario_id')
                ->nullable()
                ->after('plantilla_id')
                ->constrained('plantilla_estado_formularios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_secciones', function (Blueprint $table) {
            $table->dropForeign(['estado_formulario_id']);
            $table->dropColumn('estado_formulario_id');
        });
        Schema::dropIfExists('plantilla_estado_formularios');
    }
};
