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
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('nombre');
            $table->foreignId('especie_id')->constrained('especies')->onDelete('restrict');
            $table->foreignId('raza_id')->nullable()->constrained('razas')->onDelete('set null');
            
            // Características físicas
            $table->enum('sexo', ['macho', 'hembra'])->default('macho');
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('peso_actual_kg', 5, 2)->nullable()->comment('Peso actual en kilogramos');
            $table->string('color_pelaje')->nullable()->comment('Color o patrón del pelaje');
            $table->text('marcas_distintivas')->nullable()->comment('Cicatrices, manchas, etc.');
            
            // Identificación
            $table->string('microchip')->nullable()->unique()->comment('Número de microchip');
            $table->string('numero_registro')->nullable()->unique()->comment('Número de registro oficial');
            $table->string('foto')->nullable()->comment('Ruta de la foto de la mascota');
            
            // Estado reproductivo y salud
            $table->boolean('esterilizado')->default(false);
            $table->date('fecha_esterilizacion')->nullable();
            $table->boolean('activo')->default(true);
            
            // Relación con propietario
            $table->unsignedBigInteger('propietario_id')->nullable()->comment('ID del propietario principal');
            
            // Notas adicionales
            $table->text('notas_generales')->nullable();
            $table->text('alergias_conocidas')->nullable();
            $table->text('condiciones_cronicas')->nullable();
            $table->enum('nivel_agresividad', ['tranquilo', 'nervioso', 'agresivo_leve', 'agresivo'])->default('tranquilo');
            
            // Multi-tenancy
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['especie_id', 'activo']);
            $table->index(['propietario_id']);
            $table->index(['empresa_id', 'activo']);
            $table->index(['nombre', 'empresa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
