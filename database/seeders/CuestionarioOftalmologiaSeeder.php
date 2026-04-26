<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioOftalmologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'OFTAL')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad OFTAL no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Oftalmología',
            'descripcion'     => 'Cuestionario previo a su consulta oftalmológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta oftalmológica?',             null,                                                                                          'texto',    null,                                                                                                           true,  1],
            ['¿Presenta alguno de estos síntomas visuales?',                 null,                                                                                          'multiple', ['Visión borrosa', 'Dolor ocular', 'Ojo rojo', 'Secreción', 'Visión de destellos', 'Manchas flotantes', 'Ninguno'], true, 2],
            ['¿El problema es en uno o ambos ojos?',                         null,                                                                                          'opcion',   ['Ojo derecho', 'Ojo izquierdo', 'Ambos ojos', 'No estoy seguro'],                                       true,  3],
            ['¿Usa lentes o gafas actualmente?',                             null,                                                                                          'opcion',   ['No', 'Lentes oftálmicos', 'Lentes de contacto', 'Ambos'],                                              true,  4],
            ['¿Cuándo fue su última revisión oftalmológica?',                null,                                                                                          'opcion',   ['Nunca', 'Hace menos de 1 año', 'Hace 1-2 años', 'Hace más de 2 años'],                                 false, 5],
            ['¿Tiene diabetes?',                                             null,                                                                                          'opcion',   ['No', 'Tipo 1', 'Tipo 2', 'No sé'],                                                                    true,  6],
            ['¿Tiene presión arterial alta?',                                null,                                                                                          'opcion',   ['No', 'Sí — controlada', 'Sí — no controlada'],                                                        true,  7],
            ['¿Ha tenido alguna cirugía ocular previa?',                     'LASIK, cataratas, glaucoma, etc.',                                                            'si_no',    null,                                                                                                           false, 8],
            ['¿Tiene antecedentes familiares de glaucoma o degeneración macular?', null,                                                                                    'si_no',    null,                                                                                                           false, 9],
            ['¿Toma algún medicamento que pueda afectar la visión?',         'Corticoides, antipalúdicos, etc.',                                                            'si_no',    null,                                                                                                           false, 10],
        ]);

        $this->command->info('✓ Cuestionario Oftalmología creado.');
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
