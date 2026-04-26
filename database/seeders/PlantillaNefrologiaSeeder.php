<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaNefrologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'NEFRO')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Nefrología',
            'descripcion'       => 'Plantilla para evaluación nefrológica.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
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
            ['aspecto_general',    'Aspecto General',          'select',
                ['Buen estado general', 'Regular estado general', 'Mal estado general'], false, 'Buen estado general', null, null, 6],
            ['piel_mucosas',       'Piel y Mucosas',           'select',
                ['Normales', 'Pálidas', 'Ictéricas', 'Terrosas'], false, 'Normales', null, null, 6],
            ['edema_grado',        'Grado de Edema',           'select',
                ['Ausente', '+/4', '++/4', '+++/4', '++++/4'], false, 'Ausente', null, null, 6],
            ['puño_percusion',     'Puño-Percusión',           'select',
                ['Negativa bilateral', 'Positiva derecha', 'Positiva izquierda', 'Positiva bilateral'],
                false, 'Negativa bilateral', null, null, 6],
            ['auscultacion_card',  'Auscultación Cardíaca',    'textarea', null, false, null, null, null, 6],
            ['tension_arterial_obs','TA en ambos brazos',      'text',     null, false, null, null, 'Ej: 140/90 D / 138/88 I', 6],
            ['observaciones_examen','Observaciones',           'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Función Renal y Laboratorio ────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Función Renal y Laboratorio',
            'icono'        => 'fa-flask',
            'color'        => '#3F51B5',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['creatinina',        'Creatinina',              'number', null, false, null, 'mg/dL', null, 4],
            ['urea',              'Urea / BUN',              'number', null, false, null, 'mg/dL', null, 4],
            ['tasa_filtracion',   'TFG estimada',            'number', null, false, null, 'ml/min/1.73m²', null, 4],
            ['estadio_erc',       'Estadio ERC',             'select',
                ['No aplica', 'G1 (≥90)', 'G2 (60-89)', 'G3a (45-59)', 'G3b (30-44)', 'G4 (15-29)', 'G5 (<15)'],
                false, 'No aplica', null, null, 6],
            ['potasio',           'Potasio',                 'number', null, false, null, 'mEq/L', null, 3],
            ['sodio',             'Sodio',                   'number', null, false, null, 'mEq/L', null, 3],
            ['hemoglobina',       'Hemoglobina',             'number', null, false, null, 'g/dL', null, 3],
            ['proteinuria_24h',   'Proteinuria 24h',         'number', null, false, null, 'mg/24h', null, 3],
            ['otros_laboratorios','Otros Laboratorios',      'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 3: Diagnóstico y Plan ─────────────────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#F44336',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica',     'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',          'textarea', null, false, null, null, null, 12],
            ['restricciones_dieta',   'Restricciones Dietéticas',  'textarea', null, false, null, null, 'Potasio, fósforo, proteínas, líquidos...', 6],
            ['proxima_cita',          'Próxima Cita',              'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',             'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Nefrología creada.');
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
