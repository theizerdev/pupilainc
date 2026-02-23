<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('pago_origen_id')->nullable()->after('consulta_id')->constrained('pagos')->onDelete('cascade');
            $table->foreignId('tipo_nota_credito_id')->nullable()->after('pago_origen_id')->constrained('tipos_nota_credito')->onDelete('set null');
            $table->foreignId('tipo_nota_debito_id')->nullable()->after('tipo_nota_credito_id')->constrained('tipos_nota_debito')->onDelete('set null');
            $table->text('motivo_nota')->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['pago_origen_id']);
            $table->dropForeign(['tipo_nota_credito_id']);
            $table->dropForeign(['tipo_nota_debito_id']);
            $table->dropColumn(['pago_origen_id', 'tipo_nota_credito_id', 'tipo_nota_debito_id', 'motivo_nota']);
        });
    }
};
