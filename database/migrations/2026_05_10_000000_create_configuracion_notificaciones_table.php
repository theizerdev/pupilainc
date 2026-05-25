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
        Schema::create('configuracion_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('tipo_destinatario'); // 'paciente' o 'doctor'
            $table->string('estado_cita'); // Estado de la cita (confirmada, sala_espera, etc.)
            $table->boolean('enviar_notificacion')->default(false); // Si se debe enviar notificación
            $table->timestamps();

            // Índice único con nombre personalizado para evitar límite de 64 caracteres
            $table->unique(
                ['empresa_id', 'tipo_destinatario', 'estado_cita'],
                'cfg_notif_emp_tipo_estado_unique'
            );
            $table->index(['empresa_id', 'tipo_destinatario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_notificaciones');
    }
};
