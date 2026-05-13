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
        Schema::table('pagos', function (Blueprint $table) {
            // Change metodo_pago from ENUM to VARCHAR to support all payment methods
            $table->string('metodo_pago', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Revert back to ENUM with original values
            $table->enum('metodo_pago', [
                'efectivo',
                'bbva_dr',
                'mifel_dra',
                'cuenta_dr',
                'cuenta_dra',
                'tarjeta_credito',
                'tarjeta_debito'
            ])->change();
        });
    }
};
