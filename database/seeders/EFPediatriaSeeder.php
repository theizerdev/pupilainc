<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFPediatriaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'PEDIAT')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Pediatría — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Pediátrico');
        $s  = $this->seccion($plantilla, $ef, 'Evaluación Pediátrica', 'fa-baby', '#00BCD4', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control de niño sano', 'Fiebre', 'Tos / Catarro', 'Diarrea/Vómitos', 'Dolor', 'Erupción cutánea', 'Trauma', 'Otro'],
                true, null, null, null, 6],
            ['temperatura_axilar', 'Temperatura Axilar', 'number', null, false, null, '°C', null, 3],
            ['escala_dolor', 'Dolor (0-10)', 'range', null, false, '0', null, null, 3],
            ['vacunas_al_dia', 'Vacunas al Día', 'radio', ['Sí', 'No', 'No sabe'], false, 'Sí', null, null, 6],
            ['lactancia', 'Lactancia', 'select',
                ['Materna exclusiva', 'Mixta', 'Artificial', 'No aplica'], false, 'No aplica', null, null, 6],
            ['alergias', 'Alergias Conocidas', 'text', null, false, 'Ninguna', null, null, 6],
            ['descripcion', 'Descripción del Tutor', 'textarea', null, false, null, null, null, 12],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Pediátricos');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['Hemograma', 'PCR', 'Orina', 'Coprocultivo', 'Rx Tórax', 'Eco abdominal', 'Test rápido Strep', 'Test rápido Influenza'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Pediatría — 2 estados configurados');
    }
}
