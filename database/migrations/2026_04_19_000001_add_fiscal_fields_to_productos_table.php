<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('aplica_iva')->default(true)->after('precio_venta');
            $table->boolean('exento_iva')->default(false)->after('aplica_iva');
            $table->decimal('iva_alicuota', 5, 2)->default(16.00)->after('exento_iva');
            $table->string('codigo_fiscal', 20)->nullable()->after('iva_alicuota');
            $table->string('categoria_fiscal', 50)->default('producto')->after('codigo_fiscal');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'aplica_iva',
                'exento_iva', 
                'iva_alicuota',
                'codigo_fiscal',
                'categoria_fiscal'
            ]);
        });
    }
};