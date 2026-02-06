<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cita_recordatorios', function (Blueprint $table) {
            if (!Schema::hasColumn('cita_recordatorios', 'intentos')) {
                $table->unsignedSmallInteger('intentos')->default(0)->after('canal');
            }
        });
    }

    public function down()
    {
        Schema::table('cita_recordatorios', function (Blueprint $table) {
            $table->dropColumn('intentos');
        });
    }
};
