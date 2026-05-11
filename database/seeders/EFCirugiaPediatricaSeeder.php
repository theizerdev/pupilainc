<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFCirugiaPediatricaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'CIR-PED')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Cirugía Pediátrica — plantilla no encontrada.'); return; }

        // ── EN CONSULTORIO ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación Quirúrgica Pediátrica');

        $s = $this->seccion($plantilla, $ef, 'Examen Físico', 'fa-child', '#4CAF50', 1);
        $this->campos($s->id, [
            ['aspecto_general', 'Aspecto General', 'select',
                ['Activo y alerta', 'Tranquilo', 'Irritable', 'Decaído', 'Con dolor'],
                false, 'Activo y alerta', null, null, 6],
            ['hidratacion', 'Hidratación', 'select',
                ['Bien hidratado', 'Deshidratación leve', 'Deshidratación moderada', 'Deshidratación severa'],
                false, 'Bien hidratado', null, null, 6],
            ['abdomen', 'Abdomen', 'textarea', null, false, null, null, null, 12],
            ['ruidos_intestinales', 'Ruidos Intestinales', 'select',
                ['Presentes normales', 'Aumentados', 'Disminuidos', 'Ausentes'],
                false, 'Presentes normales', null, null, 6],
            ['hernias', 'Hernias', 'textarea', null, false, null, null, null, 12],
            ['genitales', 'Genitales', 'textarea', null, false, null, null, null, 12],
            ['mcburney', 'Punto de McBurney', 'select', ['Negativo', 'Positivo'], false, 'Negativo', null, null, 6],
            ['blumberg', 'Signo de Blumberg', 'select', ['Negativo', 'Positivo'], false, 'Negativo', null, null, 6],
            ['observaciones_examen', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $s2 = $this->seccion($plantilla, $ef, 'Evaluación Quirúrgica', 'fa-procedures', '#F44336', 2);
        $this->campos($s2->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true, null, null, null, 12],
            ['indicacion_quirurgica', 'Indicación Quirúrgica', 'select',
                ['Electiva', 'Urgente', 'Emergencia', 'No quirúrgico'],
                false, 'Electiva', null, null, 6],
            ['procedimiento_propuesto', 'Procedimiento Propuesto', 'textarea', null, true, null, null, null, 12],
            ['riesgo_anestesico', 'Riesgo Anestésico (ASA)', 'select',
                ['ASA I - Sano', 'ASA II - Enfermedad leve', 'ASA III - Enfermedad severa',
                 'ASA IV - Amenaza vida', 'ASA V - Moribundo'],
                false, 'ASA I - Sano', null, null, 6],
            ['consentimiento_informado', 'Consentimiento Informado', 'select',
                ['Firmado por padres/tutores', 'Pendiente', 'Rechazado'],
                false, 'Firmado por padres/tutores', null, null, 6],
            ['plan_preoperatorio', 'Plan Preoperatorio', 'textarea', null, false, null, null, null, 12],
            ['proxima_cita', 'Próxima Cita', 'date', null, false, null, null, null, 6],
            ['observaciones_plan', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Quirúrgico Pediátrico');

        $s3 = $this->seccion($plantilla, $ef2, 'Evaluación Inicial', 'fa-user-nurse', '#FF9800', 1);
        $this->campos($s3->id, [
            ['dolor', 'Dolor (Escala pediátrica)', 'select',
                ['Sin dolor', 'Dolor leve', 'Dolor moderado', 'Dolor severo'],
                false, 'Sin dolor', null, null, 6],
            ['fiebre', 'Fiebre', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['vomitos', 'Vómitos', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['llanto', 'Llanto inconsolable', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['rechazo_alimentacion', 'Rechazo alimentación', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['signos_vitales', 'Signos Vitales', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Cirugía Pediátrica — 2 estados configurados');
    }
}
