<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaOtorrinolaringologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'ORL')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Otorrinolaringología',
            'descripcion'               => 'Plantilla para evaluación de oído, nariz y garganta.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Examen ORL ─────────────────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen ORL',
            'icono'        => 'fa-stethoscope',
            'color'        => '#795548',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['otoscopia_od',        'Otoscopía OD',             'textarea', null, false, null, null, 'CAE, membrana timpánica...', 6],
            ['otoscopia_oi',        'Otoscopía OI',             'textarea', null, false, null, null, 'CAE, membrana timpánica...', 6],
            ['rinoscopia',          'Rinoscopía',               'textarea', null, false, null, null, 'Tabique, cornetes, mucosa...', 6],
            ['orofaringe',          'Orofaringe',               'textarea', null, false, null, null, 'Amígdalas, pilares, úvula...', 6],
            ['laringoscopia',       'Laringoscopía',            'textarea', null, false, null, null, null, 6],
            ['cuello',              'Cuello / Adenopatías',     'textarea', null, false, null, null, null, 6],
            ['weber',               'Weber',                    'select',
                ['No realizado', 'Centrado', 'Lateraliza a OD', 'Lateraliza a OI'], false, 'No realizado', null, null, 4],
            ['rinne_od',            'Rinne OD',                 'select',
                ['No realizado', 'Positivo', 'Negativo'], false, 'No realizado', null, null, 4],
            ['rinne_oi',            'Rinne OI',                 'select',
                ['No realizado', 'Positivo', 'Negativo'], false, 'No realizado', null, null, 4],
            ['observaciones_examen','Observaciones',            'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Diagnóstico y Plan ─────────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#3F51B5',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['indicacion_quirurgica', 'Indicación Quirúrgica', 'select',
                ['No', 'Electiva', 'Urgente'], false, 'No', null, null, 6],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Otorrinolaringología creada.');
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
