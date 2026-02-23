<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20);
            $table->date('fecha');
            $table->enum('tipo', ['apertura', 'diario', 'ajuste', 'cierre'])->default('diario');
            $table->text('descripcion');
            $table->enum('estado', ['borrador', 'aprobado', 'anulado'])->default('borrador');
            $table->string('referencia_tipo')->nullable(); // factura, pago, nota_credito, nota_debito
            $table->foreignId('referencia_id')->nullable(); // ID del documento origen
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['empresa_id', 'fecha']);
            $table->index(['referencia_tipo', 'referencia_id']);
            $table->index(['estado', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asientos_contables');
    }
};
