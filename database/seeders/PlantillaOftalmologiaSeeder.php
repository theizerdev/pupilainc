<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaOftalmologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'OFTAL')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Oftalmología',
            'descripcion'               => 'Plantilla completa para evaluación oftalmológica.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => [
                'por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio',
                'en_consultorio_optometrista', 'en_gotas', 'dilatado', 'en_optica', 'en_estudio', 'finalizada',
            ],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Agudeza Visual ─────────────────────────────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Agudeza Visual',
            'icono'        => 'fa-eye',
            'color'        => '#2196F3',
            'orden'        => 1,
        ]);

        $opciones_av = ['20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/80', '20/100', '20/200', 'CF', 'MM', 'PL', 'NPL'];

        $this->campos($s1->id, [
            ['av_od_sc',   'AV OD Sin Corrección',  'select', $opciones_av, false, null, null, null, 3],
            ['av_oi_sc',   'AV OI Sin Corrección',  'select', $opciones_av, false, null, null, null, 3],
            ['av_od_cc',   'AV OD Con Corrección',  'select', $opciones_av, false, null, null, null, 3],
            ['av_oi_cc',   'AV OI Con Corrección',  'select', $opciones_av, false, null, null, null, 3],
            ['av_od_ph',   'AV OD Pinhole',         'select', $opciones_av, false, null, null, null, 3],
            ['av_oi_ph',   'AV OI Pinhole',         'select', $opciones_av, false, null, null, null, 3],
            ['av_cerca_od','AV Cerca OD',           'select', $opciones_av, false, null, null, null, 3],
            ['av_cerca_oi','AV Cerca OI',           'select', $opciones_av, false, null, null, null, 3],
        ]);

        // ── SECCIÓN 2: Refracción ─────────────────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Refracción',
            'icono'        => 'fa-glasses',
            'color'        => '#9C27B0',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['ref_od_esfera',   'OD Esfera',   'number', null, false, null, 'D', null, 2],
            ['ref_od_cilindro', 'OD Cilindro', 'number', null, false, null, 'D', null, 2],
            ['ref_od_eje',      'OD Eje',      'number', null, false, null, '°', null, 2],
            ['ref_od_adicion',  'OD Adición',  'number', null, false, null, 'D', null, 2],
            ['ref_oi_esfera',   'OI Esfera',   'number', null, false, null, 'D', null, 2],
            ['ref_oi_cilindro', 'OI Cilindro', 'number', null, false, null, 'D', null, 2],
            ['ref_oi_eje',      'OI Eje',      'number', null, false, null, '°', null, 2],
            ['ref_oi_adicion',  'OI Adición',  'number', null, false, null, 'D', null, 2],
            ['tipo_refraccion',  'Tipo',       'select',
                ['Emétrope', 'Miopía', 'Hipermetropía', 'Astigmatismo', 'Presbicia', 'Mixto'], false, null, null, null, 4],
            ['observaciones_refraccion', 'Observaciones', 'textarea', null, false, null, null, null, 8],
        ]);

        // ── SECCIÓN 3: Biomicroscopía ─────────────────────────────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Biomicroscopía',
            'icono'        => 'fa-microscope',
            'color'        => '#FF9800',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['cornea_od',      'Córnea OD',            'textarea', null, false, null, null, 'Transparente, úlceras, opacidades...', 6],
            ['cornea_oi',      'Córnea OI',            'textarea', null, false, null, null, null, 6],
            ['ca_od',          'Cámara Anterior OD',   'select',
                ['Profunda y clara', 'Poco profunda', 'Tyndall +', 'Hipopión', 'Hifema'], false, 'Profunda y clara', null, null, 6],
            ['ca_oi',          'Cámara Anterior OI',   'select',
                ['Profunda y clara', 'Poco profunda', 'Tyndall +', 'Hipopión', 'Hifema'], false, 'Profunda y clara', null, null, 6],
            ['cristalino_od',  'Cristalino OD',        'select',
                ['Transparente', 'Catarata incipiente', 'Catarata moderada', 'Catarata avanzada', 'Pseudofáquico', 'Afáquico'],
                false, 'Transparente', null, null, 6],
            ['cristalino_oi',  'Cristalino OI',        'select',
                ['Transparente', 'Catarata incipiente', 'Catarata moderada', 'Catarata avanzada', 'Pseudofáquico', 'Afáquico'],
                false, 'Transparente', null, null, 6],
        ]);

        // ── SECCIÓN 4: Presión Intraocular ────────────────────────────────────
        $s4 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Presión Intraocular',
            'icono'        => 'fa-tachometer-alt',
            'color'        => '#F44336',
            'orden'        => 4,
        ]);

        $this->campos($s4->id, [
            ['pio_od',          'PIO OD',        'number',   null, false, null, 'mmHg', null, 3],
            ['pio_oi',          'PIO OI',        'number',   null, false, null, 'mmHg', null, 3],
            ['metodo_pio',      'Método',        'select',
                ['Aplanación', 'No contacto', 'Icare', 'Digital'], false, 'No contacto', null, null, 3],
            ['hora_pio',        'Hora de Toma',  'text',     null, false, null, null, 'Ej: 09:30', 3],
            ['observaciones_pio','Observaciones','textarea',  null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 5: Fondo de Ojo ───────────────────────────────────────────
        $s5 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Fondo de Ojo',
            'icono'        => 'fa-eye',
            'color'        => '#3F51B5',
            'orden'        => 5,
        ]);

        $this->campos($s5->id, [
            ['papila_od',               'Papila OD',             'textarea', null, false, null, null, 'Bordes, excavación, color...', 6],
            ['papila_oi',               'Papila OI',             'textarea', null, false, null, null, null, 6],
            ['macula_od',               'Mácula OD',             'textarea', null, false, null, null, 'Reflejo foveal, drusas, edema...', 6],
            ['macula_oi',               'Mácula OI',             'textarea', null, false, null, null, null, 6],
            ['vasos_od',                'Vasos OD',              'textarea', null, false, null, null, 'Relación A/V, cruces, tortuosidad...', 6],
            ['vasos_oi',                'Vasos OI',              'textarea', null, false, null, null, null, 6],
            ['retina_periferica_od',    'Retina Periférica OD',  'textarea', null, false, null, null, null, 6],
            ['retina_periferica_oi',    'Retina Periférica OI',  'textarea', null, false, null, null, null, 6],
            ['dilatacion',              'Dilatación Pupilar',    'select',
                ['No realizada', 'Realizada — sin alteraciones', 'Realizada — con alteraciones'], false, 'No realizada', null, null, 12],
        ]);

        // ── SECCIÓN 6: Diagnóstico y Plan ─────────────────────────────────────
        $s6 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan',
            'icono'        => 'fa-clipboard-list',
            'color'        => '#009688',
            'orden'        => 6,
        ]);

        $this->campos($s6->id, [
            ['impresion_diagnostica', 'Impresión Diagnóstica', 'textarea', null, true,  null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico',      'textarea', null, false, null, null, null, 12],
            ['indicacion_optica',     'Indicación Óptica',     'select',
                ['No requiere', 'Lentes monofocales', 'Lentes bifocales', 'Lentes progresivos', 'Lentes de contacto', 'Cirugía refractiva'],
                false, 'No requiere', null, null, 6],
            ['proxima_cita',          'Próxima Cita',          'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', '1 año'], false, null, null, null, 6],
            ['observaciones',         'Observaciones',         'textarea', null, false, null, null, null, 12],
        ]);

        $this->command->info('✓ Plantilla Oftalmología creada.');
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
