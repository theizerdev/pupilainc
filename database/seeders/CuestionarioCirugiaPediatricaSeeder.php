<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioCirugiaPediatricaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CIR-PED')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad CIR-PED no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Cirugía Pediátrica',
            'descripcion'     => 'Cuestionario previo a la consulta quirúrgica pediátrica. Por favor responda en nombre del niño/a.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de la consulta quirúrgica del niño/a?',     null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Desde cuándo presenta estos síntomas?',                       null,                                                                                          'texto',    null,                                                                                                           true,  2],
            ['¿El niño/a presenta dolor?',                                   'Indique la ubicación.',                                                                       'opcion',   ['No', 'Abdomen', 'Ingle', 'Escroto/genitales', 'Otro'],                                                 true,  3],
            ['¿Cómo calificaría el dolor del niño/a?',                       '1 = Sin dolor, 10 = Llanto intenso.',                                                         'escala',   null,                                                                                                           false, 4],
            ['¿Tiene fiebre actualmente?',                                   null,                                                                                          'opcion',   ['No', 'Sí — menos de 38°C', 'Sí — entre 38 y 39°C', 'Sí — más de 39°C'],                               true,  5],
            ['¿Ha tenido cirugías previas?',                                 'Indique cuáles y a qué edad.',                                                                'si_no',    null,                                                                                                           true,  6],
            ['¿Tiene alguna alergia conocida?',                              'Medicamentos, anestesia, látex, etc.',                                                        'si_no',    null,                                                                                                           true,  7],
            ['¿Está tomando algún medicamento actualmente?',                 null,                                                                                          'texto',    null,                                                                                                           false, 8],
            ['¿Tiene alguna enfermedad crónica o condición especial?',       'Cardiopatía, coagulopatía, etc.',                                                             'texto',    null,                                                                                                           false, 9],
            ['¿Ha notado alguna masa, bulto o hernia en el niño/a?',         'Indique la ubicación.',                                                                       'si_no',    null,                                                                                                           true,  10],
        ]);

        $this->command->info('✓ Cuestionario Cirugía Pediátrica creado.');
    }

    private function preguntas(int $cid, array $items): void
    {
        foreach ($items as $i) {
            Pregunta::create([
                'cuestionario_id' => $cid,
                'titulo'          => $i[0],
                'descripcion'     => $i[1],
                'tipo'            => $i[2],
                'opciones'        => $i[3],
                'obligatorio'     => $i[4],
                'orden'           => $i[5],
                'activo'          => true,
            ]);
        }
    }
}
