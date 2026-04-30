<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_honorarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->onDelete('set null');
            $table->json('servicios_detalle'); // Array de servicios con sus honorarios
            $table->decimal('total_facturado_usd', 12, 2)->default(0);
            $table->decimal('total_facturado_bs', 15, 2)->default(0);
            $table->decimal('total_honorarios_medico_usd', 12, 2)->default(0);
            $table->decimal('total_honorarios_medico_bs', 15, 2)->default(0);
            $table->decimal('total_ingresos_clinica_usd', 12, 2)->default(0);
            $table->decimal('total_ingresos_clinica_bs', 15, 2)->default(0);
            $table->enum('estado', ['calculado', 'incluido_nomina', 'pagado'])->default('calculado');
            $table->timestamp('fecha_calculo')->useCurrent();
            $table->timestamps();
            
            $table->index(['consulta_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_honorarios');
    }
};