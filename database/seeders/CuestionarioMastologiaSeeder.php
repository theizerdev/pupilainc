<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioMastologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MASTOL')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad MASTOL no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Mastología',
            'descripcion'     => 'Cuestionario previo a su consulta mastológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta mastológica?',               null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Ha notado algún nódulo o masa en la mama?',                   null,                                                                                          'opcion',   ['No', 'Mama derecha', 'Mama izquierda', 'Ambas mamas'],                                                  true,  2],
            ['¿Presenta dolor en las mamas?',                                null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Relacionado con el ciclo', 'Constante'],                                       true,  3],
            ['¿Ha notado secreción por el pezón?',                           null,                                                                                          'opcion',   ['No', 'Sí — transparente', 'Sí — lechosa', 'Sí — con sangre'],                                         true,  4],
            ['¿Ha notado cambios en la piel de la mama?',                    'Enrojecimiento, hoyuelos, piel de naranja.',                                                  'si_no',    null,                                                                                                           true,  5],
            ['¿Cuándo fue su última mamografía?',                            null,                                                                                          'opcion',   ['Nunca', 'Hace menos de 1 año', 'Hace 1-2 años', 'Hace más de 2 años'],                                 false, 6],
            ['¿Tiene antecedentes familiares de cáncer de mama?',            'Madre, hermana, hija, abuela.',                                                               'si_no',    null,                                                                                                           true,  7],
            ['¿Ha tenido cáncer de mama u otra patología mamaria previamente?', null,                                                                                       'si_no',    null,                                                                                                           true,  8],
            ['¿Usa o ha usado anticonceptivos hormonales o terapia hormonal?', null,                                                                                        'opcion',   ['No', 'Actualmente', 'Anteriormente', 'Nunca'],                                                         false, 9],
            ['¿Ha dado lactancia materna?',                                  null,                                                                                          'opcion',   ['No', 'Sí — menos de 6 meses', 'Sí — 6 a 12 meses', 'Sí — más de 12 meses'],                           false, 10],
        ]);

        $this->command->info('✓ Cuestionario Mastología creado.');
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
