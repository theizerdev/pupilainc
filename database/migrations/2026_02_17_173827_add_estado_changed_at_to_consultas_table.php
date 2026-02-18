<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->timestamp('estado_changed_at')->nullable()->after('estado');
        });

        // Inicializar con updated_at para registros existentes
        DB::statement('UPDATE consultas SET estado_changed_at = updated_at WHERE estado_changed_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn('estado_changed_at');
        });
    }
};
