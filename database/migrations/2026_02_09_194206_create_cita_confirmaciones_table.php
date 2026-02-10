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
        Schema::create('cita_confirmaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->enum('metodo', ['whatsapp', 'email', 'sms'])->default('whatsapp');
            $table->string('destinatario'); // teléfono o email
            $table->text('mensaje_enviado');
            $table->string('token_confirmacion', 64)->unique();
            $table->enum('estado', ['pendiente', 'confirmado', 'rechazado', 'sin_respuesta', 'expirado'])->default('pendiente');
            $table->text('respuesta_recibida')->nullable();
            $table->timestamp('fecha_envio');
            $table->timestamp('fecha_respuesta')->nullable();
            $table->integer('intentos')->default(0);
            $table->integer('max_intentos')->default(3);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['cita_id', 'estado']);
            $table->index(['estado', 'fecha_envio']);
            $table->index(['token_confirmacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_confirmaciones');
    }
};
