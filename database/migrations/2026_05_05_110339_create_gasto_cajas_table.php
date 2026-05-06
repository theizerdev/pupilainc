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
        Schema::create('gasto_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('concepto'); // Descripción del gasto
            $table->text('observaciones')->nullable();
            $table->decimal('monto', 12, 2); // Monto en USD
            $table->decimal('monto_bs', 12, 2)->default(0); // Monto en Bs
            $table->decimal('tasa_cambio', 10, 4)->default(1);

            $table->string('metodo_pago')->default('efectivo'); // efectivo, transferencia, etc.
            $table->string('numero_referencia')->nullable();
            $table->string('categoria')->nullable(); // combustible, materiales, servicios, etc.

            $table->enum('estado', ['pendiente', 'aprobado', 'cancelado'])->default('aprobado');
            $table->timestamp('fecha_gasto');

            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['caja_id', 'estado']);
            $table->index(['empresa_id', 'sucursal_id', 'fecha_gasto']);
        });

        // Agregar columnas a la tabla cajas para totales de egresos
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('total_egresos', 12, 2)->default(0)->after('total_ingresos');
            $table->decimal('monto_final_ajustado', 12, 2)->default(0)->after('monto_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn(['total_egresos', 'monto_final_ajustado']);
        });

        Schema::dropIfExists('gasto_cajas');
    }
};
