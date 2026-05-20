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
        // Crear tabla de tipos de atención veterinaria
        Schema::create('tipos_atencion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('codigo')->unique()->comment('Código único: consulta_general, emergencia, etc.');
            $table->string('color')->default('#0d6efd')->comment('Color para el calendario');
            $table->string('icono')->nullable()->comment('Icono Material Design o RemixIcon');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0)->comment('Orden de visualización');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo', 'orden']);
        });

        // Insertar valores veterinarios por defecto
        DB::table('tipos_atencion')->insert([
            [
                'nombre' => 'Consulta General',
                'codigo' => 'consulta_general',
                'descripcion' => 'Consulta veterinaria general de rutina',
                'color' => '#0d6efd',
                'icono' => 'mdi-stethoscope',
                'activo' => true,
                'orden' => 1,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Emergencia',
                'codigo' => 'emergencia',
                'descripcion' => 'Atención de emergencia veterinaria urgente',
                'color' => '#dc3545',
                'icono' => 'mdi-alert',
                'activo' => true,
                'orden' => 2,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Cirugía',
                'codigo' => 'cirugia',
                'descripcion' => 'Procedimiento quirúrgico programado',
                'color' => '#fd7e14',
                'icono' => 'mdi-knife',
                'activo' => true,
                'orden' => 3,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Vacunación',
                'codigo' => 'vacunacion',
                'descripcion' => 'Aplicación de vacunas preventivas',
                'color' => '#28a745',
                'icono' => 'mdi-syringe',
                'activo' => true,
                'orden' => 4,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Desparasitación',
                'codigo' => 'desparasitacion',
                'descripcion' => 'Tratamiento antiparasitario interno o externo',
                'color' => '#6f42c1',
                'icono' => 'mdi-bug',
                'activo' => true,
                'orden' => 5,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Control de Salud',
                'codigo' => 'control_salud',
                'descripcion' => 'Control preventivo y seguimiento de salud',
                'color' => '#20c997',
                'icono' => 'mdi-heart-pulse',
                'activo' => true,
                'orden' => 6,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Seguimiento',
                'codigo' => 'seguimiento',
                'descripcion' => 'Seguimiento post-tratamiento o post-cirugía',
                'color' => '#ffc107',
                'icono' => 'mdi-calendar-check',
                'activo' => true,
                'orden' => 7,
                'created_at' => now(),
            ],
            [
                'nombre' => 'Primera Visita',
                'codigo' => 'primera_visita',
                'descripcion' => 'Primera consulta del paciente en la clínica',
                'color' => '#17a2b8',
                'icono' => 'mdi-paw',
                'activo' => true,
                'orden' => 8,
                'created_at' => now(),
            ],
        ]);

        // Agregar columna temporal tipo_atencion_id con relación
        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_atencion_id')->nullable()->after('tipo_atencion')
                  ->comment('Relación con tabla tipos_atencion');
            $table->foreign('tipo_atencion_id')->references('id')->on('tipos_atencion')->onDelete('set null');
        });

        // Migrar datos: mapear los valores ENUM a los IDs de la nueva tabla
        $tiposMap = [
            'consulta_general' => 1,
            'emergencia' => 2,
            'cirugia' => 3,
            'vacunacion' => 4,
            'desparasitacion' => 5,
            'control_salud' => 6,
            'seguimiento' => 7,
            'primera_visita' => 8,
        ];

        foreach ($tiposMap as $codigo => $id) {
            DB::table('citas')
                ->where('tipo_atencion', $codigo)
                ->update(['tipo_atencion_id' => $id]);
        }

        // Eliminar la columna ENUM original
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('tipo_atencion');
        });

        // Renombrar la columna temporal
        Schema::table('citas', function (Blueprint $table) {
            $table->renameColumn('tipo_atencion_id', 'tipo_atencion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columna ENUM
        Schema::table('citas', function (Blueprint $table) {
            $table->enum('tipo_atencion', [
                'consulta_general', 'emergencia', 'cirugia', 'vacunacion',
                'desparasitacion', 'control_salud', 'seguimiento', 'primera_visita'
            ])->default('consulta_general')->after('tipo_consulta_id');
        });

        // Migrar datos de vuelta
        $tiposMap = [
            1 => 'consulta_general',
            2 => 'emergencia',
            3 => 'cirugia',
            4 => 'vacunacion',
            5 => 'desparasitacion',
            6 => 'control_salud',
            7 => 'seguimiento',
            8 => 'primera_visita',
        ];

        foreach ($tiposMap as $id => $codigo) {
            DB::table('citas')
                ->where('tipo_atencion', $id)
                ->update(['tipo_atencion' => $codigo]);
        }

        // Eliminar relación
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['tipo_atencion']);
            $table->dropColumn('tipo_atencion');
        });

        Schema::dropIfExists('tipos_atencion');
    }
};
