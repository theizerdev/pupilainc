<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaMedicinaGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'MED-GEN')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Medicina General',
            'descripcion'               => 'Plantilla estándar para consultas de medicina general.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Examen Físico (médico evalúa) ──────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['aspecto_general',   'Aspecto General',      'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general'], false, 'Buen estado general', null, null, 6],
            ['estado_conciencia', 'Estado de Conciencia', 'select',
                ['Consciente', 'Somnoliento', 'Estuporoso', 'Comatoso'], false, 'Consciente', null, null, 6],
            ['piel_mucosas',      'Piel y Mucosas',       'select',
                ['Normales', 'Pálidas', 'Ictéricas', 'Cianóticas', 'Deshidratadas'], false, 'Normales', null, null, 6],
            ['hidratacion',       'Hidratación',          'select',
                ['Bien hidratado', 'Deshidratación leve', 'Deshidratación moderada', 'Deshidratación severa'], false, 'Bien hidratado', null, null, 6],
            ['cabeza_cuello',     'Cabeza y Cuello',      'textarea', null, false, null, null, null, 6],
            ['torax_pulmones',    'Tórax y Pulmones',     'textarea', null, false, null, null, null, 6],
            ['abdomen',           'Abdomen',              'textarea', null, false, null, null, null, 6],
            ['extremidades',      'Extremidades',         'textarea', null, false, null, null, null, 6],
            ['observaciones_examen', 'Observaciones del Examen', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Diagnóstico y Plan ─────────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#9C27B0',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_tratamiento',      'Plan de Tratamiento',   'textarea', null, false, null, null, null, 12],
            ['pronostico',            'Pronóstico',            'select',
                ['Bueno', 'Reservado', 'Malo', 'Grave'], false, 'Bueno', null, null, 6],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', 'Según evolución'], false, null, null, null, 6],
            ['observaciones_finales', 'Observaciones Finales', 'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Medicina General creada.');
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
