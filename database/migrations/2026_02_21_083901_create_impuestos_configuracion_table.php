<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos_configuracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            $table->string('nombre', 50); // IVA, IGTF
            $table->string('codigo', 10); // IVA, IGTF
            $table->enum('tipo', ['porcentaje', 'fijo'])->default('porcentaje');
            $table->decimal('porcentaje', 5, 2)->default(0); // 16.00, 3.00
            $table->decimal('monto_fijo', 10, 2)->nullable();
            
            // Condiciones de aplicación
            $table->boolean('aplica_servicios')->default(true);
            $table->boolean('aplica_productos')->default(true);
            $table->json('metodos_pago_aplicables')->nullable(); // ['transferencia_usd', 'zelle', etc]
            
            // Coletilla IGTF
            $table->text('coletilla_fiscal')->nullable(); // Texto legal obligatorio
            
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
            
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuestos_configuracion');
    }
};
