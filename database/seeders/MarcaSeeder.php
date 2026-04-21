<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = \App\Models\Empresa::first();
        $sucursal = \App\Models\Sucursal::first();

        $marcas = [
            // Farmacéuticas
            [
                'nombre'      => 'Bayer',
                'descripcion' => 'Empresa farmacéutica y de ciencias de la vida',
                'sitio_web'   => 'https://www.bayer.com',
                'status'      => true,
            ],
            [
                'nombre'      => 'Pfizer',
                'descripcion' => 'Compañía biofarmacéutica multinacional',
                'sitio_web'   => 'https://www.pfizer.com',
                'status'      => true,
            ],
            [
                'nombre'      => 'Roche',
                'descripcion' => 'Empresa líder en diagnóstico y farmacéutica',
                'sitio_web'   => 'https://www.roche.com',
                'status'      => true,
            ],
            [
                'nombre'      => 'Novartis',
                'descripcion' => 'Empresa farmacéutica multinacional suiza',
                'sitio_web'   => 'https://www.novartis.com',
                'status'      => true,
            ],
            [
                'nombre'      => 'Abbott',
                'descripcion' => 'Empresa de salud global con productos diagnósticos y farmacéuticos',
                'sitio_web'   => 'https://www.abbott.com',
                'status'      => true,
            ],
            // Equipos médicos
            [
                'nombre'      => 'Philips Healthcare',
                'descripcion' => 'Equipos de diagnóstico por imagen y monitoreo clínico',
                'sitio_web'   => 'https://www.philips.com/healthcare',
                'status'      => true,
            ],
            [
                'nombre'      => 'Siemens Healthineers',
                'descripcion' => 'Tecnología médica para diagnóstico y terapia',
                'sitio_web'   => 'https://www.siemens-healthineers.com',
                'status'      => true,
            ],
            [
                'nombre'      => 'Medtronic',
                'descripcion' => 'Dispositivos médicos y terapias para enfermedades crónicas',
                'sitio_web'   => 'https://www.medtronic.com',
                'status'      => true,
            ],
            [
                'nombre'      => '3M Health Care',
                'descripcion' => 'Productos de salud, insumos médicos y soluciones de infección',
                'sitio_web'   => 'https://www.3m.com/healthcare',
                'status'      => true,
            ],
            // Genérica / Local
            [
                'nombre'      => 'Genérico',
                'descripcion' => 'Productos sin marca específica o de fabricación genérica',
                'sitio_web'   => null,
                'status'      => true,
            ],
        ];

        foreach ($marcas as $data) {
            Marca::firstOrCreate(
                [
                    'nombre'      => $data['nombre'],
                    'empresa_id'  => $empresa?->id,
                    'sucursal_id' => $sucursal?->id,
                ],
                array_merge($data, [
                    'empresa_id'  => $empresa?->id,
                    'sucursal_id' => $sucursal?->id,
                ])
            );
        }

        $this->command->info('✅ Marcas creadas: ' . count($marcas));
    }
}
