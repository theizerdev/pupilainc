<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlantillasClinicasSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('── Creando especialidades médicas ──');
        $this->call(EspecialidadesMedicasSeeder::class);

        $this->command->info('── Creando plantillas clínicas ──');
        $this->call([
            PlantillaMedicinaGeneralSeeder::class,
            PlantillaMedicinaInternaSeeder::class,
            PlantillaCardiologiaSeeder::class,
            PlantillaGinecologiaSeeder::class,
            PlantillaGastroenterologiaSeeder::class,
            PlantillaNeuroLogiaSeeder::class,
            PlantillaNeurocirugiaSeeder::class,
            PlantillaPediatriaSeeder::class,
            PlantillaNefrologiaSeeder::class,
            PlantillaOtorrinolaringologiaSeeder::class,
            PlantillaOftalmologiaSeeder::class,
            PlantillaCirugiaGeneralSeeder::class,
            PlantillaCirugiaPediatricaSeeder::class,
            PlantillaMastologiaSeeder::class,
        ]);

        $this->command->info('✓ Todas las plantillas clínicas creadas exitosamente.');
    }
}
