<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asientos_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asientos_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_id')->constrained('cuentas_contables')->cascadeOnDelete();
            $table->decimal('debe', 15, 2)->default(0);
            $table->decimal('haber', 15, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->index(['asiento_id', 'cuenta_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asientos_detalles');
    }
};
