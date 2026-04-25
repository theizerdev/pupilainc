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
        Schema::create('valor_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_variante_id')->constrained('tipo_variantes')->onDelete('cascade');
            $table->string('valor');
            $table->string('codigo')->nullable();
            $table->string('color_hex')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('orden')->default(0);
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['tipo_variante_id', 'status']);
            $table->index(['empresa_id', 'sucursal_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valor_variantes');
    }
};
