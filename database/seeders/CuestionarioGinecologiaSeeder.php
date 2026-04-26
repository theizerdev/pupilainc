<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioGinecologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'GINECO')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad GINECO no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Ginecología',
            'descripcion'     => 'Cuestionario previo a su consulta ginecológica.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta ginecológica?',              null,                                                                                          'texto',    null,                                                                                                   true,  1],
            ['¿Cuándo fue su última menstruación (FUM)?',                    'Indique la fecha aproximada.',                                                                'texto',    null,                                                                                                   true,  2],
            ['¿Su ciclo menstrual es regular?',                              null,                                                                                          'opcion',   ['Sí, regular', 'Irregular', 'Ausente (menopausia)', 'Ausente (otro motivo)'],                          true,  3],
            ['¿Presenta dolor durante la menstruación?',                     null,                                                                                          'opcion',   ['No', 'Leve', 'Moderado', 'Severo (incapacitante)'],                                                 true,  4],
            ['¿Usa algún método anticonceptivo actualmente?',                null,                                                                                          'opcion',   ['No', 'Anticonceptivos orales', 'DIU', 'Implante', 'Inyectable', 'Preservativo', 'Otro'],            true,  5],
            ['¿Cuántos embarazos ha tenido?',                                'Incluya partos, cesáreas y abortos.',                                                         'texto',    null,                                                                                                   false, 6],
            ['¿Cuándo fue su último Papanicolaou?',                          null,                                                                                          'opcion',   ['Nunca', 'Hace menos de 1 año', 'Hace 1-3 años', 'Hace más de 3 años'],                              false, 7],
            ['¿Ha tenido alguna cirugía ginecológica previa?',               'Cesárea, miomectomía, histerectomía, etc.',                                                   'si_no',    null,                                                                                                   false, 8],
            ['¿Tiene antecedentes familiares de cáncer de mama o útero?',   null,                                                                                          'si_no',    null,                                                                                                   false, 9],
            ['¿Presenta alguno de estos síntomas actualmente?',              null,                                                                                          'multiple', ['Sangrado fuera del período', 'Flujo vaginal anormal', 'Dolor pélvico', 'Picazón o ardor', 'Ninguno'], true, 10],
        ]);

        $this->command->info('✓ Cuestionario Ginecología creado.');
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
