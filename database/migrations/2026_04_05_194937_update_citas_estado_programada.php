<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand enum to include new values alongside old ones
        DB::statement("ALTER TABLE citas MODIFY COLUMN estado ENUM('pendiente', 'programada', 'confirmada', 'en_curso', 'completada', 'cancelada', 'no_asistio', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_consultorio_optometrista', 'en_gotas', 'en_optica', 'en_estudio', 'finalizada', 'pagada') DEFAULT 'programada'");

        // Step 2: Migrate data
        DB::table('citas')->where('estado', 'pendiente')->update(['estado' => 'programada']);
        DB::table('citas')->where('estado', 'completada')->update(['estado' => 'finalizada']);

        // Step 3: Remove old enum values
        DB::statement("ALTER TABLE citas MODIFY COLUMN estado ENUM('programada', 'confirmada', 'en_curso', 'cancelada', 'no_asistio', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_consultorio_optometrista', 'en_gotas', 'en_optica', 'en_estudio', 'finalizada', 'pagada') DEFAULT 'programada'");
    }

    public function down(): void
    {
        // Expand enum to include old values
        DB::statement("ALTER TABLE citas MODIFY COLUMN estado ENUM('pendiente', 'programada', 'confirmada', 'en_curso', 'completada', 'cancelada', 'no_asistio', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_consultorio_optometrista', 'en_gotas', 'en_optica', 'en_estudio', 'finalizada', 'pagada') DEFAULT 'pendiente'");

        // Revert data
        DB::table('citas')->where('estado', 'programada')->update(['estado' => 'pendiente']);
        DB::table('citas')->where('estado', 'finalizada')->update(['estado' => 'completada']);

        // Restore original enum
        DB::statement("ALTER TABLE citas MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'en_curso', 'completada', 'cancelada', 'no_asistio', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_consultorio_optometrista', 'en_gotas', 'en_optica', 'en_estudio', 'finalizada', 'pagada') DEFAULT 'pendiente'");
    }
};
