<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidad;
use App\Models\Subespecialidad;
use App\Models\Empresa;
use App\Models\Sucursal;

class EspecialidadesSubespecialidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener empresa y sucursal por defecto (primera disponible)
        $empresa = Empresa::first();
        $sucursal = Sucursal::first();

        if (!$empresa || !$sucursal) {
            $this->command->warn('No se encontró empresa o sucursal para asignar a las especialidades.');
            return;
        }

        // Datos de especialidades médicas
        $especialidadesData = [
            ['nombre' => 'Anestesiología', 'codigo' => 'ANEST', 'color' => '#6f42c1', 'costo_consulta' => 50.00],
            ['nombre' => 'Anatomía Patológica', 'codigo' => 'ANATPAT', 'color' => '#e83e8c', 'costo_consulta' => 60.00],
            ['nombre' => 'Cardiología Clínica', 'codigo' => 'CARDCLI', 'color' => '#dc3545', 'costo_consulta' => 70.00],
            ['nombre' => 'Cardiología Intervencionista', 'codigo' => 'CARDINT', 'color' => '#c82333', 'costo_consulta' => 80.00],
            ['nombre' => 'Cirugía Pediátrica', 'codigo' => 'CIRPED', 'color' => '#17a2b8', 'costo_consulta' => 65.00],
            ['nombre' => 'Cirugía General', 'codigo' => 'CIRGEN', 'color' => '#007bff', 'costo_consulta' => 60.00],
            ['nombre' => 'Cirugía Plástica y Reconstructiva', 'codigo' => 'CIRPLAS', 'color' => '#fd7e14', 'costo_consulta' => 75.00],
            ['nombre' => 'Angiología y Cirugía Vascular', 'codigo' => 'ANGVASC', 'color' => '#20c997', 'costo_consulta' => 70.00],
            ['nombre' => 'Dermatología', 'codigo' => 'DERMA', 'color' => '#ffc107', 'costo_consulta' => 55.00],
            ['nombre' => 'Endoscopia del Aparato Digestivo', 'codigo' => 'ENDODIG', 'color' => '#28a745', 'costo_consulta' => 65.00],
            ['nombre' => 'Gastroenterología', 'codigo' => 'GASTRO', 'color' => '#218838', 'costo_consulta' => 60.00],
            ['nombre' => 'Ginecología y Obstetricia', 'codigo' => 'GINOBS', 'color' => '#e83e8c', 'costo_consulta' => 55.00],
            ['nombre' => 'Hematología', 'codigo' => 'HEMAT', 'color' => '#dc3545', 'costo_consulta' => 65.00],
            ['nombre' => 'Infectología de Adulto', 'codigo' => 'INFECT', 'color' => '#6c757d', 'costo_consulta' => 60.00],
            ['nombre' => 'Medicina Aeroespacial', 'codigo' => 'MEDAERO', 'color' => '#17a2b8', 'costo_consulta' => 70.00],
            ['nombre' => 'Medicina de Rehabilitación', 'codigo' => 'MEDREHAB', 'color' => '#20c997', 'costo_consulta' => 50.00],
            ['nombre' => 'Medicina Interna', 'codigo' => 'MEDINT', 'color' => '#007bff', 'costo_consulta' => 55.00],
            ['nombre' => 'Nefrología', 'codigo' => 'NEFRO', 'color' => '#17a2b8', 'costo_consulta' => 65.00],
            ['nombre' => 'Neurología de Adultos', 'codigo' => 'NEURO', 'color' => '#6f42c1', 'costo_consulta' => 70.00],
            ['nombre' => 'Neumología', 'codigo' => 'NEUMO', 'color' => '#20c997', 'costo_consulta' => 60.00],
            ['nombre' => 'Oftalmología', 'codigo' => 'OFTAL', 'color' => '#28a745', 'costo_consulta' => 50.00],
            ['nombre' => 'Odontología', 'codigo' => 'ODONTO', 'color' => '#ffc107', 'costo_consulta' => 45.00],
            ['nombre' => 'Ortopedia', 'codigo' => 'ORTOP', 'color' => '#fd7e14', 'costo_consulta' => 60.00],
            ['nombre' => 'Otorrinolaringología', 'codigo' => 'OTORRI', 'color' => '#17a2b8', 'costo_consulta' => 55.00],
            ['nombre' => 'Patología Clínica', 'codigo' => 'PATCLI', 'color' => '#6c757d', 'costo_consulta' => 60.00],
            ['nombre' => 'Pediatría', 'codigo' => 'PEDIAT', 'color' => '#e83e8c', 'costo_consulta' => 50.00],
            ['nombre' => 'Psiquiatría General', 'codigo' => 'PSIQ', 'color' => '#6f42c1', 'costo_consulta' => 65.00],
            ['nombre' => 'Radiología e Imagen', 'codigo' => 'RADIO', 'color' => '#007bff', 'costo_consulta' => 70.00],
            ['nombre' => 'Medicina Crítica', 'codigo' => 'MEDCRIT', 'color' => '#dc3545', 'costo_consulta' => 80.00],
            ['nombre' => 'Urología', 'codigo' => 'URO', 'color' => '#17a2b8', 'costo_consulta' => 60.00],
            ['nombre' => 'Cirugía Oncológica', 'codigo' => 'CIRONCO', 'color' => '#c82333', 'costo_consulta' => 75.00],
            ['nombre' => 'Oncología Médica', 'codigo' => 'ONCOMEDIC', 'color' => '#dc3545', 'costo_consulta' => 70.00],
            ['nombre' => 'Oncología Pediátrica', 'codigo' => 'ONCOPED', 'color' => '#e83e8c', 'costo_consulta' => 70.00],
            ['nombre' => 'Radio-Oncología', 'codigo' => 'RADIONCO', 'color' => '#6f42c1', 'costo_consulta' => 75.00],
            ['nombre' => 'Cirugía Neurológica', 'codigo' => 'CIRNEURO', 'color' => '#007bff', 'costo_consulta' => 85.00],
        ];

        // Crear especialidades
        foreach ($especialidadesData as $especialidadData) {
            $especialidadData['empresa_id'] = $empresa->id;
            $especialidadData['sucursal_id'] = $sucursal->id;
            $especialidadData['duracion_consulta'] = 30;
            $especialidadData['requiere_cita_previa'] = true;
            $especialidadData['status'] = true;
            $especialidadData['descripcion'] = 'Especialidad médica de ' . $especialidadData['nombre'];
            
            Especialidad::create($especialidadData);
        }

        // Crear subespecialidades de Oftalmología
        $oftalmologia = Especialidad::where('codigo', 'OFTAL')->first();
        if ($oftalmologia) {
            $subespecialidades = [
                ['nombre' => 'Oftalmología General', 'codigo' => 'OFTALGEN', 'descripcion' => 'Enfermedades oculares generales'],
                ['nombre' => 'Córnea y Segmento Anterior', 'codigo' => 'CORNEA', 'descripcion' => 'Enfermedades de córnea'],
                ['nombre' => 'Cirugía de Cataratas', 'codigo' => 'CIRCATAR', 'descripcion' => 'Cirugía de cataratas'],
                ['nombre' => 'Glaucoma', 'codigo' => 'GLAUC', 'descripcion' => 'Diagnóstico y tratamiento del glaucoma'],
                ['nombre' => 'Retina y Vítreo', 'codigo' => 'RETINA', 'descripcion' => 'Enfermedades de retina'],
                ['nombre' => 'Oculoplástica', 'codigo' => 'OCULOPL', 'descripcion' => 'Cirugía plástica ocular'],
                ['nombre' => 'Estrabismo', 'codigo' => 'ESTRAB', 'descripcion' => 'Desviación ocular'],
                ['nombre' => 'Optometría', 'codigo' => 'OPTOM', 'descripcion' => 'Corrección visual'],
            ];

            foreach ($subespecialidades as $subData) {
                $subData['especialidad_id'] = $oftalmologia->id;
                $subData['empresa_id'] = $empresa->id;
                $subData['sucursal_id'] = $sucursal->id;
                $subData['costo_consulta'] = 50.00;
                $subData['duracion_consulta'] = 30;
                $subData['color'] = $oftalmologia->color;
                $subData['requiere_cita_previa'] = true;
                $subData['status'] = true;
                Subespecialidad::create($subData);
            }
        }
        
        $this->command->info('✓ ' . count($especialidadesData) . ' especialidades creadas exitosamente.');
    }
}