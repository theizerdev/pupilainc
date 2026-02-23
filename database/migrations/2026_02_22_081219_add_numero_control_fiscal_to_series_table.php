<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->string('control_fiscal_actual', 20)->nullable()->after('correlativo_actual');
            $table->integer('longitud_control_fiscal')->default(8)->after('longitud_correlativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn(['control_fiscal_actual', 'longitud_control_fiscal']);
        });
    }
};
