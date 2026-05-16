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
        Schema::table('consultas', function (Blueprint $table) {
            // Relación con mascota
            $table->unsignedBigInteger('mascota_id')->nullable()->after('paciente_id');
            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('set null');

            // Campos veterinarios específicos
            $table->decimal('temperatura_rectal', 4, 2)->nullable()->comment('Temperatura en °C');
            $table->integer('frecuencia_cardiaca')->nullable()->comment('FC en lpm');
            $table->integer('frecuencia_respiratoria')->nullable()->comment('FR en rpm');
            $table->decimal('peso_actual_kg', 5, 2)->nullable()->comment('Peso en kg al momento de la consulta');
            $table->decimal('peso_historico_kg', 5, 2)->nullable()->comment('Peso histórico registrado');

            // Body Condition Score (1-9 escala veterinaria)
            $table->integer('bcs_score')->nullable()->comment('Body Condition Score 1-9');

            // Examen físico veterinario
            $table->text('examen_fisico_general')->nullable();
            $table->text('sistema_cardiovascular')->nullable();
            $table->text('sistema_respiratorio')->nullable();
            $table->text('sistema_digestivo')->nullable();
            $table->text('sistema_urinario')->nullable();
            $table->text('sistema_nervioso')->nullable();
            $table->text('piel_y_pelaje')->nullable();
            $table->text('ojos_oidos_boca')->nullable();
            $table->text('sistema_locomotor')->nullable();

            // Índice
            $table->index(['mascota_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['mascota_id']);
            $table->dropColumn([
                'mascota_id',
                'temperatura_rectal',
                'frecuencia_cardiaca',
                'frecuencia_respiratoria',
                'peso_actual_kg',
                'peso_historico_kg',
                'bcs_score',
                'examen_fisico_general',
                'sistema_cardiovascular',
                'sistema_respiratorio',
                'sistema_digestivo',
                'sistema_urinario',
                'sistema_nervioso',
                'piel_y_pelaje',
                'ojos_oidos_boca',
                'sistema_locomotor'
            ]);
        });
    }
};
