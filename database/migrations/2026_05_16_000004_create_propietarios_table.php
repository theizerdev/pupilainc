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
        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            
            // Información personal
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('documento_identidad')->nullable()->comment('Cédula, DNI, etc.');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable();
            
            // Contacto
            $table->string('telefono')->nullable();
            $table->string('telefono_alternativo')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            
            // Información adicional
            $table->string('ocupacion')->nullable();
            $table->text('notas')->nullable();
            $table->string('foto')->nullable();
            
            // Preferencias de contacto
            $table->enum('preferencia_contacto', ['whatsapp', 'llamada', 'email', 'sms'])->default('whatsapp');
            $table->boolean('acepta_recordatorios')->default(true);
            $table->boolean('acepta_promociones')->default(false);
            
            // Estado
            $table->boolean('activo')->default(true);
            
            // Multi-tenancy
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['empresa_id', 'activo']);
            $table->index(['telefono']);
            $table->index(['email']);
            $table->index(['nombres', 'apellidos']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propietarios');
    }
};
