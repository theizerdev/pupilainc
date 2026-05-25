<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaNeurocirugiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'NEUROCI')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Neurocirugía',
            'descripcion'               => 'Plantilla para evaluación neuroquirúrgica.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Examen Neurológico ─────────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Neurológico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#3F51B5',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['glasgow',            'Escala de Glasgow',        'number', null, false, null, '/15', null, 4],
            ['estado_mental',      'Estado Mental',            'select',
                ['Normal', 'Confuso', 'Desorientado', 'Estuporoso', 'Comatoso'], false, 'Normal', null, null, 4],
            ['pupilas',            'Pupilas',                  'select',
                ['Isocóricas reactivas', 'Anisocoria', 'Midriasis bilateral', 'Miosis bilateral'],
                false, 'Isocóricas reactivas', null, null, 4],
            ['fuerza_miembro_sup', 'Fuerza MMSS',              'select',
                ['5/5 Normal', '4/5', '3/5', '2/5', '1/5', '0/5 Plejia'], false, '5/5 Normal', null, null, 6],
            ['fuerza_miembro_inf', 'Fuerza MMII',              'select',
                ['5/5 Normal', '4/5', '3/5', '2/5', '1/5', '0/5 Plejia'], false, '5/5 Normal', null, null, 6],
            ['reflejos',           'Reflejos',                 'select',
                ['Normales', 'Hiperreflexia', 'Hiporreflexia', 'Arreflexia'], false, 'Normales', null, null, 6],
            ['sensibilidad',       'Sensibilidad',             'textarea', null, false, null, null, null, 6],
            ['signos_meningeos',   'Signos Meníngeos',         'select',
                ['Negativos', 'Rigidez de nuca', 'Kernig +', 'Brudzinski +'], false, 'Negativos', null, null, 6],
            ['lasegue',            'Signo de Lasègue',         'select',
                ['Negativo', 'Positivo derecho', 'Positivo izquierdo', 'Positivo bilateral'],
                false, 'Negativo', null, null, 6],
            ['observaciones_examen','Observaciones',           'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Interpretación de Estudios de Imagen ───────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Interpretación de Estudios de Imagen',
            'icono'        => 'fa-x-ray',
            'color'        => '#FF5722',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['tc_craneal',    'TC Craneal',             'textarea', null, false, null, null, null, 6],
            ['rm_cerebral',   'RM Cerebral',            'textarea', null, false, null, null, null, 6],
            ['rm_columna',    'RM Columna',             'textarea', null, false, null, null, null, 6],
            ['angiografia',   'Angiografía / Angio-RM', 'textarea', null, false, null, null, null, 6],
            ['otros_estudios','Otros Estudios',         'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 3: Diagnóstico y Plan Quirúrgico ──────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#F44336',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['impresion_diagnostica',  'Impresión Diagnóstica',   'textarea', null, true,  null, null, null, 12],
            ['indicacion_quirurgica',  'Indicación Quirúrgica',   'select',
                ['No quirúrgico', 'Electivo', 'Urgente', 'Emergencia'], false, 'No quirúrgico', null, null, 6],
            ['procedimiento_propuesto','Procedimiento Propuesto', 'textarea', null, false, null, null, null, 6],
            ['plan_terapeutico',       'Plan Terapéutico',        'textarea', null, false, null, null, null, 12],
            ['proxima_cita',           'Próxima Cita',            'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', 'Post-operatorio', 'Urgente'], false, null, null, null, 6],
            ['observaciones',          'Observaciones',           'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Neurocirugía creada.');
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
