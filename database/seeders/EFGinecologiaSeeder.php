<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFGinecologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'GINECO')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Ginecología — plantilla no encontrada.'); return; }

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Ginecológico');

        $s = $this->seccion($plantilla, $ef, 'Motivo y Antecedentes', 'fa-venus', '#E91E63', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo de Consulta', 'select',
                ['Control ginecológico', 'Dolor pélvico', 'Sangrado anormal', 'Flujo vaginal', 'Control embarazo', 'Planificación familiar', 'Menopausia', 'Otro'],
                true, null, null, null, 6],
            ['fum', 'Fecha Última Menstruación', 'date', null, false, null, null, null, 6],
            ['ciclo_menstrual', 'Ciclo Menstrual', 'select',
                ['Regular', 'Irregular', 'Amenorrea', 'Oligomenorrea', 'Menorragia', 'Postmenopáusica'],
                false, null, null, null, 6],
            ['gestas', 'Gestas', 'number', null, false, '0', null, null, 3],
            ['partos', 'Partos', 'number', null, false, '0', null, null, 3],
            ['cesareas', 'Cesáreas', 'number', null, false, '0', null, null, 3],
            ['abortos', 'Abortos', 'number', null, false, '0', null, null, 3],
            ['metodo_anticonceptivo', 'Método Anticonceptivo', 'select',
                ['Ninguno', 'ACO', 'DIU', 'Implante', 'Inyectable', 'Preservativo', 'Ligadura', 'Otro'],
                false, 'Ninguno', null, null, 6],
            ['ultimo_pap', 'Último PAP', 'select',
                ['Menos de 1 año', '1-2 años', '2-3 años', 'Más de 3 años', 'Nunca'],
                false, null, null, null, 6],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Ginecológicos');

        $s2 = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['PAP / Citología', 'Colposcopía', 'Eco transvaginal', 'Eco obstétrica', 'Mamografía', 'Eco mamaria', 'Hormonal FSH/LH/E2', 'Beta HCG', 'Cultivo vaginal'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Ginecología — 2 estados configurados');
    }
}
