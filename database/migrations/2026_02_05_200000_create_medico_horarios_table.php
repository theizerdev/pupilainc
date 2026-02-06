<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->tinyInteger('dia_semana')->comment('1=Lunes, 2=Martes, ..., 7=Domingo');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_cita')->default(30)->comment('Duración de cada cita en minutos');
            $table->boolean('activo')->default(true);
            $table->date('fecha_inicio')->nullable()->comment('Fecha desde cuando aplica este horario');
            $table->date('fecha_fin')->nullable()->comment('Fecha hasta cuando aplica este horario');
            $table->timestamps();
            
            $table->index(['medico_id', 'dia_semana', 'activo']);
            $table->index(['medico_id', 'activo']);
            $table->unique(['medico_id', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_horarios');
    }
};