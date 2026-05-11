<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;

class EFOrtopediaPediatricaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa      = Empresa::first();
        $especialidad = Especialidad::where('codigo', 'ORTO-PED')->where('empresa_id', $empresa->id)->first();
        $plantilla    = $especialidad
            ? EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->where('activo', true)->latest()->first()
            : null;

        if (!$plantilla) { $this->command->warn('Ortopedia Pediátrica — plantilla no encontrada.'); return; }

        // EN ENFERMERÍA - Evaluación Inicial
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Evaluación Ortopédica Inicial');
        $s  = $this->seccion($plantilla, $ef, 'Evaluación Ortopédica', 'fa-bone', '#FF5722', 1);
        $this->campos($s->id, [
            ['motivo', 'Motivo de Consulta', 'select',
                ['Fractura', 'Luxación', 'Escoliosis', 'Pie plano', 'Marcha anormal', 'Dolor articular', 'Trauma deportivo', 'Control post-quirúrgico', 'Otro'],
                true, null, null, null, 6],
            ['lado_afectado', 'Lado Afectado', 'radio', ['Derecho', 'Izquierdo', 'Bilateral', 'No aplica'], false, 'No aplica', null, null, 6],
            ['dolor_eva', 'Dolor (EVA 0-10)', 'range', null, false, '0', null, null, 4],
            ['movilidad_reducida', 'Movilidad Reducida', 'radio', ['Sí', 'No'], false, 'No', null, null, 4],
            ['inflamacion', 'Inflamación', 'radio', ['Sí', 'No'], false, 'No', null, null, 4],
            ['deformidad_visible', 'Deformidad Visible', 'radio', ['Sí', 'No'], false, 'No', null, null, 4],
            ['antecedentes_trauma', 'Antecedentes de Trauma', 'textarea', null, false, null, null, null, 12],
        ]);

        // EN CONSULTORIO - Examen Ortopédico
        $ef2 = $this->formularioEstado($plantilla, 'en_consultorio', 'Examen Ortopédico Completo');

        $s2 = $this->seccion($plantilla, $ef2, 'Examen Físico Ortopédico', 'fa-stethoscope', '#FF5722', 1);
        $this->campos($s2->id, [
            ['inspeccion', 'Inspección', 'textarea', null, false, null, null, null, 12],
            ['palpacion', 'Palpación', 'textarea', null, false, null, null, null, 12],
            ['rango_movimiento', 'Rango de Movimiento', 'textarea', null, false, null, null, null, 12],
            ['fuerza_muscular', 'Fuerza Muscular', 'select',
                ['0/5 - Sin contracción', '1/5 - Contracción sin movimiento', '2/5 - Movimiento sin gravedad',
                 '3/5 - Contra gravedad', '4/5 - Contra resistencia parcial', '5/5 - Normal'],
                false, '5/5 - Normal', null, null, 6],
            ['reflejos', 'Reflejos', 'select', ['Normales', 'Disminuidos', 'Aumentados', 'Ausentes'], false, 'Normales', null, null, 6],
        ]);

        $s3 = $this->seccion($plantilla, $ef2, 'Pruebas Especiales', 'fa-vials', '#E64A19', 2);
        $this->campos($s3->id, [
            ['prueba_trendelenburg', 'Prueba de Trendelenburg', 'select', ['Positiva', 'Negativa'], false, 'Negativa', null, null, 4],
            ['prueba_galeazzi', 'Prueba de Galeazzi', 'select', ['Positiva', 'Negativa'], false, 'Negativa', null, null, 4],
            ['angulo_abduccion', 'Ángulo de Abducción de Cadera', 'number', null, false, null, 'grados', null, 4],
            ['rotacion_interna', 'Rotación Interna', 'number', null, false, null, 'grados', null, 4],
            ['rotacion_externa', 'Rotación Externa', 'number', null, false, null, 'grados', null, 4],
            ['test_adams', 'Test de Adams (Escoliosis)', 'select', ['Positivo', 'Negativo'], false, 'Negativo', null, null, 6],
        ]);

        // EN ESTUDIO - Estudios de Imagen
        $ef3 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios de Imagen');
        $s4 = $this->seccion($plantilla, $ef3, 'Radiografías y Estudios', 'fa-x-ray', '#D84315', 1);
        $this->campos($s4->id, [
            ['estudios_solicitados', 'Estudios Solicitados', 'checkbox',
                ['Rx simple AP/Lateral', 'Rx comparativa', 'TAC', 'RMN', 'Ecografía musculoesquelética',
                 'Gammagrafía ósea', 'DEXA (densitometría)', 'Estudio de marcha'],
                false, null, null, null, 12],
            ['hallazgos_radiologicos', 'Hallazgos Radiológicos', 'textarea', null, false, null, null, null, 12],
            ['mediciones_angulares', 'Mediciones Angulares', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Ortopedia Pediátrica — 3 estados configurados');
    }
}
