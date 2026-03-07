<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Función auxiliar para verificar si existe un índice
        $indexExists = function($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $index) {
                if ($index->Key_name === $indexName) {
                    return true;
                }
            }
            return false;
        };
        
        // Índices para tabla citas
        Schema::table('citas', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('citas', 'idx_citas_fecha_medico')) {
                $table->index(['fecha_inicio', 'medico_id'], 'idx_citas_fecha_medico');
            }
            
            if (!$indexExists('citas', 'idx_citas_estado_empresa')) {
                $table->index(['estado', 'empresa_id'], 'idx_citas_estado_empresa');
            }
            
            if (!$indexExists('citas', 'idx_citas_paciente_estado')) {
                $table->index(['paciente_id', 'estado'], 'idx_citas_paciente_estado');
            }
            
            if (!$indexExists('citas', 'idx_citas_fechas_rango')) {
                $table->index(['fecha_inicio', 'fecha_fin'], 'idx_citas_fechas_rango');
            }
        });
        
        // Índices para tabla consultas
        Schema::table('consultas', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('consultas', 'idx_consultas_fecha_medico')) {
                $table->index(['fecha_consulta', 'medico_id'], 'idx_consultas_fecha_medico');
            }
            
            if (!$indexExists('consultas', 'idx_consultas_estado_empresa')) {
                $table->index(['estado', 'empresa_id'], 'idx_consultas_estado_empresa');
            }
            
            if (!$indexExists('consultas', 'idx_consultas_paciente_estado')) {
                $table->index(['paciente_id', 'estado'], 'idx_consultas_paciente_estado');
            }
            
            if (!$indexExists('consultas', 'idx_consultas_estado_fecha')) {
                $table->index(['estado', 'estado_changed_at'], 'idx_consultas_estado_fecha');
            }
        });
        
        // Índices para tabla medicos
        Schema::table('medicos', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('medicos', 'idx_medicos_empresa_estado')) {
                $table->index(['empresa_id', 'status'], 'idx_medicos_empresa_estado');
            }
            
            if (!$indexExists('medicos', 'idx_medicos_user_id')) {
                $table->index('user_id', 'idx_medicos_user_id');
            }
        });
        
        // Índices para tabla pacientes
        Schema::table('pacientes', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('pacientes', 'idx_pacientes_empresa_estado')) {
                $table->index(['empresa_id', 'status'], 'idx_pacientes_empresa_estado');
            }
            
            if (!$indexExists('pacientes', 'idx_pacientes_nombres')) {
                $table->index(['nombres', 'apellidos'], 'idx_pacientes_nombres');
            }
        });
        
        // Índices para tabla especialidades
        Schema::table('especialidades', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('especialidades', 'idx_especialidades_status_empresa')) {
                $table->index(['status', 'empresa_id'], 'idx_especialidades_status_empresa');
            }
        });
        
        // Índices para tabla tipo_consultas
        Schema::table('tipo_consultas', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('tipo_consultas', 'idx_tipo_consultas_status_empresa')) {
                $table->index(['status', 'empresa_id'], 'idx_tipo_consultas_status_empresa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En caso de rollback, eliminar solo los índices que creamos
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex('idx_citas_fecha_medico');
            $table->dropIndex('idx_citas_estado_empresa');
            $table->dropIndex('idx_citas_paciente_estado');
            $table->dropIndex('idx_citas_fechas_rango');
        });
        
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex('idx_consultas_fecha_medico');
            $table->dropIndex('idx_consultas_estado_empresa');
            $table->dropIndex('idx_consultas_paciente_estado');
            $table->dropIndex('idx_consultas_estado_fecha');
        });
        
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropIndex('idx_medicos_empresa_estado');
            $table->dropIndex('idx_medicos_user_id');
        });
        
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex('idx_pacientes_empresa_estado');
            $table->dropIndex('idx_pacientes_nombres');
        });
        
        Schema::table('especialidades', function (Blueprint $table) {
            $table->dropIndex('idx_especialidades_status_empresa');
        });
        
        Schema::table('tipo_consultas', function (Blueprint $table) {
            $table->dropIndex('idx_tipo_consultas_status_empresa');
        });
    }
};