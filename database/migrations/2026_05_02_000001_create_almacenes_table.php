<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('ubicacion')->nullable();
            $table->boolean('es_principal')->default(false);
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
