<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_notification_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('connection_id')->constrained('messaging_connections')->onDelete('cascade');
            $table->string('module_key'); // citas, usuarios, consultas, doctores
            $table->string('action_key'); // creacion, recordatorio, confirmacion
            $table->string('recipient_type'); // paciente, doctor, usuario
            $table->boolean('enabled')->default(true);
            $table->integer('priority')->default(0); // orden de fallback
            $table->timestamps();

            $table->unique(['empresa_id', 'module_key', 'action_key', 'recipient_type'], 'module_channel_unique');
            $table->index(['empresa_id', 'module_key', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_notification_channels');
    }
};