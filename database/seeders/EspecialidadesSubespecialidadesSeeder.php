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

        // Datos de especialidades y subespecialidades con códigos únicos
        $especialidadesData = [
            [
                'nombre' => 'Medicina General',
                'codigo' => 'MEDGEN',
                'descripcion' => 'Atención médica primaria y preventiva',
                'costo_consulta' => 30.00,
                'duracion_consulta' => 30,
                'color' => '#28a745',
                'requiere_cita_previa' => false,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Medicina Familiar', 'codigo' => 'MEDFAM', 'descripcion' => 'Atención integral a la familia'],
                    ['nombre' => 'Medicina Preventiva', 'codigo' => 'MEDPREV', 'descripcion' => 'Prevención de enfermedades'],
                    ['nombre' => 'Medicina del Trabajo', 'codigo' => 'MEDTRA', 'descripcion' => 'Salud laboral y ocupacional'],
                ]
            ],
            [
                'nombre' => 'Pediatría',
                'codigo' => 'PEDIA',
                'descripcion' => 'Medicina infantil y del adolescente',
                'costo_consulta' => 35.00,
                'duracion_consulta' => 30,
                'color' => '#17a2b8',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Pediatría General', 'codigo' => 'PEDGEN', 'descripcion' => 'Atención pediátrica básica'],
                    ['nombre' => 'Neonatología', 'codigo' => 'NEONA', 'descripcion' => 'Atención del recién nacido'],
                    ['nombre' => 'Pediatría del Desarrollo', 'codigo' => 'PEDDES', 'descripcion' => 'Desarrollo infantil y crecimiento'],
                ]
            ],
            [
                'nombre' => 'Ginecología y Obstetricia',
                'codigo' => 'GINOBST',
                'descripcion' => 'Salud de la mujer y obstetricia',
                'costo_consulta' => 40.00,
                'duracion_consulta' => 45,
                'color' => '#e83e8c',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Ginecología General', 'codigo' => 'GINGEN', 'descripcion' => 'Salud ginecológica'],
                    ['nombre' => 'Obstetricia', 'codigo' => 'OBSTET', 'descripcion' => 'Embarazo y parto'],
                    ['nombre' => 'Ginecología Oncológica', 'codigo' => 'GINONC', 'descripcion' => 'Cancer ginecológico'],
                ]
            ],
            [
                'nombre' => 'Cardiología',
                'codigo' => 'CARDIO',
                'descripcion' => 'Enfermedades del corazón y sistema cardiovascular',
                'costo_consulta' => 60.00,
                'duracion_consulta' => 45,
                'color' => '#dc3545',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Cardiología General', 'codigo' => 'CARDGEN', 'descripcion' => 'Enfermedades cardíacas'],
                    ['nombre' => 'Cardiología Intervencionista', 'codigo' => 'CARDINT', 'descripcion' => 'Procedimientos cardíacos invasivos'],
                    ['nombre' => 'Electrofisiología Cardíaca', 'codigo' => 'ELECTRO', 'descripcion' => 'Trastornos del ritmo cardíaco'],
                ]
            ],
            [
                'nombre' => 'Neurología',
                'codigo' => 'NEURO',
                'descripcion' => 'Enfermedades del sistema nervioso',
                'costo_consulta' => 55.00,
                'duracion_consulta' => 45,
                'color' => '#6f42c1',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Neurología General', 'codigo' => 'NEUROGEN', 'descripcion' => 'Enfermedades neurológicas'],
                    ['nombre' => 'Neurocirugía', 'codigo' => 'NEUROCIR', 'descripcion' => 'Cirugía del sistema nervioso'],
                    ['nombre' => 'Neurología Pediátrica', 'codigo' => 'NEUROPED', 'descripcion' => 'Neurología infantil'],
                ]
            ],
            [
                'nombre' => 'Ortopedia',
                'codigo' => 'ORTOP',
                'descripcion' => 'Enfermedades del sistema musculoesquelético',
                'costo_consulta' => 45.00,
                'duracion_consulta' => 30,
                'color' => '#fd7e14',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Ortopedia General', 'codigo' => 'ORTOGEN', 'descripcion' => 'Enfermedades ortopédicas'],
                    ['nombre' => 'Traumatología', 'codigo' => 'TRAUMA', 'descripcion' => 'Lesiones traumáticas'],
                    ['nombre' => 'Cirugía Ortopédica', 'codigo' => 'CIRORTO', 'descripcion' => 'Cirugía del aparato locomotor'],
                ]
            ],
            [
                'nombre' => 'Oftalmología',
                'codigo' => 'OFtal',
                'descripcion' => 'Enfermedades del ojo y su tratamiento',
                'costo_consulta' => 50.00,
                'duracion_consulta' => 30,
                'color' => '#20c997',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Oftalmología General', 'codigo' => 'OFTALGEN', 'descripcion' => 'Enfermedades oculares'],
                    ['nombre' => 'Cirugía Ocular', 'codigo' => 'CIRoftal', 'descripcion' => 'Cirugía del ojo'],
                    ['nombre' => 'Optometría', 'codigo' => 'OPTOM', 'descripcion' => 'Corrección visual'],
                ]
            ],
            [
                'nombre' => 'Otorrinolaringología',
                'codigo' => 'OTORRINO',
                'descripcion' => 'Enfermedades de oído, nariz y garganta',
                'costo_consulta' => 40.00,
                'duracion_consulta' => 30,
                'color' => '#6610f2',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Otorrinolaringología General', 'codigo' => 'OTORRGEN', 'descripcion' => 'Enfermedades ORL'],
                    ['nombre' => 'Audiología', 'codigo' => 'AUDIO', 'descripcion' => 'Trastornos auditivos'],
                    ['nombre' => 'Cirugía Cabeza y Cuello', 'codigo' => 'CIRCUE', 'descripcion' => 'Cirugía de cabeza y cuello'],
                ]
            ],
            [
                'nombre' => 'Dermatología',
                'codigo' => 'DERMA',
                'descripcion' => 'Enfermedades de la piel',
                'costo_consulta' => 45.00,
                'duracion_consulta' => 30,
                'color' => '#ffc107',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Dermatología General', 'codigo' => 'DERMAGEN', 'descripcion' => 'Enfermedades de la piel'],
                    ['nombre' => 'Dermatología Cosmética', 'codigo' => 'DERMACOS', 'descripcion' => 'Tratamientos estéticos'],
                    ['nombre' => 'Dermatología Oncológica', 'codigo' => 'DERMAONC', 'descripcion' => 'Cáncer de piel'],
                ]
            ],
            [
                'nombre' => 'Psiquiatría',
                'codigo' => 'PSIQUIA',
                'descripcion' => 'Trastornos mentales y emocionales',
                'costo_consulta' => 55.00,
                'duracion_consulta' => 60,
                'color' => '#6c757d',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Psiquiatría General', 'codigo' => 'PSIQUIGEN', 'descripcion' => 'Trastornos psiquiátricos'],
                    ['nombre' => 'Psiquiatría Infantil', 'codigo' => 'PSIQUIPED', 'descripcion' => 'Psiquiatría pediátrica'],
                    ['nombre' => 'Psicología Clínica', 'codigo' => 'PSICOLCLIN', 'descripcion' => 'Terapia psicológica'],
                ]
            ],
            [
                'nombre' => 'Endocrinología',
                'codigo' => 'ENDOCRINO',
                'descripcion' => 'Trastornos hormonales y metabólicos',
                'costo_consulta' => 50.00,
                'duracion_consulta' => 45,
                'color' => '#007bff',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Endocrinología General', 'codigo' => 'ENDOGEN', 'descripcion' => 'Trastornos endocrinos'],
                    ['nombre' => 'Diabetología', 'codigo' => 'DIABETO', 'descripcion' => 'Diabetes y metabolismo'],
                    ['nombre' => 'Nutrición Clínica', 'codigo' => 'NUTRICLIN', 'descripcion' => 'Nutrición médica'],
                ]
            ],
            [
                'nombre' => 'Urología',
                'codigo' => 'UROLOGIA',
                'descripcion' => 'Enfermedades del sistema urinario y genital masculino',
                'costo_consulta' => 55.00,
                'duracion_consulta' => 30,
                'color' => '#795548',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Urología General', 'codigo' => 'UROLOGEN', 'descripcion' => 'Enfermedades urológicas'],
                    ['nombre' => 'Urología Oncológica', 'codigo' => 'UROLOGONC', 'descripcion' => 'Cáncer urológico'],
                    ['nombre' => 'Andrología', 'codigo' => 'ANDROLOG', 'descripcion' => 'Salud masculina'],
                ]
            ],
            [
                'nombre' => 'Gastroenterología',
                'codigo' => 'GASTRO',
                'descripcion' => 'Enfermedades del sistema digestivo',
                'costo_consulta' => 50.00,
                'duracion_consulta' => 45,
                'color' => '#ff9800',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    ['nombre' => 'Gastroenterología General', 'codigo' => 'GASTROGEN', 'descripcion' => 'Enfermedades digestivas'],
                    ['nombre' => 'Hepatología', 'codigo' => 'HEPATO', 'descripcion' => 'Enfermedades del hígado'],
                    ['nombre' => 'Endoscopia Digestiva', 'codigo' => 'ENDOSDIG', 'descripcion' => 'Procedimientos endoscópicos'],
                ]
            ],
        ];

        // Crear especialidades y subespecialidades
        foreach ($especialidadesData as $especialidadData) {
            $subespecialidades = $especialidadData['subespecialidades'];
            unset($especialidadData['subespecialidades']);
            
            // Agregar datos de empresa y sucursal
            $especialidadData['empresa_id'] = $empresa->id;
            $especialidadData['sucursal_id'] = $sucursal->id;
            
            $especialidad = Especialidad::create($especialidadData);
            
            // Crear subespecialidades
            foreach ($subespecialidades as $subespecialidadData) {
                $subespecialidadData['especialidad_id'] = $especialidad->id;
                $subespecialidadData['empresa_id'] = $empresa->id;
                $subespecialidadData['sucursal_id'] = $sucursal->id;
                $subespecialidadData['costo_consulta'] = $especialidadData['costo_consulta'];
                $subespecialidadData['duracion_consulta'] = $especialidadData['duracion_consulta'];
                $subespecialidadData['color'] = $especialidadData['color'];
                $subespecialidadData['requiere_cita_previa'] = $especialidadData['requiere_cita_previa'];
                $subespecialidadData['status'] = true;
                
                Subespecialidad::create($subespecialidadData);
            }
        }
        
        $this->command->info('Especialidades y subespecialidades creadas exitosamente.');
    }
}