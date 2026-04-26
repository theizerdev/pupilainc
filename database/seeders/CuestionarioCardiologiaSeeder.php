<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioCardiologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CARDIO')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad CARDIO no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Cardiología',
            'descripcion'     => 'Cuestionario previo a su consulta cardiológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta cardiológica?',              null,                                                                                          'texto',    null,                                                                                                                   true,  1],
            ['¿Presenta dolor o presión en el pecho?',                       'Indique si es frecuente, ocasional o en reposo.',                                             'si_no',    null,                                                                                                                   true,  2],
            ['¿Siente falta de aire (disnea)?',                              null,                                                                                          'opcion',   ['No', 'Al hacer esfuerzo', 'Al caminar', 'En reposo', 'Al acostarse'],                                               true,  3],
            ['¿Ha tenido palpitaciones o latidos irregulares?',              null,                                                                                          'si_no',    null,                                                                                                                   true,  4],
            ['¿Ha presentado desmayos o pérdida de conciencia?',             null,                                                                                          'si_no',    null,                                                                                                                   true,  5],
            ['¿Tiene hinchazón en los pies o piernas?',                      null,                                                                                          'si_no',    null,                                                                                                                   false, 6],
            ['¿Tiene alguno de estos factores de riesgo cardiovascular?',    null,                                                                                          'multiple', ['Hipertensión arterial', 'Diabetes', 'Colesterol alto', 'Tabaquismo', 'Obesidad', 'Sedentarismo', 'Antecedente familiar de infarto', 'Ninguno'], true, 7],
            ['¿Ha tenido algún evento cardíaco previo?',                     'Infarto, angina, cirugía cardíaca, stent, etc.',                                              'si_no',    null,                                                                                                                   true,  8],
            ['¿Está tomando medicamentos para el corazón o la presión?',     'Indique cuáles si los recuerda.',                                                             'texto',    null,                                                                                                                   false, 9],
            ['¿Fuma actualmente?',                                           null,                                                                                          'opcion',   ['No', 'Ex-fumador (dejó hace menos de 1 año)', 'Ex-fumador (más de 1 año)', 'Fumador activo'],                       true,  10],
            ['¿Cómo calificaría su nivel de esfuerzo físico habitual?',     '1 = Sedentario total, 10 = Muy activo.',                                                      'escala',   null,                                                                                                                   false, 11],
        ]);

        $this->command->info('✓ Cuestionario Cardiología creado.');
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
