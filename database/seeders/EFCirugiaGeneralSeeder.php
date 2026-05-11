<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFCirugiaGeneralSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CIR-GEN')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Cirugía General — plantilla no encontrada.'); return; }

        // ── EN CONSULTORIO ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación Quirúrgica');

        $s = $this->seccion($plantilla, $ef, 'Examen Físico', 'fa-stethoscope', '#FF5722', 1);
        $this->campos($s->id, [
            ['aspecto_general', 'Aspecto General', 'select',
                ['Buen estado general', 'Regular', 'Mal estado general', 'Con dolor agudo'],
                false, 'Buen estado general', null, null, 6],
            ['abdomen_inspeccion', 'Abdomen — Inspección', 'textarea', null, false, null, null, null, 12],
            ['ruidos_intestinales', 'Ruidos Intestinales', 'select',
                ['Presentes normales', 'Aumentados', 'Disminuidos', 'Ausentes'],
                false, 'Presentes normales', null, null, 6],
            ['palpacion', 'Palpación', 'textarea', null, false, null, null, null, 12],
            ['murphy', 'Signo de Murphy', 'select', ['Negativo', 'Positivo'], false, 'Negativo', null, null, 4],
            ['mcburney', 'Punto de McBurney', 'select', ['Negativo', 'Positivo'], false, 'Negativo', null, null, 4],
            ['blumberg', 'Signo de Blumberg', 'select', ['Negativo', 'Positivo'], false, 'Negativo', null, null, 4],
            ['hernias', 'Hernias', 'textarea', null, false, null, null, null, 12],
            ['tacto_rectal', 'Tacto Rectal', 'textarea', null, false, null, null, null, 12],
            ['observaciones_examen', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $s2 = $this->seccion($plantilla, $ef, 'Evaluación Quirúrgica', 'fa-procedures', '#D32F2F', 2);
        $this->campos($s2->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true, null, null, null, 12],
            ['indicacion_quirurgica', 'Indicación Quirúrgica', 'select',
                ['Electiva', 'Urgente', 'Emergencia', 'No quirúrgico'],
                false, 'Electiva', null, null, 6],
            ['procedimiento_propuesto', 'Procedimiento Propuesto', 'textarea', null, true, null, null, null, 12],
            ['riesgo_asa', 'Riesgo Quirúrgico (ASA)', 'select',
                ['ASA I - Paciente sano', 'ASA II - Enfermedad leve', 'ASA III - Enfermedad severa',
                 'ASA IV - Amenaza vida', 'ASA V - Moribundo'],
                false, 'ASA I - Paciente sano', null, null, 6],
            ['plan_preoperatorio', 'Plan Preoperatorio', 'textarea', null, false, null, null, null, 12],
            ['proxima_cita', 'Próxima Cita', 'date', null, false, null, null, null, 6],
            ['observaciones_plan', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Quirúrgico');

        $s3 = $this->seccion($plantilla, $ef2, 'Evaluación Inicial', 'fa-user-md', '#FF9800', 1);
        $this->campos($s3->id, [
            ['dolor', 'Dolor (EVA 0-10)', 'number', null, false, '0', '0', '10', 4],
            ['fiebre', 'Fiebre', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['vomitos', 'Vómitos', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['distension_abdominal', 'Distensión Abdominal', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['defensa_abdominal', 'Defensa Abdominal', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['signos_vitales', 'Signos Vitales', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Cirugía General — 2 estados configurados');
    }
}
