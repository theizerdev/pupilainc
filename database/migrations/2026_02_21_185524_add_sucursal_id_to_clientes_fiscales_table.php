<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes_fiscales', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->after('empresa_id')->constrained('sucursales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('clientes_fiscales', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
