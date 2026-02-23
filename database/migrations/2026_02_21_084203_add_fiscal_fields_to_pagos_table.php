<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Cliente fiscal
            $table->foreignId('cliente_fiscal_id')->nullable()->after('user_id')->constrained('clientes_fiscales')->onDelete('set null');
            
            // Control fiscal
            $table->string('numero_control_fiscal', 50)->nullable()->after('numero');
            $table->boolean('es_factura_fiscal')->default(false)->after('tipo_pago');
            
            // Montos fiscales
            $table->decimal('base_imponible', 10, 2)->default(0)->after('subtotal');
            $table->decimal('monto_exento', 10, 2)->default(0)->after('base_imponible');
            
            // IVA
            $table->decimal('iva_porcentaje', 5, 2)->default(0)->after('monto_exento');
            $table->decimal('iva_monto', 10, 2)->default(0)->after('iva_porcentaje');
            
            // IGTF
            $table->decimal('igtf_porcentaje', 5, 2)->default(0)->after('iva_monto');
            $table->decimal('igtf_monto', 10, 2)->default(0)->after('igtf_porcentaje');
            $table->boolean('aplica_igtf')->default(false)->after('igtf_monto');
            
            // Total con impuestos
            $table->decimal('total_con_impuestos', 10, 2)->default(0)->after('total');
            
            // Fechas fiscales
            $table->date('fecha_emision_fiscal')->nullable()->after('fecha');
            $table->date('fecha_vencimiento_fiscal')->nullable()->after('fecha_emision_fiscal');
            
            // Índices
            $table->index('numero_control_fiscal');
            $table->index('es_factura_fiscal');
            $table->index('cliente_fiscal_id');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['cliente_fiscal_id']);
            $table->dropIndex(['numero_control_fiscal']);
            $table->dropIndex(['es_factura_fiscal']);
            $table->dropIndex(['cliente_fiscal_id']);
            
            $table->dropColumn([
                'cliente_fiscal_id',
                'numero_control_fiscal',
                'es_factura_fiscal',
                'base_imponible',
                'monto_exento',
                'iva_porcentaje',
                'iva_monto',
                'igtf_porcentaje',
                'igtf_monto',
                'aplica_igtf',
                'total_con_impuestos',
                'fecha_emision_fiscal',
                'fecha_vencimiento_fiscal'
            ]);
        });
    }
};
