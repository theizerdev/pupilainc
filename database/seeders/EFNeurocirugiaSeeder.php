<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFNeurocirugiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEUROCI')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Neurocirugía — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Neuroquirúrgico');
        $s  = $this->seccion($plantilla, $ef, 'Evaluación Inicial', 'fa-brain', '#673AB7', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control postoperatorio', 'Dolor cervical/lumbar', 'Hernia discal', 'Trauma craneal', 'Hidrocefalia', 'Tumor', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['glasgow', 'Escala de Glasgow', 'number', null, false, '15', null, '3-15', 4],
            ['deficit_neurologico', 'Déficit Neurológico', 'checkbox',
                ['Sin déficit', 'Paresia', 'Plejia', 'Parestesias', 'Incontinencia', 'Afasia'],
                false, null, null, null, 8],
            ['cirugia_previa', 'Cirugía Previa', 'radio', ['Sí', 'No'], false, 'No', null, null, 6],
            ['descripcion_cirugia', 'Descripción Cirugía Previa', 'text', null, false, null, null, null, 6],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Neuroquirúrgicos');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-x-ray', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['TAC cerebral', 'TAC columna', 'RMN cerebral', 'RMN columna', 'Angiografía', 'Rx columna', 'EMG/VCN'],
                false, null, null, null, 12],
            ['urgencia', 'Urgencia', 'radio', ['Rutina', 'Preferente', 'Urgente'], false, 'Rutina', null, null, 6],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Neurocirugía — 2 estados configurados');
    }
}
