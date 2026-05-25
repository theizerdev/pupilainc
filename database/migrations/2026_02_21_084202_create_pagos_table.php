<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->nullable()->constrained('consultas')->onDelete('set null');
            $table->foreignId('caja_id')->nullable()->constrained('cajas')->onDelete('set null');
            $table->foreignId('serie_id')->nullable()->constrained('series')->onDelete('set null');
            $table->string('serie', 20);
            $table->integer('numero');
            $table->string('tipo_pago', 50); // factura, boleta, recibo, nota_credito, nota_debito
            $table->date('fecha');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Montos base (en USD)
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Tasa de cambio del día
            $table->decimal('tasa_cambio_usd', 10, 4)->nullable();
            $table->decimal('tasa_cambio_eur', 10, 4)->nullable();
            
            // Totales en diferentes monedas
            $table->decimal('total_usd', 10, 2)->default(0);
            $table->decimal('total_bs', 10, 2)->default(0);
            
            // Método de pago principal
            $table->enum('metodo_pago', [
                'efectivo',
                'bbva_dr', 
                'mifel_dra',
                'cuenta_dr',
                'cuenta_dra',
                'tarjeta_credito',
                'tarjeta_debito'
            ]);
            $table->string('referencia')->nullable();
            
            // Pago mixto (múltiples métodos)
            $table->boolean('es_pago_mixto')->default(false);
            $table->json('detalles_pago_mixto')->nullable();
            // Estructura: [{metodo: 'efectivo_bs', monto_bs: 100, monto_usd: 2.5}, ...]
            
            // Estado
            $table->enum('estado', ['pendiente', 'aprobado', 'cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            
            // Multitenancy
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('fecha');
            $table->index('estado');
            $table->index('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
