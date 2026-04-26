<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaNeuroLogiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'NEURO')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Neurología',
            'descripcion'       => 'Plantilla para evaluación neurológica.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
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
            ['estado_mental',      'Estado Mental',            'select',
                ['Normal', 'Confuso', 'Desorientado', 'Agitado', 'Estuporoso'], false, 'Normal', null, null, 6],
            ['glasgow',            'Escala de Glasgow',        'number', null, false, null, '/15', null, 6],
            ['pares_craneales',    'Pares Craneales',          'textarea', null, false, null, null, 'Alteraciones por par...', 12],
            ['fuerza_muscular',    'Fuerza Muscular',          'select',
                ['5/5 Normal', '4/5 Leve debilidad', '3/5 Moderada', '2/5 Severa', '1/5 Mínima', '0/5 Plejia'],
                false, '5/5 Normal', null, null, 6],
            ['tono_muscular',      'Tono Muscular',            'select',
                ['Normal', 'Hipotonía', 'Hipertonía espástica', 'Hipertonía rígida'], false, 'Normal', null, null, 6],
            ['reflejos',           'Reflejos Osteotendinosos', 'select',
                ['Normales', 'Hiperreflexia', 'Hiporreflexia', 'Arreflexia'], false, 'Normales', null, null, 6],
            ['babinski',           'Signo de Babinski',        'select',
                ['Negativo bilateral', 'Positivo derecho', 'Positivo izquierdo', 'Positivo bilateral'],
                false, 'Negativo bilateral', null, null, 6],
            ['coordinacion',       'Coordinación',             'select',
                ['Normal', 'Dismetría', 'Disdiadococinesia', 'Ataxia'], false, 'Normal', null, null, 6],
            ['sensibilidad',       'Sensibilidad',             'textarea', null, false, null, null, 'Táctil, dolorosa, propioceptiva...', 6],
            ['marcha',             'Marcha',                   'textarea', null, false, null, null, null, 6],
            ['signos_meningeos',   'Signos Meníngeos',         'select',
                ['Negativos', 'Rigidez de nuca', 'Kernig +', 'Brudzinski +'], false, 'Negativos', null, null, 6],
            ['observaciones_examen','Observaciones',           'textarea', null, false, null, null, null, 6],
        ]);

        // ── SECCIÓN 2: Diagnóstico y Plan ─────────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#F44336',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', 'Urgente'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Neurología creada.');
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
