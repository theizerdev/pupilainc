<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFGastroenterologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'GASTRO')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Gastroenterología — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Gastroenterológico');
        $s  = $this->seccion($plantilla, $ef, 'Síntomas Digestivos', 'fa-procedures', '#FF9800', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control', 'Dolor abdominal', 'Náuseas/Vómitos', 'Diarrea', 'Constipación', 'Sangrado digestivo', 'Disfagia', 'Pirosis', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['localizacion_dolor', 'Localización', 'select',
                ['Epigastrio', 'Hipocondrio derecho', 'Hipocondrio izquierdo', 'Mesogastrio', 'FID', 'FII', 'Hipogastrio', 'Difuso'],
                false, null, null, null, 6],
            ['habitos_intestinales', 'Hábitos Intestinales', 'select',
                ['Normales', 'Constipación', 'Diarrea', 'Alternancia', 'Sangre en heces'],
                false, 'Normales', null, null, 6],
            ['observaciones', 'Descripción', 'textarea', null, false, null, null, null, 12],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Digestivos');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['Endoscopía alta', 'Colonoscopía', 'Eco abdominal', 'TAC abdomen', 'Coprológico', 'H. pylori', 'Transaminasas', 'Bilirrubinas', 'Amilasa/Lipasa'],
                false, null, null, null, 12],
            ['ayuno', '¿Paciente en Ayuno?', 'radio', ['Sí', 'No'], false, 'No', null, null, 6],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Gastroenterología — 2 estados configurados');
    }
}
