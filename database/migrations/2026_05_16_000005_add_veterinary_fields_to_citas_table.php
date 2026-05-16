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
            // Agregar relación con mascota (nullable para compatibilidad hacia atrás)
            $table->unsignedBigInteger('mascota_id')->nullable()->after('paciente_id');
            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('set null');

            // Campos específicos veterinarios
            $table->enum('tipo_atencion', [
                'consulta_general',
                'emergencia',
                'cirugia',
                'vacunacion',
                'desparasitacion',
                'control_salud',
                'seguimiento',
                'primera_visita'
            ])->default('consulta_general')->after('motivo');

            $table->enum('urgencia', ['normal', 'urgente', 'emergencia'])->default('normal')->after('tipo_atencion');

            $table->text('notas_comportamiento')->nullable()->after('urgencia')
                ->comment('Notas sobre comportamiento del animal, miedos, agresividad, etc.');

            // Índice para búsquedas rápidas
            $table->index(['mascota_id']);
            $table->index(['tipo_atencion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['mascota_id']);
            $table->dropColumn(['mascota_id', 'tipo_atencion', 'urgencia', 'notas_comportamiento']);
        });
    }
};
