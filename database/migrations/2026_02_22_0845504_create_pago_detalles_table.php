<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->foreignId('concepto_pago_id')->nullable()->constrained('conceptos_pago')->onDelete('set null');
            $table->foreignId('baremo_id')->nullable()->constrained('baremos')->onDelete('set null');
            $table->unsignedBigInteger('payment_schedule_id')->nullable();

            $table->string('descripcion');
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            // Campos fiscales
            $table->boolean('aplica_iva')->default(true);
            $table->boolean('exento_iva')->default(false);
            $table->decimal('iva_alicuota', 5, 2)->default(16.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_detalles');
    }
};
