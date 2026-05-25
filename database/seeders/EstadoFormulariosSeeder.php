<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EstadoFormulariosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('── Creando formularios por estado ──');

        $this->call([
            EFOftalmologiaSeeder::class,
            EFMedicinaGeneralSeeder::class,
            EFMedicinaInternaSeeder::class,
            EFCardiologiaSeeder::class,
            EFGinecologiaSeeder::class,
            EFGastroenterologiaSeeder::class,
            EFNeurologiaSeeder::class,
            EFNeurocirugiaSeeder::class,
            EFPediatriaSeeder::class,
            EFNefrologiaSeeder::class,
            EFOtorrinolaringologiaSeeder::class,
            // Nuevos seeders agregados
            EFCirugiaGeneralSeeder::class,
            EFCirugiaPediatricaSeeder::class,
            EFMastologiaSeeder::class,
            EFOrtopediaPediatricaSeeder::class,
        ]);

        $this->command->info('✓ Formularios por estado creados exitosamente.');
    }
}
