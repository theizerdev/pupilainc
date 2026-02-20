<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->dropForeign(['enfermero_id']);
            $table->foreignId('enfermero_id')->nullable()->change()->constrained('enfermeros');
        });
    }

    public function down(): void
    {
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->dropForeign(['enfermero_id']);
            $table->foreignId('enfermero_id')->nullable(false)->change()->constrained('enfermeros');
        });
    }
};
