<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->string('sku')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_producto_id')->nullable()->constrained('categorias_producto')->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('unidad_medida')->default('unidad');
            $table->decimal('precio_costo', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->nullable();
            $table->integer('punto_reorden')->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('ubicacion_fisica')->nullable();
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('es_medicamento')->default(false);
            $table->boolean('status')->default(true);
            $table->string('imagen')->nullable();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
            $table->index('fecha_vencimiento');
            $table->index('categoria_producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
