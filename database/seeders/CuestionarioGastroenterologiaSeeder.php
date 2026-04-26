<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioGastroenterologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'GASTRO')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad GASTRO no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Gastroenterología',
            'descripcion'     => 'Cuestionario previo a su consulta digestiva.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta?',                           null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Presenta dolor abdominal?',                                   'Indique la ubicación si puede.',                                                              'opcion',   ['No', 'Parte alta (epigastrio)', 'Parte baja', 'Lado derecho', 'Lado izquierdo', 'Todo el abdomen'],     true,  2],
            ['¿Cómo calificaría la intensidad del dolor?',                   '1 = Sin dolor, 10 = Dolor insoportable.',                                                     'escala',   null,                                                                                                           false, 3],
            ['¿Presenta náuseas o vómitos?',                                 null,                                                                                          'opcion',   ['No', 'Solo náuseas', 'Vómitos ocasionales', 'Vómitos frecuentes'],                                     true,  4],
            ['¿Ha notado cambios en sus deposiciones?',                      null,                                                                                          'opcion',   ['No', 'Constipación', 'Diarrea', 'Alternancia diarrea/constipación'],                                   true,  5],
            ['¿Ha visto sangre en las heces o heces negras?',               null,                                                                                          'si_no',    null,                                                                                                           true,  6],
            ['¿Presenta acidez o reflujo frecuente?',                        null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Varias veces por semana', 'Diariamente'],                                       true,  7],
            ['¿Ha perdido peso sin intentarlo?',                             null,                                                                                          'si_no',    null,                                                                                                           true,  8],
            ['¿Consume AINEs (ibuprofeno, aspirina, diclofenaco)?',         'Con qué frecuencia.',                                                                         'opcion',   ['No', 'Ocasionalmente', 'Frecuentemente', 'Diariamente'],                                               false, 9],
            ['¿Consume alcohol?',                                            null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Frecuentemente', 'Diariamente'],                                               true,  10],
            ['¿Tiene antecedentes de úlcera, gastritis o colon irritable?', null,                                                                                          'si_no',    null,                                                                                                           false, 11],
        ]);

        $this->command->info('✓ Cuestionario Gastroenterología creado.');
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
