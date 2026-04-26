<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioNeurocirugiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEUROCI')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad NEUROCI no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Neurocirugía',
            'descripcion'     => 'Cuestionario previo a su consulta neuroquirúrgica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta?',                           null,                                                                                          'texto',    null,                                                                                                   true,  1],
            ['¿Presenta dolor de columna o cuello?',                         null,                                                                                          'opcion',   ['No', 'Dolor cervical (cuello)', 'Dolor dorsal (espalda media)', 'Dolor lumbar (espalda baja)', 'Varios niveles'], true, 2],
            ['¿Cómo calificaría la intensidad del dolor?',                   '1 = Sin dolor, 10 = Insoportable.',                                                           'escala',   null,                                                                                                   false, 3],
            ['¿El dolor se irradia hacia brazos o piernas?',                 'Indique hacia dónde.',                                                                        'si_no',    null,                                                                                                   true,  4],
            ['¿Siente hormigueo o entumecimiento en extremidades?',          null,                                                                                          'si_no',    null,                                                                                                   true,  5],
            ['¿Ha notado debilidad en brazos o piernas?',                    null,                                                                                          'si_no',    null,                                                                                                   true,  6],
            ['¿Tiene dificultad para caminar o mantener el equilibrio?',     null,                                                                                          'si_no',    null,                                                                                                   true,  7],
            ['¿Ha tenido dolores de cabeza intensos o repentinos?',          null,                                                                                          'si_no',    null,                                                                                                   true,  8],
            ['¿Ha tenido alguna cirugía de columna o cerebro previamente?',  'Indique cuál y cuándo.',                                                                      'si_no',    null,                                                                                                   true,  9],
            ['¿Ha sufrido algún traumatismo craneal o de columna?',          null,                                                                                          'si_no',    null,                                                                                                   false, 10],
            ['¿Toma anticoagulantes o antiagregantes?',                      'Warfarina, aspirina, clopidogrel, etc.',                                                      'si_no',    null,                                                                                                   true,  11],
            ['¿Tiene estudios de imagen recientes?',                         'Resonancia, tomografía, radiografía.',                                                        'opcion',   ['No', 'Sí — los traeré a la consulta', 'Sí — están en digital'],                                    false, 12],
        ]);

        $this->command->info('✓ Cuestionario Neurocirugía creado.');
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
