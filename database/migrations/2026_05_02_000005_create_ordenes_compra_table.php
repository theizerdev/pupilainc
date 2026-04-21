<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->enum('estado', ['borrador', 'enviada', 'recibida_parcial', 'recibida', 'cancelada'])->default('borrador');
            $table->date('fecha_emision');
            $table->date('fecha_esperada')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->boolean('generada_automaticamente')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('estado');
        });

        Schema::create('ordenes_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_recibida')->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra_detalle');
        Schema::dropIfExists('ordenes_compra');
    }
};
