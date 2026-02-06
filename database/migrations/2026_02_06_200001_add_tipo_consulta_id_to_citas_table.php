<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'tipo_consulta_id')) {
                $table->foreignId('tipo_consulta_id')->nullable()->after('estado')->constrained('tipo_consultas')->onDelete('set null');
                $table->index('tipo_consulta_id');
            }
        });
    }

    public function down()
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['tipo_consulta_id']);
            $table->dropColumn('tipo_consulta_id');
        });
    }
};
