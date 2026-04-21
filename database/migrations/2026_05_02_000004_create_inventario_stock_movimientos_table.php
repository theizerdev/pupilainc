<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock por producto/almacén
        Schema::create('inventario_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id']);
            $table->index('producto_id');
        });

        // Movimientos de inventario
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->enum('tipo', [
                'entrada',
                'salida',
                'ajuste_positivo',
                'ajuste_negativo',
                'transferencia_entrada',
                'transferencia_salida',
                'devolucion',
                'vencimiento',
            ]);
            $table->integer('cantidad');
            $table->integer('stock_anterior')->default(0);
            $table->integer('stock_nuevo')->default(0);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->string('referencia')->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['producto_id', 'almacen_id']);
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('inventario_stock');
    }
};
