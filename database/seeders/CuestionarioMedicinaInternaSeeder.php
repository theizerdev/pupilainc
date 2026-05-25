<?php

namespace Database\Seeders;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class CuestionarioMedicinaInternaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MED-INT')->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Especialidad MED-INT no encontrada.');
            return;
        }

        Cuestionario::where('empresa_id', $empresa->id)
            ->where('especialidad_id', $especialidad->id)
            ->delete();

        $c = Cuestionario::create([
            'titulo'          => 'Preconsulta — Medicina Interna',
            'descripcion'     => 'Cuestionario previo a su consulta de medicina interna.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        $this->preguntas($c->id, [
            ['¿Cuál es el motivo de su consulta?',                           null,                                                                                          'texto',    null,                                                                                                   true,  1],
            ['¿Desde cuándo presenta estos síntomas?',                       null,                                                                                          'texto',    null,                                                                                                   true,  2],
            ['¿Tiene alguna de estas enfermedades crónicas?',                null,                                                                                          'multiple', ['Diabetes Mellitus', 'Hipertensión Arterial', 'Dislipidemia', 'Hipotiroidismo', 'EPOC', 'Asma', 'Insuficiencia Renal', 'Cardiopatía', 'Ninguna'], true, 3],
            ['¿Ha sido hospitalizado en los últimos 6 meses?',               'Si es sí, indique el motivo.',                                                                'si_no',    null,                                                                                                   false, 4],
            ['¿Tiene antecedentes familiares de enfermedades importantes?',  'Diabetes, hipertensión, cáncer, enfermedades cardíacas, etc.',                                'texto',    null,                                                                                                   false, 5],
            ['¿Está tomando algún medicamento actualmente?',                 'Incluya dosis y frecuencia si los recuerda.',                                                  'texto',    null,                                                                                                   false, 6],
            ['¿Tiene alergia a algún medicamento?',                          null,                                                                                          'si_no',    null,                                                                                                   true,  7],
            ['¿Presenta alguno de estos síntomas actualmente?',              null,                                                                                          'multiple', ['Fiebre', 'Pérdida de peso', 'Fatiga extrema', 'Sudoración nocturna', 'Ninguno'],                     false, 8],
            ['¿Fuma o ha fumado?',                                           null,                                                                                          'opcion',   ['No', 'Ex-fumador', 'Fumador activo'],                                                               true,  9],
            ['¿Consume alcohol?',                                            null,                                                                                          'opcion',   ['No', 'Ocasionalmente', 'Frecuentemente'],                                                            true,  10],
            ['¿Cómo calificaría su estado de salud general?',               '1 = Muy malo, 10 = Excelente.',                                                               'escala',   null,                                                                                                   false, 11],
        ]);

        $this->command->info('✓ Cuestionario Medicina Interna creado.');
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
