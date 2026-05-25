<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFNeurologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEURO')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Neurología — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Neurológico');
        $s  = $this->seccion($plantilla, $ef, 'Síntomas Neurológicos', 'fa-brain', '#9C27B0', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control', 'Cefalea', 'Mareos/Vértigo', 'Convulsiones', 'Pérdida de conciencia', 'Déficit motor', 'Déficit sensitivo', 'Temblor', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Intensidad (0-10)', 'range', null, false, '0', null, null, 6],
            ['inicio', 'Inicio', 'select',
                ['Súbito', 'Progresivo', 'Episódico', 'Crónico'], false, null, null, null, 6],
            ['antecedentes_neuro', 'Antecedentes Neurológicos', 'checkbox',
                ['Epilepsia', 'Migraña', 'ACV previo', 'Parkinson', 'Esclerosis múltiple', 'Ninguno'],
                false, null, null, null, 6],
            ['medicacion', 'Medicación Neurológica', 'textarea', null, false, null, null, null, 12],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Neurológicos');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['EEG', 'EMG/VCN', 'TAC cerebral', 'RMN cerebral', 'RMN columna', 'Doppler carotídeo', 'PL', 'Potenciales evocados'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Neurología — 2 estados configurados');
    }
}
