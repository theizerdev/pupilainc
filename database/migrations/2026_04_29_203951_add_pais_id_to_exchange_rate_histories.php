<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_rate_monthly_histories', function (Blueprint $table) {
            $table->foreignId('pais_id')->after('id')->nullable()->constrained('pais')->onDelete('cascade');
            $table->dropUnique(['year', 'month']);
            $table->unique(['year', 'month', 'pais_id']);
        });
    }

    public function down(): void
    {
        Schema::table('exchange_rate_monthly_histories', function (Blueprint $table) {
            $table->dropUnique(['year', 'month', 'pais_id']);
            $table->unique(['year', 'month']);
            $table->dropForeign(['pais_id']);
            $table->dropColumn('pais_id');
        });
    }
};