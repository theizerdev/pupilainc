<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_diagnostico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->cascadeOnDelete();
            $table->foreignId('diagnostico_id')->constrained('diagnosticos')->cascadeOnDelete();
            $table->enum('tipo', ['principal', 'secundario'])->default('secundario');
            $table->integer('orden')->default(0);
            $table->timestamps();
            
            $table->index(['consulta_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_diagnostico');
    }
};
