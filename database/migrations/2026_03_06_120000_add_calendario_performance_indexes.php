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
        // Índices para optimizar las consultas del calendario
        
        // Índices para tabla citas
        Schema::table('citas', function (Blueprint $table) {
            // Índice compuesto para búsquedas por fecha y médico
            $table->index(['fecha_inicio', 'medico_id'], 'idx_citas_fecha_medico');
            
            // Índice para estado y empresa
            $table->index(['estado', 'empresa_id'], 'idx_citas_estado_empresa');
            
            // Índice para paciente y estado
            $table->index(['paciente_id', 'estado'], 'idx_citas_paciente_estado');
            
            // Índice compuesto para rango de fechas
            $table->index(['fecha_inicio', 'fecha_fin'], 'idx_citas_fechas_rango');
        });
        
        // Índices para tabla consultas
        Schema::table('consultas', function (Blueprint $table) {
            // Índice compuesto para fecha y médico
            $table->index(['fecha_consulta', 'medico_id'], 'idx_consultas_fecha_medico');
            
            // Índice para estado y empresa
            $table->index(['estado', 'empresa_id'], 'idx_consultas_estado_empresa');
            
            // Índice para paciente y estado
            $table->index(['paciente_id', 'estado'], 'idx_consultas_paciente_estado');
            
            // Índice para estado y fecha de cambio
            $table->index(['estado', 'estado_changed_at'], 'idx_consultas_estado_fecha');
        });
        
        // Índices para tabla medicos
        Schema::table('medicos', function (Blueprint $table) {
            // Índice para búsquedas por empresa y estado
            $table->index(['empresa_id', 'status'], 'idx_medicos_empresa_estado');
            
            // Índice para usuario_id
            $table->index('user_id', 'idx_medicos_user_id');
        });
        
        // Índices para tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            // Índice para búsquedas por empresa y estado
            $table->index(['empresa_id', 'status'], 'idx_pacientes_empresa_estado');
            
            // Índice para búsquedas por nombre
            $table->index(['nombres', 'apellidos'], 'idx_pacientes_nombres');
        });
        
        // Índices para tabla especialidades
        Schema::table('especialidades', function (Blueprint $table) {
            // Índice para estado y empresa
            $table->index(['status', 'empresa_id'], 'idx_especialidades_status_empresa');
        });
        
        // Índices para tabla tipo_consultas
        Schema::table('tipo_consultas', function (Blueprint $table) {
            // Índice para estado y empresa
            $table->index(['status', 'empresa_id'], 'idx_tipo_consultas_status_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de citas
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex('idx_citas_fecha_medico');
            $table->dropIndex('idx_citas_estado_empresa');
            $table->dropIndex('idx_citas_paciente_estado');
            $table->dropIndex('idx_citas_fechas_rango');
        });
        
        // Eliminar índices de consultas
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex('idx_consultas_fecha_medico');
            $table->dropIndex('idx_consultas_estado_empresa');
            $table->dropIndex('idx_consultas_paciente_estado');
            $table->dropIndex('idx_consultas_estado_fecha');
        });
        
        // Eliminar índices de medicos
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropIndex('idx_medicos_empresa_estado');
            $table->dropIndex('idx_medicos_user_id');
        });
        
        // Eliminar índices de pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex('idx_pacientes_empresa_estado');
            $table->dropIndex('idx_pacientes_nombres');
        });
        
        // Eliminar índices de especialidades
        Schema::table('especialidades', function (Blueprint $table) {
            $table->dropIndex('idx_especialidades_status_empresa');
        });
        
        // Eliminar índices de tipo_consultas
        Schema::table('tipo_consultas', function (Blueprint $table) {
            $table->dropIndex('idx_tipo_consultas_status_empresa');
        });
    }
};