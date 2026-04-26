<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaSeccion;
use App\Models\PlantillaCampo;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class PlantillaMastologiaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = Empresa::first();
        $sucursal = Sucursal::first();

        $especialidad = Especialidad::firstOrCreate(
            ['codigo' => 'MASTOL', 'empresa_id' => $empresa->id],
            [
                'nombre'              => 'Mastología',
                'descripcion'         => 'Diagnóstico y tratamiento de enfermedades de la mama.',
                'color'               => '#EC407A',
                'icono'               => 'fa-ribbon',
                'costo_consulta'      => 60.00,
                'duracion_consulta'   => 30,
                'requiere_cita_previa'=> true,
                'status'              => true,
                'sucursal_id'         => $sucursal->id,
            ]
        );

        EspecialidadPlantilla::where('especialidad_id', $especialidad->id)->delete();

        $plantilla = EspecialidadPlantilla::create([
            'especialidad_id'   => $especialidad->id,
            'nombre'            => 'Consulta de Mastología',
            'descripcion'       => 'Plantilla para evaluación mastológica.',
            'activo'            => true,
            'empresa_id'        => $empresa->id,
            'sucursal_id'       => $sucursal->id,
            'pasos_habilitados' => ['signos_vitales', 'cuestionario', 'evaluacion', 'estudios', 'tratamientos', 'reposo'],
            'estados_flujo'     => ['por_llegar', 'sala_espera', 'en_enfermeria', 'en_consultorio', 'en_estudio', 'finalizada'],
        ]);

        // ── SECCIÓN 1: Datos Gineco-Obstétricos (médico verifica/completa) ────
        $s1 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Datos Gineco-Obstétricos',
            'icono'        => 'fa-venus',
            'color'        => '#AD1457',
            'orden'        => 1,
        ]);

        $this->campos($s1->id, [
            ['menarquia',           'Menarquia (edad)',          'number', null, false, null, 'años', null, 4],
            ['menopausia',          'Menopausia',                'select',
                ['No', 'Sí — natural', 'Sí — quirúrgica'], false, 'No', null, null, 4],
            ['gestas',              'Gestas',                    'number', null, false, null, null, null, 2],
            ['partos',              'Partos',                    'number', null, false, null, null, null, 2],
            ['resultado_mamografia','Resultado Mamografía',      'select',
                ['No realizada', 'BIRADS 0', 'BIRADS 1', 'BIRADS 2', 'BIRADS 3', 'BIRADS 4', 'BIRADS 5', 'BIRADS 6'],
                false, 'No realizada', null, null, 6],
            ['resultado_eco',       'Resultado Eco. Mamario',    'select',
                ['No realizado', 'BIRADS 0', 'BIRADS 1', 'BIRADS 2', 'BIRADS 3', 'BIRADS 4', 'BIRADS 5', 'BIRADS 6'],
                false, 'No realizado', null, null, 6],
        ]);

        // ── SECCIÓN 2: Examen Físico Mamario ──────────────────────────────────
        $s2 = PlantillaSeccion::create([
            'plantilla_id' => $plantilla->id,
            'nombre'       => 'Examen Físico Mamario',
            'icono'        => 'fa-stethoscope',
            'color'        => '#FF9800',
            'orden'        => 2,
        ]);

        $this->campos($s2->id, [
            ['inspeccion_estatica',  'Inspección Estática',       'textarea', null, false, null, null, 'Simetría, contorno, piel, pezones...', 12],
            ['inspeccion_dinamica',  'Inspección Dinámica',       'textarea', null, false, null, null, 'Maniobras de contracción...', 12],
            ['palpacion_md',         'Palpación Mama Derecha',    'textarea', null, false, null, null, 'Nódulos, consistencia, sensibilidad...', 6],
            ['palpacion_mi',         'Palpación Mama Izquierda',  'textarea', null, false, null, null, null, 6],
            ['palpacion_axilar_d',   'Axila Derecha',             'select',
                ['Sin adenopatías', 'Adenopatías palpables — móviles', 'Adenopatías palpables — fijas'],
                false, 'Sin adenopatías', null, null, 6],
            ['palpacion_axilar_i',   'Axila Izquierda',           'select',
                ['Sin adenopatías', 'Adenopatías palpables — móviles', 'Adenopatías palpables — fijas'],
                false, 'Sin adenopatías', null, null, 6],
            ['expresion_pezon_d',    'Expresión Pezón Derecho',   'select',
                ['Sin secreción', 'Secreción serosa', 'Secreción lechosa', 'Secreción sanguinolenta'],
                false, 'Sin secreción', null, null, 6],
            ['expresion_pezon_i',    'Expresión Pezón Izquierdo', 'select',
                ['Sin secreción', 'Secreción serosa', 'Secreción lechosa', 'Secreción sanguinolenta'],
                false, 'Sin secreción', null, null, 6],
            ['observaciones_examen', 'Observaciones',             'textarea', null, false, null, null, null, 12],
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
            ['impresion_diagnostica', 'Impresión Diagnóstica',   'textarea', null, true,  null, null, null, 12],
            ['categoria_birads',      'Categoría BIRADS Global', 'select',
                ['No aplica', 'BIRADS 0', 'BIRADS 1', 'BIRADS 2', 'BIRADS 3', 'BIRADS 4A', 'BIRADS 4B', 'BIRADS 4C', 'BIRADS 5', 'BIRADS 6'],
                false, 'No aplica', null, null, 6],
            ['conducta',              'Conducta',                'select',
                ['Observación', 'Estudios complementarios', 'Biopsia', 'Cirugía electiva', 'Cirugía urgente', 'Interconsulta oncología'],
                false, 'Observación', null, null, 6],
            ['plan_terapeutico',      'Plan Terapéutico',        'textarea', null, false, null, null, null, 12],
            ['proxima_cita',          'Próxima Cita',            'select',
                ['1 semana', '2 semanas', '1 mes', '3 meses', '6 meses', '1 año', 'Según resultado biopsia'],
                false, null, null, null, 6],
            ['observaciones',         'Observaciones',           'textarea', null, false, null, null, null, 6],
        ]);

        $this->command->info('✓ Plantilla Mastología creada.');
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
