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
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('total_tarjeta_credito', 15, 2)->default(0)->after('total_tarjetas');
            $table->decimal('total_tarjeta_debito', 15, 2)->default(0)->after('total_tarjeta_credito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn(['total_tarjeta_credito', 'total_tarjeta_debito']);
        });
    }
};
