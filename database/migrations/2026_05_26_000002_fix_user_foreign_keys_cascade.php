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
        // Corregir signos_vitales - created_by y updated_by
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
            
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Corregir consulta_estudios - created_by y updated_by
        Schema::table('consulta_estudios', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
            
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Corregir consulta_evaluaciones - created_by y updated_by
        Schema::table('consulta_evaluaciones', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
            
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Corregir consulta_tratamientos - created_by y updated_by
        Schema::table('consulta_tratamientos', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
            
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Corregir diagnosticos - created_by
        Schema::table('diagnosticos', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir signos_vitales
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Revertir consulta_estudios
        Schema::table('consulta_estudios', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Revertir consulta_evaluaciones
        Schema::table('consulta_evaluaciones', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Revertir consulta_tratamientos
        Schema::table('consulta_tratamientos', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Revertir diagnosticos
        Schema::table('diagnosticos', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
