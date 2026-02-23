<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->enum('tipo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'egreso', 'costo']);
            $table->enum('naturaleza', ['deudora', 'acreedora']);
            $table->integer('nivel')->default(1);
            $table->foreignId('cuenta_padre_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            $table->boolean('acepta_movimientos')->default(true);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['empresa_id', 'activo']);
            $table->index(['tipo', 'activo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuentas_contables');
    }
};
