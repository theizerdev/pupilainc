<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cita_recordatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->enum('tipo', ['24h', '2h', 'personalizado']);
            $table->datetime('fecha_envio_programado');
            $table->datetime('fecha_envio_real')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->string('canal', 50)->default('whatsapp'); // whatsapp, email, sms
            $table->text('mensaje')->nullable();
            $table->text('error_mensaje')->nullable();
            $table->boolean('confirmacion_respuesta')->nullable();
            $table->datetime('fecha_respuesta')->nullable();
            $table->timestamps();
            
            $table->index(['fecha_envio_programado', 'estado']);
            $table->index('cita_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cita_recordatorios');
    }
};