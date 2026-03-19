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
        Schema::create('exchange_rate_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pais_id')->constrained('pais')->onDelete('cascade');
            $table->string('moneda_base', 3)->default('USD'); // USD, EUR, etc.
            $table->string('moneda_local', 3)->nullable(); // VES, COP, ARS, etc.
            $table->boolean('requiere_tasa_cambio')->default(false); // Si el país usa tasa de cambio
            $table->boolean('usar_api_bcv')->default(false); // Si usa API del BCV para Venezuela
            $table->string('api_url')->nullable(); // URL de la API para obtener tasas
            $table->string('api_key')->nullable(); // API Key si es requerida
            $table->decimal('tasa_fija', 10, 4)->nullable(); // Tasa fija si no se usa API
            $table->integer('frecuencia_actualizacion_minutos')->default(60); // Cada cuánto actualizar
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('pais_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_configs');
    }
};
