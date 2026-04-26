<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaMedicinaInternaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'MED-INT')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Medicina Interna',
            'descripcion'       => 'Plantilla para consultas de medicina interna del adulto.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
        ]);

        // ── SECCIÓN 1: Revisión por Sistemas (médico interroga) ───────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Revisión por Sistemas',
            'icono'        => 'fa-list-check',
            'color'        => '#FF9800',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['sistema_cardiovascular', 'Cardiovascular',     'textarea', null, false, null, null, 'Palpitaciones, disnea, edemas...', 6],
            ['sistema_respiratorio',   'Respiratorio',       'textarea', null, false, null, null, 'Tos, expectoración, disnea...', 6],
            ['sistema_digestivo',      'Digestivo',          'textarea', null, false, null, null, 'Náuseas, vómitos, dolor abdominal...', 6],
            ['sistema_urinario',       'Urinario',           'textarea', null, false, null, null, 'Disuria, poliuria, hematuria...', 6],
            ['sistema_neurologico',    'Neurológico',        'textarea', null, false, null, null, 'Cefalea, mareos, parestesias...', 6],
            ['sistema_musculoesq',     'Musculoesquelético', 'textarea', null, false, null, null, 'Artralgias, mialgias...', 6],
        ]);

        // ── SECCIÓN 2: Examen Físico ──────────────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#4CAF50',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['aspecto_general',    'Aspecto General',          'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general'], false, 'Buen estado general', null, null, 6],
            ['estado_conciencia',  'Estado de Conciencia',     'select',
                ['Consciente', 'Somnoliento', 'Estuporoso', 'Comatoso'], false, 'Consciente', null, null, 6],
            ['piel_mucosas',       'Piel y Mucosas',           'select',
                ['Normales', 'Pálidas', 'Ictéricas', 'Cianóticas', 'Deshidratadas'], false, 'Normales', null, null, 6],
            ['ganglios',           'Ganglios Linfáticos',      'select',
                ['No palpables', 'Adenopatías cervicales', 'Adenopatías axilares', 'Adenopatías inguinales', 'Generalizadas'],
                false, 'No palpables', null, null, 6],
            ['auscultacion_card',  'Auscultación Cardíaca',    'textarea', null, false, null, null, 'Ruidos cardíacos, soplos...', 6],
            ['auscultacion_pulm',  'Auscultación Pulmonar',    'textarea', null, false, null, null, 'MV, estertores, sibilancias...', 6],
            ['abdomen',            'Abdomen',                  'textarea', null, false, null, null, 'Blando, depresible, doloroso...', 6],
            ['extremidades',       'Extremidades',             'textarea', null, false, null, null, 'Edemas, pulsos, varices...', 6],
            ['examen_neurologico', 'Examen Neurológico Básico','textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 3: Impresión Diagnóstica y Plan ───────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Impresión Diagnóstica y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#9C27B0',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_diagnostico',      'Plan Diagnóstico',      'textarea', null, false, null, null, 'Exámenes solicitados...', 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['pronostico',            'Pronóstico',            'select',
                ['Bueno', 'Reservado', 'Malo', 'Grave'], false, 'Bueno', null, null, 6],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', 'Según evolución'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Medicina Interna creada.');
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
