<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Primero limpiar duplicados dejando solo el registro más antiguo
        \DB::statement("
            DELETE u1 FROM users u1
            INNER JOIN users u2
            WHERE u1.id > u2.id
              AND u1.phone = u2.phone
              AND u1.phone IS NOT NULL
              AND u1.phone != ''
        ");

        Schema::table('users', function (Blueprint $table) {
            // Hacer nullable primero para que el unique no afecte NULLs
            $table->string('phone')->nullable()->change();
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
