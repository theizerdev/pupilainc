<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anulacion_talonarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('serie_id')->nullable()->constrained('series')->onDelete('set null');
            $table->string('tipo_documento', 20); // factura, nota_credito, nota_debito, boleta, recibo
            $table->string('serie_afectada', 20); // F001, NC01, etc.
            $table->string('numero_control_desde', 20);
            $table->string('numero_control_hasta', 20);
            $table->string('correlativo_desde', 20);
            $table->string('correlativo_hasta', 20);
            $table->integer('cantidad_documentos');
            $table->date('fecha_anulacion');
            $table->enum('motivo', [
                'dano_fisico',
                'robo',
                'extravio',
                'error_impresion',
                'cambio_datos_fiscales',
                'fin_actividad',
                'otro',
            ]);
            $table->text('descripcion_motivo');
            $table->enum('estado', ['registrado', 'reportado_seniat', 'confirmado'])->default('registrado');
            $table->date('fecha_reporte_seniat')->nullable();
            $table->string('numero_reporte_seniat', 50)->nullable();
            $table->boolean('acta_destruccion')->default(false);
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id', 'tipo_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anulacion_talonarios');
    }
};
