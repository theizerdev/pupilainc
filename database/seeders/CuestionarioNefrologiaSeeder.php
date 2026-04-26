<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioNefrologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEFRO')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad NEFRO no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Nefrología',
            'descripcion'     => 'Cuestionario previo a su consulta nefrológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta nefrológica?',               null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Ha notado cambios en la cantidad de orina?',                  null,                                                                                          'opcion',   ['No', 'Orina menos de lo normal', 'Orina más de lo normal', 'No orina'],                                 true,  2],
            ['¿Ha visto sangre en la orina?',                                null,                                                                                          'si_no',    null,                                                                                                           true,  3],
            ['¿Siente ardor o dolor al orinar?',                             null,                                                                                          'si_no',    null,                                                                                                           true,  4],
            ['¿Tiene hinchazón en pies, piernas o cara?',                    null,                                                                                          'opcion',   ['No', 'Leve', 'Moderada', 'Severa'],                                                                   true,  5],
            ['¿Tiene presión arterial alta diagnosticada?',                  null,                                                                                          'opcion',   ['No', 'Sí — controlada', 'Sí — no controlada', 'No sé'],                                               true,  6],
            ['¿Tiene diabetes?',                                             null,                                                                                          'opcion',   ['No', 'Tipo 1', 'Tipo 2', 'No sé'],                                                                    true,  7],
            ['¿Le han dicho que tiene problemas en los riñones?',            'Insuficiencia renal, quistes, piedras, etc.',                                                 'si_no',    null,                                                                                                           true,  8],
            ['¿Está en diálisis o ha estado en diálisis?',                   null,                                                                                          'opcion',   ['No', 'Sí — hemodiálisis', 'Sí — diálisis peritoneal', 'Anteriormente'],                               true,  9],
            ['¿Toma medicamentos para los riñones o la presión?',            'Indique cuáles si los recuerda.',                                                             'texto',    null,                                                                                                           false, 10],
            ['¿Consume AINEs frecuentemente?',                               'Ibuprofeno, naproxeno, diclofenaco, etc.',                                                    'opcion',   ['No', 'Ocasionalmente', 'Frecuentemente'],                                                              false, 11],
        ]);

        $this->command->info('✓ Cuestionario Nefrología creado.');
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
