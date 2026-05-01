<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PasoProceso;
use App\Models\Empresa;

class PasosProcesoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        $pasos = [
            ['codigo' => 'signos_vitales', 'nombre' => 'Signos Vitales', 'icono' => 'ri-heart-pulse-line'],
            ['codigo' => 'cuestionario',   'nombre' => 'Cuestionario',   'icono' => 'ri-survey-line'],
            ['codigo' => 'evaluacion',     'nombre' => 'Evaluación Clínica', 'icono' => 'ri-stethoscope-line'],
            ['codigo' => 'estudios',       'nombre' => 'Estudios',       'icono' => 'ri-microscope-line'],
            ['codigo' => 'tratamientos',   'nombre' => 'Tratamientos',   'icono' => 'ri-medicine-bottle-line'],
            ['codigo' => 'reposo',         'nombre' => 'Reposo Médico',  'icono' => 'ri-hotel-bed-line'],
        ];

        foreach ($empresas as $empresa) {
            foreach ($pasos as $paso) {
                PasoProceso::firstOrCreate(
                    ['codigo' => $paso['codigo'], 'empresa_id' => $empresa->id],
                    array_merge($paso, ['activo' => true, 'empresa_id' => $empresa->id])
                );
            }
        }
    }
}
