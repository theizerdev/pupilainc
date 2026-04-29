<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->string('firma_digital')->nullable()->after('licencia_medica');
            $table->string('sello_digital')->nullable()->after('firma_digital');
            $table->json('config_firma')->nullable()->after('sello_digital');
        });
    }

    public function down(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropColumn(['firma_digital', 'sello_digital', 'config_firma']);
        });
    }
};
