<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFMastologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'MASTOL')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Mastología — plantilla no encontrada.'); return; }

        // ── EN CONSULTORIO ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación Mastológica');

        $s = $this->seccion($plantilla, $ef, 'Datos Gineco-Obstétricos', 'fa-venus', '#E91E63', 1);
        $this->campos($s->id, [
            ['menarquia_edad', 'Menarquia (edad)', 'number', null, false, null, '8', '20', 4],
            ['menopausia', 'Menopausia', 'select', ['Sí', 'No'], false, 'No', null, null, 4],
            ['edad_menopausia', 'Edad Menopausia', 'number', null, false, null, '35', '65', 4],
            ['gestas', 'Gestas', 'number', null, false, '0', '0', '20', 3],
            ['partos', 'Partos', 'number', null, false, '0', '0', '20', 3],
            ['lactancia', 'Lactancia', 'select', ['Sí', 'No', 'En curso'], false, 'Sí', null, null, 4],
            ['anticonceptivos_orales', 'Anticonceptivos Orales', 'select', ['Sí', 'No', 'Suspendidos'], false, 'No', null, null, 6],
            ['terapia_reemplazo_hormonal', 'Terapia Reemplazo Hormonal', 'select', ['Sí', 'No'], false, 'No', null, null, 6],
        ]);

        $s2 = $this->seccion($plantilla, $ef, 'Examen Físico Mamario', 'fa-hand-holding-medical', '#F06292', 2);
        $this->campos($s2->id, [
            ['inspeccion_estatica', 'Inspección Estática', 'textarea', null, false, null, null, null, 12],
            ['inspeccion_dinamica', 'Inspección Dinámica', 'textarea', null, false, null, null, null, 12],
            ['palpacion_mama_derecha', 'Palpación Mama Derecha', 'textarea', null, false, null, null, null, 12],
            ['palpacion_mama_izquierda', 'Palpación Mama Izquierda', 'textarea', null, false, null, null, null, 12],
            ['axila_derecha', 'Axila Derecha', 'textarea', null, false, null, null, null, 12],
            ['axila_izquierda', 'Axila Izquierda', 'textarea', null, false, null, null, null, 12],
            ['expresion_pezon_derecho', 'Expresión Pezón Derecho', 'textarea', null, false, null, null, null, 12],
            ['expresion_pezon_izquierdo', 'Expresión Pezón Izquierdo', 'textarea', null, false, null, null, null, 12],
            ['observaciones_examen', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $s3 = $this->seccion($plantilla, $ef, 'Diagnóstico y Plan', 'fa-notes-medical', '#C2185B', 3);
        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true, null, null, null, 12],
            ['categoria_birads', 'Categoría BIRADS Global', 'select',
                ['BIRADS 0 - Incompleto', 'BIRADS 1 - Negativo', 'BIRADS 2 - Benigno',
                 'BIRADS 3 - Probablemente benigno', 'BIRADS 4 - Sospechoso', 'BIRADS 5 - Altamente sospechoso',
                 'BIRADS 6 - Biopsia probada malignidad'],
                false, 'BIRADS 1 - Negativo', null, null, 6],
            ['conducta', 'Conducta', 'select',
                ['Seguimiento rutinario', 'Seguimiento corto plazo (6 meses)', 'Biopsia',
                 'Cirugía', 'Referencia oncología', 'Tratamiento médico'],
                false, 'Seguimiento rutinario', null, null, 6],
            ['plan_terapeutico', 'Plan Terapéutico', 'textarea', null, false, null, null, null, 12],
            ['proxima_cita', 'Próxima Cita', 'date', null, false, null, null, null, 6],
            ['observaciones_plan', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios de Imagen Mamaria');

        $s4 = $this->seccion($plantilla, $ef2, 'Resultados de Estudios', 'fa-x-ray', '#AD1457', 1);
        $this->campos($s4->id, [
            ['mamografia_realizada', 'Mamografía Realizada', 'select', ['Sí', 'No'], false, 'No', null, null, 6],
            ['resultado_mamografia', 'Resultado Mamografía', 'select',
                ['Normal', 'Hallazgos benignos', 'Hallazgos probablemente benignos',
                 'Hallazgos sospechosos', 'Altamente sugestivo de malignidad'],
                false, 'Normal', null, null, 6],
            ['eco_mamario_realizado', 'Eco. Mamario Realizado', 'select', ['Sí', 'No'], false, 'No', null, null, 6],
            ['resultado_eco_mamario', 'Resultado Eco. Mamario', 'select',
                ['Normal', 'Quiste simple', 'Quiste complejo', 'Nódulo sólido benigno',
                 'Nódulo sospechoso', 'Lesión maligna'],
                false, 'Normal', null, null, 6],
            ['biopsia_realizada', 'Biopsia Realizada', 'select', ['Sí', 'No', 'Pendiente'], false, 'No', null, null, 6],
            ['resultado_biopsia', 'Resultado Biopsia', 'textarea', null, false, null, null, null, 12],
            ['otros_estudios', 'Otros Estudios', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Mastología — 2 estados configurados');
    }
}
