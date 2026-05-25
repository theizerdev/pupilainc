<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baremos', function (Blueprint $table) {
            $table->decimal('porcentaje_medico', 5, 2)->default(60.00)->after('duracion_minutos');
            $table->decimal('porcentaje_clinica', 5, 2)->default(40.00)->after('porcentaje_medico');
        });
    }

    public function down(): void
    {
        Schema::table('baremos', function (Blueprint $table) {
            $table->dropColumn(['porcentaje_medico', 'porcentaje_clinica']);
        });
    }
};