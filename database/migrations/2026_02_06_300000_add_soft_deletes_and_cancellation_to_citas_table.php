<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('motivo_cancelacion')->nullable()->after('estado');
            $table->foreignId('cancelado_por')->nullable()->after('motivo_cancelacion')->constrained('users')->onDelete('set null');
            $table->index(['empresa_id', 'medico_id', 'fecha_inicio']);
        });

        
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['motivo_cancelacion', 'cancelado_por']);
            $table->dropIndex(['empresa_id', 'medico_id', 'fecha_inicio']);
        });

       
    }
};
