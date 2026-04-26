<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaGinecologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'GINECO')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Ginecología',
            'descripcion'       => 'Plantilla para evaluación ginecológica.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
        ]);

        // ── SECCIÓN 1: Datos Gineco-Obstétricos (médico completa/verifica) ────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Datos Gineco-Obstétricos',
            'icono'        => 'fa-venus',
            'color'        => '#E91E63',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['menarquia',          'Menarquia (edad)',               'number', null, false, null, 'años', null, 4],
            ['ciclo_menstrual',    'Ciclo Menstrual',                'select',
                ['Regular', 'Irregular'], false, 'Regular', null, null, 4],
            ['duracion_ciclo',     'Duración del Ciclo',             'text',   null, false, null, 'días', 'Ej: 28 días', 4],
            ['fum',                'FUM',                            'date',   null, false, null, null, null, 4],
            ['gestas',             'Gestas',                         'number', null, false, null, null, null, 2],
            ['partos',             'Partos',                         'number', null, false, null, null, null, 2],
            ['cesareas',           'Cesáreas',                       'number', null, false, null, null, null, 2],
            ['abortos',            'Abortos',                        'number', null, false, null, null, null, 2],
            ['ultimo_pap',         'Último Papanicolaou',            'date',   null, false, null, null, null, 4],
            ['resultado_pap',      'Resultado Pap',                  'select',
                ['Normal', 'ASCUS', 'LSIL', 'HSIL', 'Carcinoma', 'No realizado'], false, 'No realizado', null, null, 4],
            ['ultima_mamografia',  'Última Mamografía',              'date',   null, false, null, null, null, 4],
        ]);

        // ── SECCIÓN 2: Examen Físico Ginecológico ─────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico Ginecológico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['mamas',              'Examen de Mamas',           'textarea', null, false, null, null, 'Simétricas, nódulos, secreción...', 12],
            ['abdomen',            'Abdomen',                   'textarea', null, false, null, null, 'Blando, depresible, masas...', 6],
            ['genitales_externos', 'Genitales Externos',        'textarea', null, false, null, null, null, 6],
            ['especuloscopia',     'Especuloscopía',            'textarea', null, false, null, null, 'Cérvix, vagina, secreciones...', 12],
            ['tacto_vaginal',      'Tacto Vaginal / Bimanual',  'textarea', null, false, null, null, 'Útero, anexos, dolor...', 12],
            ['observaciones_examen', 'Observaciones',           'textarea', null, false, null, null, null, 12],
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
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', '1 año'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Ginecología creada.');
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
