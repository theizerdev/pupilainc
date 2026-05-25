<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFOtorrinolaringologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'ORL')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('ORL — plantilla no encontrada.'); return; }

        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage ORL');
        $s  = $this->seccion($plantilla, $ef, 'Síntomas ORL', 'fa-ear', '#795548', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo', 'select',
                ['Control', 'Hipoacusia', 'Acúfenos', 'Vértigo', 'Obstrucción nasal', 'Epistaxis', 'Odinofagia', 'Disfonía', 'Otro'],
                true, null, null, null, 6],
            ['escala_dolor', 'Dolor (0-10)', 'range', null, false, '0', null, null, 6],
            ['lado_afectado', 'Lado Afectado', 'radio',
                ['Derecho', 'Izquierdo', 'Bilateral', 'No aplica'], false, 'No aplica', null, null, 6],
            ['tiempo_evolucion', 'Tiempo de Evolución', 'select',
                ['Horas', '1-3 días', '1 semana', '2 semanas', '1 mes', 'Crónico'], false, null, null, null, 6],
            ['descripcion', 'Descripción', 'textarea', null, false, null, null, null, 12],
        ]);

        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios ORL');
        $s2  = $this->seccion($plantilla, $ef2, 'Estudios Solicitados', 'fa-flask', '#EC407A', 1);
        $this->campos($s2->id, [
            ['estudios', 'Estudios', 'checkbox',
                ['Audiometría', 'Impedanciometría', 'BERA', 'Nasofibroscopía', 'TAC senos paranasales', 'TAC oídos', 'Cultivo secreción', 'Rx cavum'],
                false, null, null, null, 12],
            ['observaciones', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ ORL — 2 estados configurados');
    }
}
