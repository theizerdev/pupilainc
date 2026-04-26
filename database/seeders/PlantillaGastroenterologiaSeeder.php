<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaGastroenterologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'GASTRO')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Gastroenterología',
            'descripcion'       => 'Plantilla para evaluación del sistema digestivo.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
        ]);

        // ── SECCIÓN 1: Examen Abdominal ───────────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Abdominal',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['inspeccion',         'Inspección',            'textarea', null, false, null, null, 'Distensión, cicatrices, hernias...', 12],
            ['ruidos_intestinales','Ruidos Intestinales',   'select',
                ['Normales', 'Aumentados', 'Disminuidos', 'Ausentes'], false, 'Normales', null, null, 6],
            ['palpacion',          'Palpación',             'textarea', null, false, null, null, 'Blando, depresible, masas, defensa...', 6],
            ['hepatomegalia',      'Hepatomegalia',         'select',
                ['Ausente', 'Leve', 'Moderada', 'Severa'], false, 'Ausente', null, null, 4],
            ['esplenomegalia',     'Esplenomegalia',        'select',
                ['Ausente', 'Leve', 'Moderada', 'Severa'], false, 'Ausente', null, null, 4],
            ['ascitis',            'Ascitis',               'select',
                ['Ausente', 'Leve', 'Moderada', 'Severa'], false, 'Ausente', null, null, 4],
            ['murphy',             'Signo de Murphy',       'select',
                ['Negativo', 'Positivo', 'No evaluado'], false, 'Negativo', null, null, 4],
            ['mcburney',           'Punto de McBurney',     'select',
                ['Negativo', 'Positivo', 'No evaluado'], false, 'Negativo', null, null, 4],
            ['tacto_rectal',       'Tacto Rectal',          'textarea', null, false, null, null, null, 4],
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
            ['dieta_recomendada',     'Dieta Recomendada',     'textarea', null, false, null, null, null, 6],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Gastroenterología creada.');
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
