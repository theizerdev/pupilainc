<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes_fiscales', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('razon_social')
                  ->comment('Nombre para clientes sin RIF / consumidor final');
            $table->boolean('es_cliente_rapido')->default(false)->after('nombre')
                  ->comment('Cliente creado desde el POS sin datos fiscales completos');
        });
    }

    public function down(): void
    {
        Schema::table('clientes_fiscales', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'es_cliente_rapido']);
        });
    }
};
