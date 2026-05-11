<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaCirugiaGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::firstOrCreate(
            ['codigo' => 'CIR-GEN', 'empresa_id' => $empresa->id],
            [
                'nombre'              => 'Cirugía General',
                'descripcion'         => 'Diagnóstico y tratamiento quirúrgico de patologías abdominales y generales.',
                'color'               => '#607D8B',
                'icono'               => 'fa-scalpel',
                'costo_consulta'      => 65.00,
                'duracion_consulta'   => 30,
                'requiere_cita_previa'=> true,
                'status'              => true,
                'sucursal_id'         => $sucursal->id,
            ]
        );

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Cirugía General',
            'descripcion'               => 'Plantilla para evaluación quirúrgica general.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Examen Físico ──────────────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['aspecto_general',    'Aspecto General',         'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general'], false, 'Buen estado general', null, null, 6],
            ['abdomen_inspeccion', 'Abdomen — Inspección',    'textarea', null, false, null, null, 'Distensión, cicatrices, hernias...', 6],
            ['ruidos_intestinales','Ruidos Intestinales',     'select',
                ['Normales', 'Aumentados', 'Disminuidos', 'Ausentes'], false, 'Normales', null, null, 6],
            ['palpacion',          'Palpación',               'textarea', null, false, null, null, 'Blando, depresible, defensa, masas...', 6],
            ['murphy',             'Signo de Murphy',         'select',
                ['Negativo', 'Positivo', 'No evaluado'], false, 'Negativo', null, null, 4],
            ['mcburney',           'Punto de McBurney',       'select',
                ['Negativo', 'Positivo', 'No evaluado'], false, 'Negativo', null, null, 4],
            ['blumberg',           'Signo de Blumberg',       'select',
                ['Negativo', 'Positivo', 'No evaluado'], false, 'Negativo', null, null, 4],
            ['hernias',            'Hernias',                 'select',
                ['Ausentes', 'Inguinal derecha', 'Inguinal izquierda', 'Umbilical', 'Incisional', 'Epigástrica'],
                false, 'Ausentes', null, null, 6],
            ['tacto_rectal',       'Tacto Rectal',            'textarea', null, false, null, null, null, 6],
            ['observaciones_examen','Observaciones',          'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Evaluación Quirúrgica ──────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Evaluación Quirúrgica',
            'icono'        => 'fa-procedures',
            'color'        => '#F44336',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['impresion_diagnostica',  'Impresión Diagnóstica',   'textarea', null, true,  null, null, null, 12],
            ['indicacion_quirurgica',  'Indicación Quirúrgica',   'select',
                ['No quirúrgico — manejo médico', 'Electivo', 'Urgente', 'Emergencia'], false, 'No quirúrgico — manejo médico', null, null, 6],
            ['procedimiento_propuesto','Procedimiento Propuesto', 'textarea', null, false, null, null, 'Laparoscopía, laparotomía...', 6],
            ['riesgo_quirurgico',      'Riesgo Quirúrgico (ASA)', 'select',
                ['ASA I', 'ASA II', 'ASA III', 'ASA IV', 'ASA V', 'No evaluado'], false, 'No evaluado', null, null, 6],
            ['plan_preoperatorio',     'Plan Preoperatorio',      'textarea', null, false, null, null, 'Exámenes, interconsultas...', 6],
            ['proxima_cita',           'Próxima Cita',            'select',
                ['48 horas', '1 semana', '2 semanas', '1 mes', 'Post-operatorio', 'Urgente'], false, null, null, null, 6],
            ['observaciones',          'Observaciones',           'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Cirugía General creada.');
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
