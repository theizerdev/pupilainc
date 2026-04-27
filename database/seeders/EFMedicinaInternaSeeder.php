<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFMedicinaInternaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MED-INT')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Medicina Interna — plantilla no encontrada.'); return; }

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Medicina Interna');

        $s = $this->seccion($plantilla, $ef, 'Evaluación Inicial', 'fa-user-md', '#EF5350', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo de Consulta', 'select',
                ['Control crónico', 'Descompensación', 'Dolor', 'Fiebre', 'Disnea', 'Edemas', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['enfermedades_cronicas', 'Enfermedades Crónicas', 'checkbox',
                ['HTA', 'Diabetes', 'Dislipidemia', 'Hipotiroidismo', 'EPOC', 'Insuficiencia cardíaca', 'ERC', 'Ninguna'],
                false, null, null, null, 12],
            ['medicacion_actual', 'Medicación Actual', 'textarea', null, false, null, null, 'Liste los medicamentos que toma...', 12],
        ]);

        // ── EN CONSULTORIO ────────────────────────────────────────────────────
        $efConsultorio = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación de Medicina Interna');

        $s1 = $this->seccion($plantilla, $efConsultorio, 'Historia Clínica', 'fa-file-medical', '#2196F3', 1);
        $this->campos($s1->id, [
            ['enfermedad_actual', 'Enfermedad Actual', 'textarea', null, true, null, null, null, 12],
            ['revision_sistemas', 'Revisión por Sistemas', 'textarea', null, false, null, null, 'Cardiovascular, respiratorio, digestivo...', 12],
        ]);

        $s2 = $this->seccion($plantilla, $efConsultorio, 'Examen Físico', 'fa-stethoscope', '#2196F3', 2);
        $this->campos($s2->id, [
            ['estado_general', 'Estado General', 'select',
                ['Buen estado', 'Regular estado', 'Mal estado'], false, 'Buen estado', null, null, 6],
            ['cardiovascular', 'Cardiovascular', 'textarea', null, false, null, null, 'Ruidos cardíacos, soplos...', 6],
            ['respiratorio', 'Respiratorio', 'textarea', null, false, null, null, 'Murmullo vesicular, ruidos agregados...', 6],
            ['abdomen', 'Abdomen', 'textarea', null, false, null, null, null, 6],
            ['extremidades', 'Extremidades', 'textarea', null, false, null, null, 'Edemas, pulsos...', 6],
            ['neurologico', 'Neurológico', 'textarea', null, false, null, null, null, 6],
        ]);

        $s3 = $this->seccion($plantilla, $efConsultorio, 'Plan', 'fa-clipboard-check', '#2196F3', 3);
        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true, null, null, null, 12],
            ['plan_manejo', 'Plan de Manejo', 'textarea', null, true, null, null, null, 12],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Complementarios');

        $s2 = $this->seccion($plantilla, $ef2, 'Estudios', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios_lab', 'Laboratorio', 'checkbox',
                ['Hemograma', 'Glucemia', 'HbA1c', 'Perfil lipídico', 'Función renal', 'Función hepática', 'TSH', 'Orina', 'Proteinuria 24h'],
                false, null, null, null, 12],
            ['estudios_imagen', 'Imagen', 'checkbox',
                ['Rx Tórax', 'Eco abdominal', 'Eco renal', 'ECG', 'Ecocardiograma', 'TAC', 'RMN'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Medicina Interna — 3 estados configurados');
    }
}
