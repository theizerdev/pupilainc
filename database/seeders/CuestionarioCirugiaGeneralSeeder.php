<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioCirugiaGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CIR-GEN')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad CIR-GEN no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Cirugía General',
            'descripcion'     => 'Cuestionario previo a su consulta quirúrgica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta quirúrgica?',                null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Presenta dolor abdominal?',                                   'Indique la ubicación.',                                                                       'opcion',   ['No', 'Parte alta', 'Parte baja', 'Lado derecho', 'Lado izquierdo', 'Todo el abdomen'],                 true,  2],
            ['¿Cómo calificaría la intensidad del dolor?',                   '1 = Sin dolor, 10 = Insoportable.',                                                           'escala',   null,                                                                                                           false, 3],
            ['¿Ha tenido cirugías abdominales previas?',                     'Indique cuáles.',                                                                             'si_no',    null,                                                                                                           true,  4],
            ['¿Tiene fiebre o escalofríos actualmente?',                     null,                                                                                          'si_no',    null,                                                                                                           true,  5],
            ['¿Ha notado alguna masa o bulto en el abdomen?',                null,                                                                                          'si_no',    null,                                                                                                           true,  6],
            ['¿Tiene náuseas o vómitos?',                                    null,                                                                                          'opcion',   ['No', 'Náuseas sin vómitos', 'Vómitos ocasionales', 'Vómitos frecuentes'],                              true,  7],
            ['¿Toma anticoagulantes o aspirina?',                            'Warfarina, clopidogrel, aspirina, etc.',                                                      'si_no',    null,                                                                                                           true,  8],
            ['¿Tiene diabetes, hipertensión u otra enfermedad crónica?',     null,                                                                                          'texto',    null,                                                                                                           false, 9],
            ['¿Tiene alergia a algún medicamento o anestesia?',              null,                                                                                          'si_no',    null,                                                                                                           true,  10],
        ]);

        $this->command->info('✓ Cuestionario Cirugía General creado.');
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
