<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Primero eliminamos el índice único existente
            $table->dropUnique(['api_key']);
            // Luego modificamos el tamaño del campo
            $table->string('api_key', 500)->nullable()->change();
            // Finalmente volvemos a crear el índice único
            $table->unique('api_key');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Eliminamos el índice único
            $table->dropUnique(['api_key']);
            // Restauramos el tamaño original
            $table->string('api_key', 64)->nullable()->change();
            // Volvemos a crear el índice único con el tamaño original
            $table->unique('api_key');
        });
    }
};