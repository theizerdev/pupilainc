<?php

namespace Database\Seeders;

use App\Models\EspecialidadPlantilla;
use Illuminate\Database\Seeder;

class UpdatePlantillasWizardConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar todas las plantillas existentes para usar wizard por defecto
        $count = EspecialidadPlantilla::whereNull('usar_wizard_en_consultorio')
            ->orWhere('usar_wizard_en_consultorio', false)
            ->update(['usar_wizard_en_consultorio' => true]);

        $this->command->info("✓ {$count} plantilla(s) actualizada(s) con usar_wizard_en_consultorio = true");
    }
}
