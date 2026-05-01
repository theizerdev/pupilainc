<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ventas_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->boolean('aplica_iva')->default(true);
            $table->boolean('exento_iva')->default(false);
            $table->decimal('iva_alicuota', 5, 2)->default(16.00);
            $table->decimal('iva_monto', 10, 2)->default(0);
            $table->decimal('costo_unitario', 10, 2)->nullable();
            $table->decimal('costo_total', 10, 2)->nullable();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->onDelete('set null');
            $table->timestamps();

            $table->index(['pago_id', 'producto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_productos');
    }
};