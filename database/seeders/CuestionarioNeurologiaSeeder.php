<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioNeurologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEURO')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad NEURO no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Neurología',
            'descripcion'     => 'Cuestionario previo a su consulta neurológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta neurológica?',               null,                                                                                          'texto',    null,                                                                                                                   true,  1],
            ['¿Presenta dolores de cabeza frecuentes?',                      null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Varias veces por semana', 'Diariamente'],                                               true,  2],
            ['¿Cómo calificaría la intensidad del dolor de cabeza?',        '1 = Leve, 10 = Insoportable.',                                                                'escala',   null,                                                                                                                   false, 3],
            ['¿Ha tenido convulsiones o episodios de pérdida de conciencia?',null,                                                                                          'si_no',    null,                                                                                                                   true,  4],
            ['¿Presenta mareos o sensación de vértigo?',                     null,                                                                                          'si_no',    null,                                                                                                                   true,  5],
            ['¿Ha notado debilidad o entumecimiento en alguna parte del cuerpo?','Indique en qué zona.',                                                                    'si_no',    null,                                                                                                                   true,  6],
            ['¿Tiene dificultad para hablar o entender el lenguaje?',        null,                                                                                          'si_no',    null,                                                                                                                   true,  7],
            ['¿Ha tenido problemas de memoria o concentración?',             null,                                                                                          'opcion',   ['No', 'Leves', 'Moderados', 'Severos'],                                                                           false, 8],
            ['¿Presenta temblores en manos, brazos u otras partes?',         null,                                                                                          'si_no',    null,                                                                                                                   false, 9],
            ['¿Tiene antecedentes de ACV, epilepsia u otra enfermedad neurológica?', null,                                                                                  'si_no',    null,                                                                                                                   true,  10],
            ['¿Algún familiar tiene epilepsia, Parkinson o demencia?',       null,                                                                                          'si_no',    null,                                                                                                                   false, 11],
            ['¿Está tomando medicamentos neurológicos actualmente?',         'Antiepilépticos, antidepresivos, etc.',                                                       'texto',    null,                                                                                                                   false, 12],
        ]);

        $this->command->info('✓ Cuestionario Neurología creado.');
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
