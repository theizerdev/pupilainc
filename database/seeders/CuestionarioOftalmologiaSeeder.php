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
            'titulo'          => 'Formulario A — Paciente de Primera Vez (Oftalmología)',
            'descripcion'     => 'Cuestionario completo para pacientes de primera vez en oftalmología.',
            'tipo'            => 'preconsulta',
            'activo'          => true,
            'empresa_id'      => $empresa->id,
            'especialidad_id' => $especialidad->id,
        ]);

        // NOTA: Los campos PRE-LLENADO CRM se generan automáticamente desde el componente Livewire
        // No se crean como preguntas en la base de datos

        $this->preguntas($c->id, [
            // SECCIÓN 2: MOTIVO Y SÍNTOMAS
            ['Motivo principal de consulta', null, 'texto', null, true, 1],
            ['Síntomas actuales', 'Seleccione todos los que apliquen', 'multiple', ['Visión borrosa', 'Dolor ocular', 'Ojo rojo', 'Sensación de cuerpo extraño', 'Secreción', 'Lagrimeo excesivo', 'Visión de destellos', 'Manchas flotantes', 'Otro'], true, 2],
            ['Tiempo de evolución del problema', null, 'tiempo_evolucion', null, true, 3],

            // SECCIÓN 3: HISTORIAL OCULAR
            ['¿Usa lentes o anteojos actualmente?', null, 'si_no', null, true, 4],
            ['¿Ha tenido cirugías oculares previas?', 'LASIK, cataratas, glaucoma, etc.', 'si_no_detalle', null, true, 5],

            // SECCIÓN 4: ANTECEDENTES MÉDICOS
            ['Enfermedades sistémicas (diabetes, HTA, otras)', 'Si Sí, especifique cuáles', 'si_no_detalle', null, true, 6],
            ['Cirugías en otras partes del cuerpo', 'Si Sí, indique cuáles y fecha aproximada', 'si_no_detalle', null, true, 7],
            ['Alergias conocidas', 'Medicamentos, alimentos, etc. Si Sí, especifique', 'si_no_detalle', null, true, 8],

            // SECCIÓN 5: ANTECEDENTES FAMILIARES OFTALMOLÓGICOS
            ['Familiar con queratocono', null, 'si_no_no_se', null, false, 9],
            ['Familiar con degeneración macular', null, 'si_no_no_se', null, false, 10],
            ['Familiar con glaucoma', null, 'si_no_no_se', null, false, 11],
            ['Familiar con estrabismo o ambliopía', null, 'si_no_no_se', null, false, 12],
            ['Familiar con cataratas antes de 60 años', null, 'si_no_no_se', null, false, 13],
            ['Familiar con retinopatía diabética', null, 'si_no_no_se', null, false, 14],
            ['Familiar con desprendimiento de retina', null, 'si_no_no_se', null, false, 15],
            ['Enfermedades importantes en familia', 'Diabetes, HTA, enfermedades autoinmunes u otras. Si Sí, especifique', 'si_no_detalle', null, false, 16],

            // SECCIÓN 6: LOGÍSTICA
            ['¿Viene con acompañante?', null, 'si_no', null, false, 17],
            ['¿Requiere factura?', 'Si Sí, se solicitará RFC y razón social', 'si_no_factura', null, false, 18],
        ]);

        $this->command->info('✓ Cuestionario Oftalmología actualizado con ' . count($this->getPreguntas()) . ' preguntas según Formulario A.');
    }

    private function getPreguntas(): array
    {
        return [
            // SECCIÓN 2: MOTIVO Y SÍNTOMAS
            ['Motivo principal de consulta', null, 'texto', null, true, 1],
            ['Síntomas actuales', 'Seleccione todos los que apliquen', 'multiple', ['Visión borrosa', 'Dolor ocular', 'Ojo rojo', 'Sensación de cuerpo extraño', 'Secreción', 'Lagrimeo excesivo', 'Visión de destellos', 'Manchas flotantes', 'Otro'], true, 2],
            ['Tiempo de evolución del problema', null, 'tiempo_evolucion', null, true, 3],

            // SECCIÓN 3: HISTORIAL OCULAR
            ['¿Usa lentes o anteojos actualmente?', null, 'si_no', null, true, 4],
            ['¿Ha tenido cirugías oculares previas?', 'LASIK, cataratas, glaucoma, etc.', 'si_no_detalle', null, true, 5],

            // SECCIÓN 4: ANTECEDENTES MÉDICOS
            ['Enfermedades sistémicas (diabetes, HTA, otras)', 'Si Sí, especifique cuáles', 'si_no_detalle', null, true, 6],
            ['Cirugías en otras partes del cuerpo', 'Si Sí, indique cuáles y fecha aproximada', 'si_no_detalle', null, true, 7],
            ['Alergias conocidas', 'Medicamentos, alimentos, etc. Si Sí, especifique', 'si_no_detalle', null, true, 8],

            // SECCIÓN 5: ANTECEDENTES FAMILIARES OFTALMOLÓGICOS
            ['Familiar con queratocono', null, 'si_no_no_se', null, false, 9],
            ['Familiar con degeneración macular', null, 'si_no_no_se', null, false, 10],
            ['Familiar con glaucoma', null, 'si_no_no_se', null, false, 11],
            ['Familiar con estrabismo o ambliopía', null, 'si_no_no_se', null, false, 12],
            ['Familiar con cataratas antes de 60 años', null, 'si_no_no_se', null, false, 13],
            ['Familiar con retinopatía diabética', null, 'si_no_no_se', null, false, 14],
            ['Familiar con desprendimiento de retina', null, 'si_no_no_se', null, false, 15],
            ['Enfermedades importantes en familia', 'Diabetes, HTA, enfermedades autoinmunes u otras. Si Sí, especifique', 'si_no_detalle', null, false, 16],

            // SECCIÓN 6: LOGÍSTICA
            ['¿Viene con acompañante?', null, 'si_no', null, false, 17],
            ['¿Requiere factura?', 'Si Sí, se solicitará RFC y razón social', 'si_no_factura', null, false, 18],
        ];
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
