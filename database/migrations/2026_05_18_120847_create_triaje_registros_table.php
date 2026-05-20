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
        Schema::create('triaje_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->enum('tipo_emergencia', ['urgente', 'semi-urgente', 'no-urgente']);
            $table->tinyInteger('prioridad')->comment('1-alta, 2-media, 3-baja');
            $table->string('motivo_ingreso', 500);
            $table->string('duracion_sintomas', 200)->nullable();
            $table->boolean('hemorragias_controladas')->default(false);
            $table->boolean('heridas_abiertas')->default(false);
            $table->boolean('fractura_sospechada')->default(false);
            $table->text('observaciones')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['consulta_id', 'empresa_id', 'sucursal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triaje_registros');
    }
};
