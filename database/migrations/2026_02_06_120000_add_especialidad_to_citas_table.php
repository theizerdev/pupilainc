<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('especialidad_id')->nullable()->after('medico_id')->constrained('especialidades')->nullOnDelete();
            $table->foreignId('subespecialidad_id')->nullable()->after('especialidad_id')->constrained('subespecialidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['subespecialidad_id']);
            $table->dropForeign(['especialidad_id']);
            $table->dropColumn(['subespecialidad_id', 'especialidad_id']);
        });
    }
};
