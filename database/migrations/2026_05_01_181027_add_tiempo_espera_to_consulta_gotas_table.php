<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consulta_gotas', function (Blueprint $table) {
            $table->integer('tiempo_espera')->default(30)->after('observaciones')
                  ->comment('Minutos esperados de dilatacion');
        });
    }

    public function down(): void
    {
        Schema::table('consulta_gotas', function (Blueprint $table) {
            $table->dropColumn('tiempo_espera');
        });
    }
};
