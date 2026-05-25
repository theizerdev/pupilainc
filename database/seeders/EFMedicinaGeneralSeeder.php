<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFMedicinaGeneralSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MED-GEN')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Medicina General — plantilla no encontrada.'); return; }

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage');

        $s = $this->seccion($plantilla, $ef, 'Motivo y Síntomas', 'fa-stethoscope', '#EF5350', 1);
        $this->campos($s->id, [
            ['motivo_consulta', 'Motivo de Consulta', 'select',
                ['Consulta de control', 'Fiebre', 'Dolor', 'Malestar general', 'Tos / Gripe', 'Herida / Trauma', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Escala de Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['tiempo_evolucion', 'Tiempo de Evolución', 'select',
                ['Horas', '1-3 días', '1 semana', '2 semanas', '1 mes', 'Crónico'], false, null, null, null, 6],
            ['alergias_conocidas', 'Alergias Conocidas', 'text', null, false, 'Ninguna', null, 'Medicamentos, alimentos...', 6],
            ['descripcion', 'Descripción', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN CONSULTORIO ────────────────────────────────────────────────────
        $efConsultorio = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación Médica General');

        $s1 = $this->seccion($plantilla, $efConsultorio, 'Anamnesis', 'fa-clipboard-list', '#2196F3', 1);
        $this->campos($s1->id, [
            ['enfermedad_actual', 'Enfermedad Actual', 'textarea', null, true, null, null, 'Descripción detallada...', 12],
            ['antecedentes_personales', 'Antecedentes Personales', 'textarea', null, false, null, null, 'Enfermedades previas, cirugías...', 6],
            ['antecedentes_familiares', 'Antecedentes Familiares', 'textarea', null, false, null, null, 'Enfermedades hereditarias...', 6],
        ]);

        $s2 = $this->seccion($plantilla, $efConsultorio, 'Examen Físico', 'fa-user-md', '#2196F3', 2);
        $this->campos($s2->id, [
            ['aspecto_general', 'Aspecto General', 'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general'], false, 'Buen estado general', null, null, 6],
            ['estado_conciencia', 'Estado de Conciencia', 'select',
                ['Alerta', 'Somnoliento', 'Estuporoso', 'Comatoso'], false, 'Alerta', null, null, 6],
            ['cabeza_cuello', 'Cabeza y Cuello', 'textarea', null, false, null, null, null, 6],
            ['torax', 'Tórax', 'textarea', null, false, null, null, 'Corazón, pulmones...', 6],
            ['abdomen', 'Abdomen', 'textarea', null, false, null, null, null, 6],
            ['extremidades', 'Extremidades', 'textarea', null, false, null, null, null, 6],
        ]);

        $s3 = $this->seccion($plantilla, $efConsultorio, 'Impresión Diagnóstica', 'fa-notes-medical', '#2196F3', 3);
        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true, null, null, null, 12],
            ['plan_tratamiento', 'Plan de Tratamiento', 'textarea', null, true, null, null, null, 12],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Complementarios');

        $s2 = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['Hemograma completo', 'Glucemia', 'Perfil lipídico', 'Función renal', 'Función hepática', 'Orina completa', 'Rx Tórax', 'ECG', 'Otro'],
                false, null, null, null, 12],
            ['resultados_pendientes', 'Resultados Pendientes', 'radio',
                ['Sí', 'No'], false, 'No', null, null, 6],
            ['observaciones', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Medicina General — 3 estados configurados');
    }
}
