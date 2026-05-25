<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaOrtopediaPediatricaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::where('codigo', 'ORTO-PED')
            ->where('empresa_id', $empresa->id)->firstOrFail();

        // Eliminar plantillas existentes para evitar duplicados
        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'           => $especialidad->id,
            'nombre'                    => 'Consulta de Ortopedia Pediátrica',
            'descripcion'               => 'Plantilla para evaluación ortopédica en niños.',
            'activo'                    => true,
            'empresa_id'                => $empresa->id,
            'sucursal_id'               => $sucursal->id,
            'pasos_habilitados'         => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'             => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
            'usar_wizard_en_consultorio' => true,
        ]);

        // ── SECCIÓN 1: Antecedentes Ortopédicos ─────────────────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Antecedentes Ortopédicos',
            'icono'        => 'fa-history',
            'color'        => '#FF5722',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['antecedentes_personales', 'Antecedentes Personales', 'textarea', null, false, null, null, null, 12],
            ['antecedentes_familiares', 'Antecedentes Familiares', 'textarea', null, false, null, null, null, 12],
            ['traumas_previos',         'Traumas Previos',          'textarea', null, false, null, null, null, 12],
            ['cirugias_ortopedicas',    'Cirugías Ortopédicas Previas', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 2: Evaluación Ortopédica ─────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Evaluación Ortopédica',
            'icono'        => 'fa-bone',
            'color'        => '#FF5722',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['region_afectada',    'Región Afectada', 'select',
                ['Columna cervical', 'Columna dorsal', 'Columna lumbar',
                 'Hombro derecho', 'Hombro izquierdo',
                 'Codo derecho', 'Codo izquierdo',
                 'Muñeca derecha', 'Muñeca izquierda',
                 'Cadera derecha', 'Cadera izquierda',
                 'Rodilla derecha', 'Rodilla izquierda',
                 'Tobillo derecho', 'Tobillo izquierdo',
                 'Pie derecho', 'Pie izquierdo'],
                true, null, null, null, 6],
            ['lado',               'Lado',            'radio', ['Derecho', 'Izquierdo', 'Bilateral'], false, 'Derecho', null, null, 3],
            ['tiempo_evolucion',   'Tiempo de Evolución', 'text', null, false, null, null, null, 3],
            ['dolor_eva',          'Dolor (EVA 0-10)', 'range', null, false, '0', null, null, 6],
            ['limitacion_funcional', 'Limitación Funcional', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 3: Examen Físico ─────────────────
        $s3 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico Ortopédico',
            'icono'        => 'fa-stethoscope',
            'color'        => '#E64A19',
            'orden'        => 3,
        ]);

        $this->campos($s3->id, [
            ['inspeccion',           'Inspección',             'textarea', null, false, null, null, null, 12],
            ['palpacion',            'Palpación',              'textarea', null, false, null, null, null, 12],
            ['rango_movimiento',     'Rango de Movimiento',    'textarea', null, false, null, null, null, 12],
            ['fuerza_muscular',      'Fuerza Muscular',        'select',
                ['0/5 - Sin contracción', '1/5 - Contracción sin movimiento', '2/5 - Movimiento sin gravedad',
                 '3/5 - Contra gravedad', '4/5 - Contra resistencia parcial', '5/5 - Normal'],
                false, '5/5 - Normal', null, null, 6],
            ['reflejos_osteotendinosos', 'Reflejos Osteotendinosos', 'select',
                ['Normales', 'Disminuidos', 'Aumentados', 'Ausentes'], false, 'Normales', null, null, 6],
        ]);

        // ── SECCIÓN 4: Estudios de Imagen ─────────────────
        $s4 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Estudios de Imagen',
            'icono'        => 'fa-x-ray',
            'color'        => '#D84315',
            'orden'        => 4,
        ]);

        $this->campos($s4->id, [
            ['radiografias',         'Radiografías Realizadas', 'checkbox',
                ['Rx simple AP/Lateral', 'Rx comparativa', 'Rx de pie completo', 'Rx columna completa'],
                false, null, null, null, 12],
            ['otros_estudios',       'Otros Estudios', 'checkbox',
                ['TAC', 'RMN', 'Ecografía musculoesquelética', 'Gammagrafía ósea', 'DEXA', 'Estudio de marcha'],
                false, null, null, null, 12],
            ['hallazgos',            'Hallazgos Radiológicos', 'textarea', null, false, null, null, null, 12],
        ]);

        // ── SECCIÓN 5: Diagnóstico y Plan ─────────────────
        $s5 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Diagnóstico y Plan Terapéutico',
            'icono'        => 'fa-notes-medical',
            'color'        => '#BF360C',
            'orden'        => 5,
        ]);

        $this->campos($s5->id, [
            ['diagnostico_principal', 'Diagnóstico Principal', 'text', null, true, null, null, null, 12],
            ['diagnosticos_secundarios', 'Diagnósticos Secundarios', 'textarea', null, false, null, null, null, 12],
            ['plan_terapeutico',      'Plan Terapéutico', 'textarea', null, false, null, null, null, 12],
            ['tratamiento_quirurgico', '¿Requiere Tratamiento Quirúrgico?', 'radio', ['Sí', 'No', 'A evaluar'], false, 'No', null, null, 6],
            ['inmovilizacion',        'Inmovilización Requerida', 'select',
                ['No requiere', 'Vendaje', 'Férula', 'Yeso', 'Cabestrillo', 'Collarín', 'Corsé'],
                false, 'No requiere', null, null, 6],
        ]);

        $this->command->info('  ✓ Ortopedia Pediátrica — Plantilla creada con 5 secciones');
    }

    private function campos(int $seccionId, array $campos): void
    {
        foreach ($campos as $campo) {
            [$clave, $etiqueta, $tipo, $opciones, $obligatorio, $valor_defecto, $unidad, $placeholder, $ancho] = $campo;

            PlantillaCampo::create([
                'seccion_id'      => $seccionId,
                'nombre_campo'    => $clave,
                'etiqueta'        => $etiqueta,
                'tipo'            => $tipo,
                'opciones'        => $opciones ? json_encode($opciones) : null,
                'obligatorio'     => $obligatorio,
                'valor_defecto'   => $valor_defecto,
                'unidad'          => $unidad,
                'placeholder'     => $placeholder,
                'ancho_columnas'  => $ancho ?? 6,
                'orden'           => PlantillaCampo::where('seccion_id', $seccionId)->count() + 1,
            ]);
        }
    }
}
