<?php

namespace Database\Seeders;

use App\Models\Baremo;
use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class BaremoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresa = Empresa::first();
        $sucursal = Sucursal::first();

        if (!$empresa || !$sucursal) {
            $this->command->error('Se requiere al menos una empresa y una sucursal.');
            return;
        }

        // Obtener todas las especialidades existentes
        $especialidades = Especialidad::where('empresa_id', $empresa->id)->get();

        if ($especialidades->isEmpty()) {
            $this->command->error('No hay especialidades registradas. Ejecuta primero EspecialidadesMedicasSeeder.');
            return;
        }

        $totalCreados = 0;

        foreach ($especialidades as $especialidad) {
            $servicios = $this->getServiciosPorEspecialidad($especialidad->nombre);

            foreach ($servicios as $servicio) {
                // Evitar duplicados por código
                if (Baremo::where('codigo', $servicio['codigo'])
                    ->where('empresa_id', $empresa->id)
                    ->exists()) {
                    continue;
                }

                Baremo::create([
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                    'especialidad_id' => $especialidad->id,
                    'categoria_id' => null, // Se puede asignar después
                    'codigo' => $servicio['codigo'],
                    'nombre_servicio' => $servicio['nombre'],
                    'descripcion' => $servicio['descripcion'] ?? '',
                    'costo_usd' => $servicio['costo'],
                    'costo_bs' => 0, // Se calcula automáticamente en el modelo
                    'aplica_iva' => $servicio['aplica_iva'] ?? true,
                    'exento_iva' => $servicio['exento_iva'] ?? false,
                    'duracion_minutos' => $servicio['duracion'] ?? 30,
                    'porcentaje_medico' => $servicio['porcentaje_medico'] ?? 60,
                    'porcentaje_clinica' => $servicio['porcentaje_clinica'] ?? 40,
                    'activo' => true,
                ]);

                $totalCreados++;
            }

            $this->command->info("✓ {$especialidad->nombre}: " . count($servicios) . " servicios");
        }

        $this->command->info("\nTotal de servicios creados: {$totalCreados}");
    }

    /**
     * Obtener servicios específicos por especialidad
     */
    private function getServiciosPorEspecialidad(string $especialidad): array
    {
        $servicios = match ($especialidad) {
            'Medicina General' => [
                ['codigo' => 'MG-CONS', 'nombre' => 'Consulta Medicina General', 'costo' => 30.00, 'duracion' => 20],
                ['codigo' => 'MG-RECO', 'nombre' => 'Reconsulta Medicina General', 'costo' => 20.00, 'duracion' => 15],
                ['codigo' => 'MG-CERT', 'nombre' => 'Certificado Médico', 'costo' => 10.00, 'duracion' => 10],
                ['codigo' => 'MG-PREMP', 'nombre' => 'Examen Pre-empleo', 'costo' => 35.00, 'duracion' => 30],
                ['codigo' => 'MG-CHEQ', 'nombre' => 'Chequeo General', 'costo' => 50.00, 'duracion' => 40],
            ],

            'Medicina Interna' => [
                ['codigo' => 'MI-CONS', 'nombre' => 'Consulta Medicina Interna', 'costo' => 50.00, 'duracion' => 30],
                ['codigo' => 'MI-RECO', 'nombre' => 'Reconsulta Medicina Interna', 'costo' => 35.00, 'duracion' => 20],
                ['codigo' => 'MI-EVAL', 'nombre' => 'Evaluación Integral Adulto Mayor', 'costo' => 70.00, 'duracion' => 45],
                ['codigo' => 'MI-MULTI', 'nombre' => 'Consulta Multimorbilidad', 'costo' => 65.00, 'duracion' => 40],
                ['codigo' => 'MI-PREV', 'nombre' => 'Evaluación Preventiva Cardiovascular', 'costo' => 80.00, 'duracion' => 50],
            ],

            'Cardiología' => [
                ['codigo' => 'CARD-CONS', 'nombre' => 'Consulta Cardiología', 'costo' => 60.00, 'duracion' => 30],
                ['codigo' => 'CARD-ECG', 'nombre' => 'Electrocardiograma', 'costo' => 25.00, 'duracion' => 15],
                ['codigo' => 'CARD-HOLTER', 'nombre' => 'Holter 24 horas', 'costo' => 80.00, 'duracion' => 20],
                ['codigo' => 'CARD-ECO', 'nombre' => 'Ecocardiograma', 'costo' => 100.00, 'duracion' => 40],
                ['codigo' => 'CARD-PRUEBA', 'nombre' => 'Prueba de Esfuerzo', 'costo' => 90.00, 'duracion' => 60],
                ['codigo' => 'CARD-MAPA', 'nombre' => 'MAPA (Monitoreo Presional)', 'costo' => 70.00, 'duracion' => 20],
                ['codigo' => 'CARD-RECO', 'nombre' => 'Reconsulta Cardiología', 'costo' => 45.00, 'duracion' => 20],
            ],

            'Ginecología' => [
                ['codigo' => 'GINE-CONS', 'nombre' => 'Consulta Ginecología', 'costo' => 55.00, 'duracion' => 30],
                ['codigo' => 'GINE-PAP', 'nombre' => 'Papanicolaou', 'costo' => 30.00, 'duracion' => 20],
                ['codigo' => 'GINE-COLPO', 'nombre' => 'Colposcopía', 'costo' => 60.00, 'duracion' => 30],
                ['codigo' => 'GINE-ECO', 'nombre' => 'Ecografía Ginecológica', 'costo' => 50.00, 'duracion' => 25],
                ['codigo' => 'GINE-PRENATAL', 'nombre' => 'Control Prenatal', 'costo' => 45.00, 'duracion' => 30],
                ['codigo' => 'GINE-DIU', 'nombre' => 'Colocación DIU', 'costo' => 80.00, 'duracion' => 30],
                ['codigo' => 'GINE-RECO', 'nombre' => 'Reconsulta Ginecología', 'costo' => 40.00, 'duracion' => 20],
            ],

            'Gastroenterología' => [
                ['codigo' => 'GAST-CONS', 'nombre' => 'Consulta Gastroenterología', 'costo' => 55.00, 'duracion' => 30],
                ['codigo' => 'GAST-ENDOS', 'nombre' => 'Endoscopia Digestiva Alta', 'costo' => 150.00, 'duracion' => 45],
                ['codigo' => 'GAST-COLON', 'nombre' => 'Colonoscopia', 'costo' => 180.00, 'duracion' => 60],
                ['codigo' => 'GAST-ULTRA', 'nombre' => 'Ultrasonido Abdominal', 'costo' => 70.00, 'duracion' => 30],
                ['codigo' => 'GAST-BIOPSIA', 'nombre' => 'Biopsia Endoscópica', 'costo' => 100.00, 'duracion' => 40],
                ['codigo' => 'GAST-RECO', 'nombre' => 'Reconsulta Gastroenterología', 'costo' => 40.00, 'duracion' => 20],
            ],

            'Neurología' => [
                ['codigo' => 'NEUR-CONS', 'nombre' => 'Consulta Neurología', 'costo' => 65.00, 'duracion' => 40],
                ['codigo' => 'NEUR-EEG', 'nombre' => 'Electroencefalograma', 'costo' => 90.00, 'duracion' => 45],
                ['codigo' => 'NEUR-EMG', 'nombre' => 'Electromiografía', 'costo' => 100.00, 'duracion' => 50],
                ['codigo' => 'NEUR-PUNCION', 'nombre' => 'Punción Lumbar', 'costo' => 120.00, 'duracion' => 40],
                ['codigo' => 'NEUR-BLOQUEO', 'nombre' => 'Bloqueo Nervioso', 'costo' => 80.00, 'duracion' => 30],
                ['codigo' => 'NEUR-RECO', 'nombre' => 'Reconsulta Neurología', 'costo' => 50.00, 'duracion' => 30],
            ],

            'Neurocirugía' => [
                ['codigo' => 'NCIR-CONS', 'nombre' => 'Consulta Neurocirugía', 'costo' => 80.00, 'duracion' => 45],
                ['codigo' => 'NCIR-EVAL', 'nombre' => 'Evaluación Pre-quirúrgica', 'costo' => 100.00, 'duracion' => 60],
                ['codigo' => 'NCIR-PUNCION', 'nombre' => 'Punción Ventricular', 'costo' => 150.00, 'duracion' => 45],
                ['codigo' => 'NCIR-DRENAJE', 'nombre' => 'Drenaje Subdural', 'costo' => 200.00, 'duracion' => 60],
                ['codigo' => 'NCIR-RECO', 'nombre' => 'Reconsulta Neurocirugía', 'costo' => 60.00, 'duracion' => 30],
            ],

            'Pediatría' => [
                ['codigo' => 'PED-CONS', 'nombre' => 'Consulta Pediatría', 'costo' => 40.00, 'duracion' => 25],
                ['codigo' => 'PED-RECO', 'nombre' => 'Reconsulta Pediatría', 'costo' => 30.00, 'duracion' => 20],
                ['codigo' => 'PED-CONTROL', 'nombre' => 'Control Niño Sano', 'costo' => 35.00, 'duracion' => 25],
                ['codigo' => 'PED-VACUNA', 'nombre' => 'Aplicación Vacuna', 'costo' => 15.00, 'duracion' => 10],
                ['codigo' => 'PED-TALLA', 'nombre' => 'Evaluación Talla/Peso', 'costo' => 20.00, 'duracion' => 15],
                ['codigo' => 'PED-URG', 'nombre' => 'Atención Urgencia Pediátrica', 'costo' => 55.00, 'duracion' => 30],
                ['codigo' => 'PED-CERT', 'nombre' => 'Certificado Escolar', 'costo' => 15.00, 'duracion' => 10],
            ],

            'Nefrología' => [
                ['codigo' => 'NEFR-CONS', 'nombre' => 'Consulta Nefrología', 'costo' => 60.00, 'duracion' => 30],
                ['codigo' => 'NEFR-HEMOD', 'nombre' => 'Hemodiálisis (sesión)', 'costo' => 120.00, 'duracion' => 240],
                ['codigo' => 'NEFR-PERIT', 'nombre' => 'Diálisis Peritoneal', 'costo' => 100.00, 'duracion' => 180],
                ['codigo' => 'NEFR-BIOPSIA', 'nombre' => 'Biopsia Renal', 'costo' => 150.00, 'duracion' => 45],
                ['codigo' => 'NEFR-FISTULA', 'nombre' => 'Creación Fístula AV', 'costo' => 200.00, 'duracion' => 90],
                ['codigo' => 'NEFR-RECO', 'nombre' => 'Reconsulta Nefrología', 'costo' => 45.00, 'duracion' => 20],
            ],

            'Otorrinolaringología' => [
                ['codigo' => 'ORL-CONS', 'nombre' => 'Consulta ORL', 'costo' => 50.00, 'duracion' => 25],
                ['codigo' => 'ORL-NASOFIBRO', 'nombre' => 'Nasofibroscopía', 'costo' => 70.00, 'duracion' => 30],
                ['codigo' => 'ORL-AUDIOMETRIA', 'nombre' => 'Audiometría', 'costo' => 40.00, 'duracion' => 30],
                ['codigo' => 'ORL-IMPEDANCIOMETRIA', 'nombre' => 'Impedanciometría', 'costo' => 35.00, 'duracion' => 20],
                ['codigo' => 'ORL-LAVADO', 'nombre' => 'Lavado Oído', 'costo' => 20.00, 'duracion' => 15],
                ['codigo' => 'ORL-CAUTERIZACION', 'nombre' => 'Cauterización Nasal', 'costo' => 45.00, 'duracion' => 20],
                ['codigo' => 'ORL-BIOPSIA', 'nombre' => 'Biopsia Laringe', 'costo' => 80.00, 'duracion' => 30],
                ['codigo' => 'ORL-RECO', 'nombre' => 'Reconsulta ORL', 'costo' => 35.00, 'duracion' => 20],
            ],

            default => [
                ['codigo' => strtoupper(substr($especialidad, 0, 4)) . '-CONS', 'nombre' => "Consulta {$especialidad}", 'costo' => 50.00, 'duracion' => 30],
                ['codigo' => strtoupper(substr($especialidad, 0, 4)) . '-RECO', 'nombre' => "Reconsulta {$especialidad}", 'costo' => 35.00, 'duracion' => 20],
            ],
        };

        return $servicios;
    }
}
