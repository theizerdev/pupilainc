<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioMedicinaGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MED-GEN')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad MED-GEN no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Medicina General',
            'descripcion'     => 'Cuestionario previo a su consulta de medicina general.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo principal de su consulta hoy?',             'Describa sus síntomas o razón de visita.',                                    'texto',    null,                                                                                   true,  1],
            ['¿Desde cuándo presenta estos síntomas?',                       null,                                                                          'texto',    null,                                                                                   true,  2],
            ['¿Ha tenido fiebre en los últimos días?',                       null,                                                                          'si_no',    null,                                                                                   true,  3],
            ['¿Tiene alguna enfermedad crónica diagnosticada?',              null,                                                                          'multiple', ['Diabetes', 'Hipertensión', 'Asma', 'Hipotiroidismo', 'Ninguna'],                      false, 4],
            ['¿Está tomando algún medicamento actualmente?',                 'Incluya medicamentos recetados y de venta libre.',                            'texto',    null,                                                                                   false, 5],
            ['¿Tiene alergia a algún medicamento?',                          'Si es sí, indique cuál.',                                                     'si_no',    null,                                                                                   true,  6],
            ['¿Ha sido hospitalizado en el último año?',                     null,                                                                          'si_no',    null,                                                                                   false, 7],
            ['¿Fuma actualmente?',                                           null,                                                                          'opcion',   ['No', 'Ocasionalmente', 'Diariamente', 'Ex-fumador'],                                  true,  8],
            ['¿Consume bebidas alcohólicas?',                                null,                                                                          'opcion',   ['No', 'Ocasionalmente', 'Semanalmente', 'Diariamente'],                                true,  9],
            ['¿Cómo calificaría su nivel de malestar general hoy?',         '1 = Sin malestar, 10 = Malestar muy severo.',                                 'escala',   null,                                                                                   false, 10],
        ]);

        $this->command->info('✓ Cuestionario Medicina General creado.');
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
