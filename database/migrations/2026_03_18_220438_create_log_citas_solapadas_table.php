<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_citas_solapadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('cita_original_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('cita_nueva_id')->constrained('citas')->onDelete('cascade');
            $table->string('tipo_prioridad', 20);
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['cita_original_id', 'cita_nueva_id']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_citas_solapadas');
    }
};