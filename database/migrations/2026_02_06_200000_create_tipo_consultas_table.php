<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_consultas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('color', 7)->default('#4e73df');
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 50)->default('fas fa-notes-medical');
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_consultas');
    }
};
