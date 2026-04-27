<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use Illuminate\Database\Seeder;

class EFOftalmologiaSeeder extends BaseEstadoFormularioSeeder
{
    public function run(): void
    {
        $empresa = Empresa::first();

        $especialidad = Especialidad::where('codigo', 'OFTAL')
            ->where('empresa_id', $empresa->id)->first();

        if (!$especialidad) {
            $this->command->warn('Oftalmología no encontrada — omitida.');
            return;
        }

        $plantilla = EspecialidadPlantilla::where('especialidad_id', $especialidad->id)
            ->where('activo', true)->latest()->first();

        if (!$plantilla) {
            $this->command->warn('Plantilla de Oftalmología no encontrada — omitida.');
            return;
        }

        // ── EN ENFERMERÍA ─────────────────────────────────────────────────────
        $ef = $this->formularioEstado($plantilla, 'en_enfermeria', 'Triage Oftalmológico');

        $s = $this->seccion($plantilla, $ef, 'Motivo de Consulta', 'fa-comment-medical', '#EF5350', 1);
        $this->campos($s->id, [
            ['motivo_principal', 'Motivo Principal', 'select',
                ['Revisión rutinaria', 'Disminución de visión', 'Dolor ocular', 'Ojo rojo', 'Secreción', 'Trauma', 'Cuerpo extraño', 'Otro'],
                true, 'Revisión rutinaria', null, null, 6],
            ['tiempo_evolucion', 'Tiempo de Evolución', 'select',
                ['Horas', '1-3 días', '1 semana', '2 semanas', '1 mes', 'Más de 1 mes', 'Crónico'],
                false, null, null, null, 6],
            ['descripcion_motivo', 'Descripción', 'textarea', null, false, null, null, 'Describa el motivo de consulta...', 12],
        ]);

        $s2 = $this->seccion($plantilla, $ef, 'Antecedentes Oculares', 'fa-eye', '#EF5350', 2);
        $this->campos($s2->id, [
            ['cirugia_ocular_previa', 'Cirugía Ocular Previa', 'radio',
                ['No', 'Sí — OD', 'Sí — OI', 'Sí — Ambos'], false, 'No', null, null, 6],
            ['usa_lentes', 'Usa Lentes', 'radio',
                ['No', 'Lentes de armazón', 'Lentes de contacto', 'Ambos'], false, 'No', null, null, 6],
            ['ultima_graduacion', 'Última Graduación', 'select',
                ['Menos de 6 meses', '6 meses - 1 año', '1-2 años', 'Más de 2 años', 'Nunca'],
                false, null, null, null, 6],
            ['antecedentes_oculares', 'Otros Antecedentes', 'textarea', null, false, null, null, 'Glaucoma, cataratas, retina...', 6],
        ]);

        // ── EN CONSULTORIO ────────────────────────────────────────────────────
        $efConsultorio = $this->formularioEstado($plantilla, 'en_consultorio', 'Evaluación Oftalmológica Completa');

        $s3 = $this->seccion($plantilla, $efConsultorio, 'Agudeza Visual', 'fa-eye-low-vision', '#2196F3', 1);
        $this->campos($s3->id, [
            ['av_od_sc', 'AV OD sin corrección', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200', 'CD', 'MM', 'PL', 'NPL'],
                false, null, null, null, 3],
            ['av_oi_sc', 'AV OI sin corrección', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200', 'CD', 'MM', 'PL', 'NPL'],
                false, null, null, null, 3],
            ['av_od_cc', 'AV OD con corrección', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200', 'CD', 'MM', 'PL', 'NPL'],
                false, null, null, null, 3],
            ['av_oi_cc', 'AV OI con corrección', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200', 'CD', 'MM', 'PL', 'NPL'],
                false, null, null, null, 3],
        ]);

        $s4 = $this->seccion($plantilla, $efConsultorio, 'Refracción', 'fa-glasses', '#2196F3', 2);
        $this->campos($s4->id, [
            ['ref_od_esfera', 'OD Esfera', 'number', null, false, null, 'D', null, 2],
            ['ref_od_cilindro', 'OD Cilindro', 'number', null, false, null, 'D', null, 2],
            ['ref_od_eje', 'OD Eje', 'number', null, false, null, '°', null, 2],
            ['ref_oi_esfera', 'OI Esfera', 'number', null, false, null, 'D', null, 2],
            ['ref_oi_cilindro', 'OI Cilindro', 'number', null, false, null, 'D', null, 2],
            ['ref_oi_eje', 'OI Eje', 'number', null, false, null, '°', null, 2],
        ]);

        $s5 = $this->seccion($plantilla, $efConsultorio, 'Biomicroscopía', 'fa-microscope', '#2196F3', 3);
        $this->campos($s5->id, [
            ['biomicroscopia_od', 'OD', 'textarea', null, false, null, null, 'Párpados, conjuntiva, córnea, cámara anterior, iris, cristalino...', 6],
            ['biomicroscopia_oi', 'OI', 'textarea', null, false, null, null, 'Párpados, conjuntiva, córnea, cámara anterior, iris, cristalino...', 6],
        ]);

        $s6 = $this->seccion($plantilla, $efConsultorio, 'Presión Intraocular', 'fa-tachometer-alt', '#2196F3', 4);
        $this->campos($s6->id, [
            ['pio_od', 'PIO OD', 'number', null, false, null, 'mmHg', null, 3],
            ['pio_oi', 'PIO OI', 'number', null, false, null, 'mmHg', null, 3],
            ['metodo_pio', 'Método', 'select', ['Tonometría de aplanación', 'Tonometría de aire', 'Tonopen', 'iCare'], false, 'Tonometría de aplanación', null, null, 6],
        ]);

        $s7 = $this->seccion($plantilla, $efConsultorio, 'Fondo de Ojo', 'fa-circle', '#2196F3', 5);
        $this->campos($s7->id, [
            ['fondo_ojo_od', 'OD', 'textarea', null, false, null, null, 'Papila, mácula, vasos, retina periférica...', 6],
            ['fondo_ojo_oi', 'OI', 'textarea', null, false, null, null, 'Papila, mácula, vasos, retina periférica...', 6],
        ]);

        // ── EN GOTAS ──────────────────────────────────────────────────────────
        $ef2 = $this->formularioEstado($plantilla, 'en_gotas', 'Aplicación de Gotas');

        $s3 = $this->seccion($plantilla, $ef2, 'Registro de Gotas', 'fa-eye-dropper', '#26C6DA', 1);
        $this->campos($s3->id, [
            ['tipo_gota', 'Tipo de Gota', 'select',
                ['Tropicamida 1%', 'Fenilefrina 10%', 'Ciclopentolato 1%', 'Atropina 1%', 'Tropicamida + Fenilefrina', 'Anestésico tópico', 'Fluoresceína', 'Otro'],
                true, 'Tropicamida 1%', null, null, 6],
            ['gotas_od', 'Gotas OD', 'number', null, false, '1', 'gotas', null, 3],
            ['gotas_oi', 'Gotas OI', 'number', null, false, '1', 'gotas', null, 3],
            ['hora_aplicacion', 'Hora de Aplicación', 'text', null, true, null, null, 'Ej: 10:30', 4],
            ['tiempo_espera', 'Tiempo de Espera', 'select',
                ['20 minutos', '30 minutos', '45 minutos', '60 minutos'], false, '30 minutos', null, null, 4],
            ['observaciones_gotas', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── DILATADO ──────────────────────────────────────────────────────────
        $ef3 = $this->formularioEstado($plantilla, 'dilatado', 'Control de Dilatación');

        $s4 = $this->seccion($plantilla, $ef3, 'Estado de Dilatación', 'fa-circle', '#00BCD4', 1);
        $this->campos($s4->id, [
            ['dilatacion_od', 'Dilatación OD', 'select',
                ['Sin dilatar', 'Dilatación incipiente', 'Dilatación parcial', 'Dilatación completa'],
                false, 'Sin dilatar', null, null, 6],
            ['dilatacion_oi', 'Dilatación OI', 'select',
                ['Sin dilatar', 'Dilatación incipiente', 'Dilatación parcial', 'Dilatación completa'],
                false, 'Sin dilatar', null, null, 6],
            ['hora_inicio_dilatacion', 'Hora Inicio', 'text', null, false, null, null, 'Ej: 10:30', 4],
            ['hora_revision', 'Hora Revisión', 'text', null, false, null, null, 'Ej: 11:00', 4],
            ['listo_para_fondo', '¿Listo para Fondo de Ojo?', 'radio',
                ['No', 'Sí'], false, 'No', null, null, 4],
            ['observaciones_dilatacion', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ÓPTICA ─────────────────────────────────────────────────────────
        $ef4 = $this->formularioEstado($plantilla, 'en_optica', 'Prescripción Óptica');

        $s5 = $this->seccion($plantilla, $ef4, 'Prescripción de Lentes', 'fa-glasses', '#AB47BC', 1);
        $this->campos($s5->id, [
            ['rx_od_esfera',   'OD Esfera',   'number', null, false, null, 'D',  null, 2],
            ['rx_od_cilindro', 'OD Cilindro', 'number', null, false, null, 'D',  null, 2],
            ['rx_od_eje',      'OD Eje',      'number', null, false, null, '°',  null, 2],
            ['rx_od_adicion',  'OD Adición',  'number', null, false, null, 'D',  null, 2],
            ['rx_od_av',       'OD AV Final', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200'],
                false, null, null, null, 4],
            ['rx_oi_esfera',   'OI Esfera',   'number', null, false, null, 'D',  null, 2],
            ['rx_oi_cilindro', 'OI Cilindro', 'number', null, false, null, 'D',  null, 2],
            ['rx_oi_eje',      'OI Eje',      'number', null, false, null, '°',  null, 2],
            ['rx_oi_adicion',  'OI Adición',  'number', null, false, null, 'D',  null, 2],
            ['rx_oi_av',       'OI AV Final', 'select',
                ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200'],
                false, null, null, null, 4],
        ]);

        $s6 = $this->seccion($plantilla, $ef4, 'Indicaciones Ópticas', 'fa-clipboard-list', '#AB47BC', 2);
        $this->campos($s6->id, [
            ['tipo_lente', 'Tipo de Lente', 'select',
                ['Monofocal', 'Bifocal', 'Progresivo', 'Ocupacional', 'Lentes de contacto blandos', 'Lentes de contacto rígidos'],
                false, 'Monofocal', null, null, 6],
            ['material_lente', 'Material', 'select',
                ['CR-39', 'Policarbonato', 'Trivex', 'Alto índice 1.67', 'Alto índice 1.74'],
                false, 'CR-39', null, null, 6],
            ['tratamiento_lente', 'Tratamiento', 'checkbox',
                ['Antirreflejo', 'Fotocromático', 'Filtro azul', 'Endurecido', 'Hidrofóbico'],
                false, null, null, null, 12],
            ['observaciones_optica', 'Observaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── EN ESTUDIO ────────────────────────────────────────────────────────
        $ef5 = $this->formularioEstado($plantilla, 'en_estudio', 'Estudios Complementarios');

        $s7 = $this->seccion($plantilla, $ef5, 'Estudios Solicitados', 'fa-microscope', '#EC407A', 1);
        $this->campos($s7->id, [
            ['estudios_solicitados', 'Estudios', 'checkbox',
                ['Campimetría', 'OCT', 'Retinografía', 'Angiografía', 'Biometría', 'Topografía corneal', 'Paquimetría', 'Ecografía ocular', 'Potencial visual evocado'],
                false, null, null, null, 12],
            ['urgencia_estudio', 'Urgencia', 'radio',
                ['Rutina', 'Preferente', 'Urgente'], false, 'Rutina', null, null, 6],
            ['observaciones_estudio', 'Indicaciones', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('  ✓ Oftalmología — 6 estados configurados');
    }
}
