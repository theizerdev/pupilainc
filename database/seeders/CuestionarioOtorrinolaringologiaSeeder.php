<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioOtorrinolaringologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'ORL')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad ORL no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Otorrinolaringología',
            'descripcion'     => 'Cuestionario previo a su consulta de oído, nariz y garganta.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta?',                           null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Presenta alguno de estos síntomas en los oídos?',             null,                                                                                          'multiple', ['Dolor de oído', 'Pérdida de audición', 'Zumbido (tinnitus)', 'Secreción', 'Sensación de taponamiento', 'Ninguno'], true, 2],
            ['¿Presenta alguno de estos síntomas nasales?',                  null,                                                                                          'multiple', ['Congestión nasal', 'Secreción nasal', 'Sangrado por la nariz', 'Pérdida del olfato', 'Ninguno'],         true,  3],
            ['¿Presenta alguno de estos síntomas en la garganta?',           null,                                                                                          'multiple', ['Dolor de garganta', 'Dificultad para tragar', 'Ronquera', 'Tos persistente', 'Ninguno'],                 true,  4],
            ['¿Tiene mareos o sensación de que todo gira?',                  null,                                                                                          'si_no',    null,                                                                                                           true,  5],
            ['¿Ronca al dormir o le han dicho que deja de respirar?',        null,                                                                                          'opcion',   ['No', 'Ronco levemente', 'Ronco fuerte', 'Me han dicho que dejo de respirar'],                           false, 6],
            ['¿Ha tenido infecciones de oído o garganta frecuentes?',        'Más de 3 veces al año.',                                                                      'si_no',    null,                                                                                                           false, 7],
            ['¿Tiene alergias respiratorias o rinitis?',                     null,                                                                                          'si_no',    null,                                                                                                           false, 8],
            ['¿Ha tenido alguna cirugía de oído, nariz o garganta?',         'Amigdalectomía, septoplastia, etc.',                                                          'si_no',    null,                                                                                                           false, 9],
            ['¿Está expuesto a ruidos fuertes en su trabajo o actividades?', null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Frecuentemente', 'Diariamente'],                                               false, 10],
        ]);

        $this->command->info('✓ Cuestionario Otorrinolaringología creado.');
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
