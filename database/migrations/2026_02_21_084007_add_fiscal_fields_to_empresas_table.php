<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Datos fiscales
            $table->string('rif_fiscal', 20)->nullable()->after('documento');
            $table->text('direccion_fiscal_completa')->nullable()->after('direccion');
            $table->string('telefono_fiscal', 20)->nullable()->after('telefono');
            $table->string('punto_emision', 10)->default('0001')->after('telefono_fiscal');
            
            // Rangos de facturación autorizados
            $table->string('numero_control_desde', 50)->nullable();
            $table->string('numero_control_hasta', 50)->nullable();
            $table->date('fecha_autorizacion_seniat')->nullable();
            
            $table->index('rif_fiscal');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex(['rif_fiscal']);
            $table->dropColumn([
                'rif_fiscal',
                'direccion_fiscal_completa',
                'telefono_fiscal',
                'punto_emision',
                'numero_control_desde',
                'numero_control_hasta',
                'fecha_autorizacion_seniat'
            ]);
        });
    }
};
