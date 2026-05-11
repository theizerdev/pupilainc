<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class EspecialidadesMedicasSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        if (!$empresa || !$sucursal) {
            $this->command->error('Se requiere al menos una empresa y una sucursal.');
            return;
        }

        $especialidades = [
            [
                'nombre'             => 'Medicina General',
                'codigo'             => 'MED-GEN',
                'color'              => '#4CAF50',
                'icono'              => 'fa-stethoscope',
                'costo_consulta'     => 30.00,
                'duracion_consulta'  => 20,
                'descripcion'        => 'Atención médica primaria y general.',
            ],
            [
                'nombre'             => 'Medicina Interna',
                'codigo'             => 'MED-INT',
                'color'              => '#2196F3',
                'icono'              => 'fa-user-md',
                'costo_consulta'     => 50.00,
                'duracion_consulta'  => 30,
                'descripcion'        => 'Diagnóstico y tratamiento de enfermedades del adulto.',
            ],
            [
                'nombre'             => 'Cardiología',
                'codigo'             => 'CARDIO',
                'color'              => '#F44336',
                'icono'              => 'fa-heartbeat',
                'costo_consulta'     => 60.00,
                'duracion_consulta'  => 30,
                'descripcion'        => 'Diagnóstico y tratamiento de enfermedades del corazón.',
            ],
            [
                'nombre'             => 'Ginecología',
                'codigo'             => 'GINECO',
                'color'              => '#E91E63',
                'icono'              => 'fa-venus',
                'costo_consulta'     => 55.00,
                'duracion_consulta'  => 30,
                'descripcion'        => 'Salud del sistema reproductor femenino.',
            ],
            [
                'nombre'             => 'Gastroenterología',
                'codigo'             => 'GASTRO',
                'color'              => '#FF9800',
                'icono'              => 'fa-procedures',
                'costo_consulta'     => 55.00,
                'duracion_consulta'  => 30,
                'descripcion'        => 'Diagnóstico y tratamiento del sistema digestivo.',
            ],
            [
                'nombre'             => 'Neurología',
                'codigo'             => 'NEURO',
                'color'              => '#9C27B0',
                'icono'              => 'fa-brain',
                'costo_consulta'     => 65.00,
                'duracion_consulta'  => 40,
                'descripcion'        => 'Diagnóstico y tratamiento del sistema nervioso.',
            ],
            [
                'nombre'             => 'Neurocirugía',
                'codigo'             => 'NEUROCI',
                'color'              => '#673AB7',
                'icono'              => 'fa-brain',
                'costo_consulta'     => 80.00,
                'duracion_consulta'  => 45,
                'descripcion'        => 'Cirugía del sistema nervioso central y periférico.',
            ],
            [
                'nombre'             => 'Pediatría',
                'codigo'             => 'PEDIAT',
                'color'              => '#00BCD4',
                'icono'              => 'fa-baby',
                'costo_consulta'     => 40.00,
                'duracion_consulta'  => 25,
                'descripcion'        => 'Atención médica de niños y adolescentes.',
            ],
            [
                'nombre'             => 'Nefrología',
                'codigo'             => 'NEFRO',
                'color'              => '#009688',
                'icono'              => 'fa-kidneys',
                'costo_consulta'     => 60.00,
                'duracion_consulta'  => 30,
                'descripcion'        => 'Diagnóstico y tratamiento de enfermedades renales.',
            ],
            [
                'nombre'             => 'Otorrinolaringología',
                'codigo'             => 'ORL',
                'color'              => '#795548',
                'icono'              => 'fa-ear',
                'costo_consulta'     => 50.00,
                'duracion_consulta'  => 25,
                'descripcion'        => 'Diagnóstico y tratamiento de oído, nariz y garganta.',
            ],
            [
                'nombre'             => 'Ortopedia Pediátrica',
                'codigo'             => 'ORTO-PED',
                'color'              => '#FF5722',
                'icono'              => 'fa-bone',
                'costo_consulta'     => 65.00,
                'duracion_consulta'  => 35,
                'descripcion'        => 'Diagnóstico y tratamiento de trastornos musculoesqueléticos en niños.',
            ],

        ];

        $creadas = 0;
        foreach ($especialidades as $data) {
            // Evitar duplicados por código
            if (Especialidad::where('codigo', $data['codigo'])->where('empresa_id', $empresa->id)->exists()) {
                $this->command->warn("  Ya existe: {$data['nombre']} — omitida.");
                continue;
            }

            Especialidad::create(array_merge($data, [
                'empresa_id'          => $empresa->id,
                'sucursal_id'         => $sucursal->id,
                'requiere_cita_previa' => true,
                'status'              => true,
            ]));

            $creadas++;
            $this->command->info("  ✓ {$data['nombre']}");
        }

        $this->command->info("Especialidades creadas: {$creadas}");
    }
}
