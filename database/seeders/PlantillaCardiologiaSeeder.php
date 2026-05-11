<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaCardiologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'CARDIO')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Cardiología',
            'descripcion'               => 'Plantilla para evaluación cardiológica.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Examen Cardiovascular ─────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Cardiovascular',
            'icono'        => 'fa-stethoscope',
            'color'        => '#F44336',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['ritmo_cardiaco',     'Ritmo Cardíaco',            'select',
                ['Regular', 'Irregular'], false, 'Regular', null, null, 6],
            ['frecuencia_cardiaca_ex', 'Frecuencia Cardíaca',   'number', null, false, null, 'lpm', null, 6],
            ['ruidos_cardiacos',   'Ruidos Cardíacos',          'select',
                ['Normales', 'Apagados', 'Reforzados', 'Arrítmicos'], false, 'Normales', null, null, 6],
            ['soplos',             'Soplos',                    'select',
                ['Ausentes', 'Sistólico', 'Diastólico', 'Continuo'], false, 'Ausentes', null, null, 6],
            ['pulsos_perifericos', 'Pulsos Periféricos',        'select',
                ['Presentes y simétricos', 'Disminuidos', 'Ausentes', 'Asimétricos'], false, 'Presentes y simétricos', null, null, 6],
            ['ingurgitacion_yug',  'Ingurgitación Yugular',     'select',
                ['Ausente', 'Leve', 'Moderada', 'Severa'], false, 'Ausente', null, null, 6],
            ['edema_miembros',     'Edema Miembros Inferiores', 'select',
                ['Ausente', '+/4', '++/4', '+++/4', '++++/4'], false, 'Ausente', null, null, 6],
            ['auscultacion_pulm',  'Auscultación Pulmonar',     'textarea', null, false, null, null, 'Crepitantes, sibilancias...', 6],
            ['observaciones_examen', 'Observaciones del Examen','textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Interpretación de Estudios ─────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Interpretación de Estudios',
            'icono'        => 'fa-file-medical',
            'color'        => '#9C27B0',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['ecg',              'ECG',                        'textarea', null, false, null, null, 'Ritmo, eje, alteraciones...', 12],
            ['ecocardiograma',   'Ecocardiograma',             'textarea', null, false, null, null, 'FEVI, valvulopatías, derrame...', 12],
            ['holter',           'Holter / Monitor',           'textarea', null, false, null, null, null, 6],
            ['prueba_esfuerzo',  'Prueba de Esfuerzo',         'textarea', null, false, null, null, null, 6],
            ['laboratorio_cv',   'Laboratorio Cardiovascular', 'textarea', null, false, null, null, 'Troponinas, BNP, perfil lipídico...', 12],
        ]);

        // ── SECCIÓN 3: Diagnóstico y Plan ─────────────────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#3F51B5',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['clase_funcional',       'Clase Funcional NYHA',  'select',
                ['Clase I', 'Clase II', 'Clase III', 'Clase IV', 'No aplica'], false, 'No aplica', null, null, 6],
            ['riesgo_cardiovascular', 'Riesgo Cardiovascular', 'select',
                ['Bajo', 'Moderado', 'Alto', 'Muy alto'], false, 'Bajo', null, null, 6],
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', 'Urgente'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Cardiología creada.');
    }

    private function campos(int $seccionId, array $campos): void
    {
        foreach ($campos as $orden => $c) {
            PlantillaCampo::create([
                'seccion_id'     => $seccionId,
                'nombre_campo'   => $c[0],
                'etiqueta'       => $c[1],
                'tipo'           => $c[2],
                'opciones'       => $c[3],
                'obligatorio'    => $c[4],
                'valor_defecto'  => $c[5],
                'unidad'         => $c[6],
                'placeholder'    => $c[7],
                'ancho_columnas' => $c[8],
                'orden'          => $orden + 1,
                'activo'         => true,
            ]);
        }
    }
}
