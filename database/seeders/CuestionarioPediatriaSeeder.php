<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioPediatriaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'PEDIAT')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad PEDIAT no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Pediatría',
            'descripcion'     => 'Cuestionario previo a la consulta pediátrica. Por favor responda en nombre del niño/a.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de la consulta del niño/a hoy?',            null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Desde cuándo presenta estos síntomas?',                       null,                                                                                          'texto',    null,                                                                                                           true,  2],
            ['¿El niño/a tiene fiebre actualmente?',                         null,                                                                                          'opcion',   ['No', 'Sí — menos de 38°C', 'Sí — entre 38 y 39°C', 'Sí — más de 39°C'],                               true,  3],
            ['¿Presenta vómitos o diarrea?',                                 null,                                                                                          'multiple', ['No', 'Vómitos', 'Diarrea', 'Ambos'],                                                                   true,  4],
            ['¿El niño/a tiene su esquema de vacunación al día?',            null,                                                                                          'opcion',   ['Sí, completo', 'Incompleto', 'No sé', 'No vacunado'],                                                   true,  5],
            ['¿Tiene alguna alergia conocida?',                              'Medicamentos, alimentos, etc.',                                                               'si_no',    null,                                                                                                           true,  6],
            ['¿Está tomando algún medicamento actualmente?',                 null,                                                                                          'texto',    null,                                                                                                           false, 7],
            ['¿Ha sido hospitalizado anteriormente?',                        'Indique el motivo si recuerda.',                                                              'si_no',    null,                                                                                                           false, 8],
            ['¿Tiene alguna enfermedad crónica diagnosticada?',              'Asma, diabetes, epilepsia, etc.',                                                             'texto',    null,                                                                                                           false, 9],
            ['¿Cómo describiría el estado general del niño/a hoy?',         null,                                                                                          'opcion',   ['Activo y bien', 'Algo decaído', 'Muy decaído', 'Irritable', 'Somnoliento'],                             true,  10],
        ]);

        $this->command->info('✓ Cuestionario Pediatría creado.');
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
