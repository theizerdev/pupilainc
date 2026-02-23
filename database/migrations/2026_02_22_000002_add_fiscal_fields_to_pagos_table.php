<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'es_factura_fiscal')) {
                $table->boolean('es_factura_fiscal')->default(false)->after('es_pago_mixto');
            }
            if (!Schema::hasColumn('pagos', 'cliente_fiscal_id')) {
                $table->foreignId('cliente_fiscal_id')->nullable()->after('es_factura_fiscal')->constrained('clientes_fiscales')->onDelete('set null');
            }
            if (!Schema::hasColumn('pagos', 'condicion_pago')) {
                $table->enum('condicion_pago', ['contado', 'credito'])->default('contado')->after('cliente_fiscal_id');
            }
            if (!Schema::hasColumn('pagos', 'seniat_tipo_documento')) {
                $table->string('seniat_tipo_documento', 2)->nullable()->after('condicion_pago');
            }
            if (!Schema::hasColumn('pagos', 'fecha_emision_fiscal')) {
                $table->date('fecha_emision_fiscal')->nullable()->after('seniat_tipo_documento');
            }
            if (!Schema::hasColumn('pagos', 'numero_control_fiscal')) {
                $table->string('numero_control_fiscal', 20)->nullable()->after('fecha_emision_fiscal');
            }
            if (!Schema::hasColumn('pagos', 'pago_origen_id')) {
                $table->foreignId('pago_origen_id')->nullable()->after('numero_control_fiscal')->constrained('pagos')->onDelete('set null');
            }
            if (!Schema::hasColumn('pagos', 'tipo_nota_credito_id')) {
                $table->foreignId('tipo_nota_credito_id')->nullable()->after('pago_origen_id')->constrained('tipos_nota_credito')->onDelete('set null');
            }
            if (!Schema::hasColumn('pagos', 'tipo_nota_debito_id')) {
                $table->foreignId('tipo_nota_debito_id')->nullable()->after('tipo_nota_credito_id')->constrained('tipos_nota_debito')->onDelete('set null');
            }
            if (!Schema::hasColumn('pagos', 'motivo_nota')) {
                $table->text('motivo_nota')->nullable()->after('tipo_nota_debito_id');
            }
            if (!Schema::hasColumn('pagos', 'base_imponible')) {
                $table->decimal('base_imponible', 10, 2)->default(0)->after('descuento');
            }
            if (!Schema::hasColumn('pagos', 'iva_monto')) {
                $table->decimal('iva_monto', 10, 2)->default(0)->after('base_imponible');
            }
            if (!Schema::hasColumn('pagos', 'igtf_monto')) {
                $table->decimal('igtf_monto', 10, 2)->default(0)->after('iva_monto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['cliente_fiscal_id']);
            $table->dropForeign(['pago_origen_id']);
            $table->dropForeign(['tipo_nota_credito_id']);
            $table->dropForeign(['tipo_nota_debito_id']);

            $table->dropColumn([
                'es_factura_fiscal',
                'cliente_fiscal_id',
                'condicion_pago',
                'seniat_tipo_documento',
                'fecha_emision_fiscal',
                'numero_control_fiscal',
                'pago_origen_id',
                'tipo_nota_credito_id',
                'tipo_nota_debito_id',
                'motivo_nota',
                'base_imponible',
                'iva_monto',
                'igtf_monto'
            ]);
        });
    }
};
