<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFNefrologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'NEFRO')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Nefrología — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Nefrológico');
        $s  = $this->seccion($plantilla, $ef, 'Evaluación Renal', 'fa-kidneys', '#009688', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control ERC', 'Control diálisis', 'Edemas', 'HTA', 'Hematuria', 'Proteinuria', 'IRA', 'Otro'],
                true, null, null, null, 6],
            ['diuresis', 'Diuresis', 'select',
                ['Normal', 'Oliguria', 'Anuria', 'Poliuria', 'Hematuria'], false, 'Normal', null, null, 6],
            ['estadio_erc', 'Estadio ERC', 'select',
                ['No aplica', 'G1', 'G2', 'G3a', 'G3b', 'G4', 'G5', 'G5D (diálisis)'],
                false, 'No aplica', null, null, 6],
            ['acceso_dialisis', 'Acceso Diálisis', 'select',
                ['No aplica', 'FAV', 'Catéter HD', 'Catéter DP'], false, 'No aplica', null, null, 6],
            ['peso_seco', 'Peso Seco', 'number', null, false, null, 'kg', null, 4],
            ['peso_actual', 'Peso Actual', 'number', null, false, null, 'kg', null, 4],
            ['ganancia_peso', 'Ganancia de Peso', 'number', null, false, null, 'kg', null, 4],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Nefrológicos');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['Creatinina/BUN', 'TFG', 'Electrolitos', 'Orina completa', 'Proteinuria 24h', 'Eco renal', 'Biopsia renal', 'PTH', 'Ferritina/Hierro'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Nefrología — 2 estados configurados');
    }
}
