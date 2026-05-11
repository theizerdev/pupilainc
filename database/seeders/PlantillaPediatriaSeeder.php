<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaPediatriaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'PEDIAT')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Pediatría',
            'descripcion'               => 'Plantilla para evaluación pediátrica.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Datos del Desarrollo (médico verifica) ─────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Datos Perinatales y del Desarrollo',
            'icono'        => 'fa-child',
            'color'        => '#4CAF50',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['tipo_parto',            'Tipo de Parto',              'select',
                ['Eutócico', 'Cesárea', 'Instrumentado'], false, 'Eutócico', null, null, 4],
            ['edad_gestacional',      'Edad Gestacional al Nacer',  'number', null, false, null, 'semanas', null, 4],
            ['peso_nacer',            'Peso al Nacer',              'number', null, false, null, 'kg', null, 4],
            ['desarrollo_psicomotor', 'Desarrollo Psicomotor',      'select',
                ['Normal', 'Retraso leve', 'Retraso moderado', 'Retraso severo'], false, 'Normal', null, null, 6],
            ['estadio_tanner',        'Estadio de Tanner',          'select',
                ['No aplica', 'I', 'II', 'III', 'IV', 'V'], false, 'No aplica', null, null, 6],
        ]);

        // ── SECCIÓN 2: Examen Físico Pediátrico ───────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['aspecto_general',    'Aspecto General',         'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general', 'Irritable', 'Decaído'],
                false, 'Buen estado general', null, null, 6],
            ['hidratacion',        'Hidratación',             'select',
                ['Bien hidratado', 'Deshidratación leve', 'Deshidratación moderada', 'Deshidratación severa'],
                false, 'Bien hidratado', null, null, 6],
            ['piel_mucosas',       'Piel y Mucosas',          'select',
                ['Normales', 'Pálidas', 'Ictéricas', 'Cianóticas', 'Exantema'], false, 'Normales', null, null, 6],
            ['fontanela',          'Fontanela (lactantes)',   'select',
                ['No aplica', 'Normotensa', 'Abombada', 'Deprimida'], false, 'No aplica', null, null, 6],
            ['orofaringe',         'Orofaringe',              'textarea', null, false, null, null, 'Amígdalas, faringe, dentición...', 6],
            ['adenopatias',        'Adenopatías',             'select',
                ['Ausentes', 'Cervicales', 'Axilares', 'Inguinales', 'Generalizadas'], false, 'Ausentes', null, null, 6],
            ['auscultacion_card',  'Auscultación Cardíaca',   'textarea', null, false, null, null, null, 6],
            ['auscultacion_pulm',  'Auscultación Pulmonar',   'textarea', null, false, null, null, null, 6],
            ['abdomen',            'Abdomen',                 'textarea', null, false, null, null, null, 6],
            ['genitales',          'Genitales',               'textarea', null, false, null, null, null, 6],
            ['observaciones_examen','Observaciones',          'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 3: Diagnóstico y Plan ─────────────────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#9C27B0',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica',              'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',                   'textarea', null, false, null, null, null, 12],
            ['indicaciones_padres',   'Indicaciones para Padres/Cuidadores','textarea', null, false, null, null, null, 12],
            ['proxima_cita',          'Próxima Cita',                       'select',
                ['48 horas', '1 semana', '2 semanas', '1 mes', '2 meses', '6 meses', '1 año'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',                      'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Pediatría creada.');
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
