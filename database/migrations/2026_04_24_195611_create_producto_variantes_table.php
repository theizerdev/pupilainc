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
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('sku_variante')->nullable();
            $table->string('atributo');
            $table->string('valor');
            $table->string('codigo_barras')->nullable();
            $table->decimal('precio_costo', 12, 2)->nullable();
            $table->decimal('precio_venta', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('imagen')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['producto_id', 'status']);
            $table->index(['empresa_id', 'sucursal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};
