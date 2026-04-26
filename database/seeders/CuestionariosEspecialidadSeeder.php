<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CuestionariosEspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('── Creando cuestionarios por especialidad ──');

        $this->call([
            CuestionarioMedicinaGeneralSeeder::class,
            CuestionarioMedicinaInternaSeeder::class,
            CuestionarioCardiologiaSeeder::class,
            CuestionarioGinecologiaSeeder::class,
            CuestionarioGastroenterologiaSeeder::class,
            CuestionarioNeurologiaSeeder::class,
            CuestionarioNeurocirugiaSeeder::class,
            CuestionarioPediatriaSeeder::class,
            CuestionarioNefrologiaSeeder::class,
            CuestionarioOtorrinolaringologiaSeeder::class,
            CuestionarioOftalmologiaSeeder::class,
            CuestionarioCirugiaGeneralSeeder::class,
            CuestionarioCirugiaPediatricaSeeder::class,
            CuestionarioMastologiaSeeder::class,
        ]);

        $this->command->info('✓ Todos los cuestionarios creados exitosamente.');
    }
}
