<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enfermero_especialidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enfermero_id');
            $table->string('especialidad', 100);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();
            
            $table->foreign('enfermero_id')->references('id')->on('enfermeros')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            
            $table->index(['enfermero_id', 'especialidad']);
            $table->index(['empresa_id', 'sucursal_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('enfermero_especialidades');
    }
};