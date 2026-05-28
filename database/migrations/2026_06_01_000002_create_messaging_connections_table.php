<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messaging_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('messaging_providers')->onDelete('cascade');
            $table->string('name');
            $table->text('credentials'); // JSON encriptado
            $table->json('configuration')->nullable();
            $table->enum('status', ['active', 'inactive', 'testing', 'error'])->default('inactive');
            $table->json('is_default_for')->nullable(); // JSON: módulos por defecto
            $table->timestamp('last_test_at')->nullable();
            $table->json('test_result')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'provider_id', 'name']);
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_connections');
    }
};