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
                'nombre' => 'Oftalmología',
                'codigo' => 'OFtal',
                'descripcion' => 'Enfermedades del ojo y su tratamiento',
                'costo_consulta' => 50.00,
                'duracion_consulta' => 30,
                'color' => '#20c997',
                'requiere_cita_previa' => true,
                'status' => true,
                'subespecialidades' => [
                    // Subespecialidades generales y diagnóstico
                    ['nombre' => 'Oftalmología General', 'codigo' => 'OFTALGEN', 'descripcion' => 'Enfermedades oculares generales y consulta de rutina'],
                    ['nombre' => 'Oftalmología Pediátrica', 'codigo' => 'OFTALPED', 'descripcion' => 'Enfermedades oculares en niños y adolescentes'],
                    ['nombre' => 'Oftalmología Geriátrica', 'codigo' => 'OFTALGER', 'descripcion' => 'Enfermedades oculares en adultos mayores'],
                    ['nombre' => 'Oftalmología Preventiva', 'codigo' => 'OFTALPREV', 'descripcion' => 'Prevención y detección temprana de enfermedades oculares'],
                    
                    // Segmento anterior
                    ['nombre' => 'Córnea y Segmento Anterior', 'codigo' => 'CORNEA', 'descripcion' => 'Enfermedades de la córnea, conjuntiva y segmento anterior'],
                    ['nombre' => 'Cirugía de Córnea', 'codigo' => 'CIRCORNEA', 'descripcion' => 'Cirugía refractiva y trasplante de córnea'],
                    ['nombre' => 'Queratoplastia', 'codigo' => 'QUERATO', 'descripcion' => 'Trasplante de córnea'],
                    ['nombre' => 'Crosslinking Corneal', 'codigo' => 'CROSSLINK', 'descripcion' => 'Tratamiento del queratocono'],
                    
                    // Cataratas y cirugía refractiva
                    ['nombre' => 'Cirugía de Cataratas', 'codigo' => 'CIRCATAR', 'descripcion' => 'Cirugía de cataratas y lentes intraoculares'],
                    ['nombre' => 'Cirugía Refractiva', 'codigo' => 'CIRREFRAC', 'descripcion' => 'Cirugía para corrección de miopía, hipermetropía y astigmatismo'],
                    ['nombre' => 'LASIK', 'codigo' => 'LASIK', 'descripcion' => 'Cirugía LASIK para corrección de defectos refractivos'],
                    ['nombre' => 'PRK', 'codigo' => 'PRK', 'descripcion' => 'Queratectomía fotorefractiva'],
                    ['nombre' => 'Lentes Intraoculares Premium', 'codigo' => 'LENTESP', 'descripcion' => 'Lentes intraoculares multifocales y tóricos'],
                    
                    // Glaucoma
                    ['nombre' => 'Glaucoma', 'codigo' => 'GLAUC', 'descripcion' => 'Diagnóstico y tratamiento del glaucoma'],
                    ['nombre' => 'Cirugía de Glaucoma', 'codigo' => 'CIRGLAUC', 'descripcion' => 'Cirugía para control del glaucoma'],
                    ['nombre' => 'Trabeculectomía', 'codigo' => 'TRABEC', 'descripcion' => 'Cirugía de drenaje para glaucoma'],
                    ['nombre' => 'Implantes de Drenaje', 'codigo' => 'IMPLDREN', 'descripcion' => 'Implantes para control del glaucoma'],
                    ['nombre' => 'Glaucoma Congénito', 'codigo' => 'GLAUCCONG', 'descripcion' => 'Glaucoma en niños'],
                    
                    // Retina y vítreo
                    ['nombre' => 'Retina y Vítreo', 'codigo' => 'RETINA', 'descripcion' => 'Enfermedades de la retina y vítreo'],
                    ['nombre' => 'Cirugía de Retina', 'codigo' => 'CIRRETINA', 'descripcion' => 'Cirugía de desprendimiento de retina y otras patologías'],
                    ['nombre' => 'Desprendimiento de Retina', 'codigo' => 'DESPRENRET', 'descripcion' => 'Tratamiento del desprendimiento de retina'],
                    ['nombre' => 'Degeneración Macular', 'codigo' => 'DEGMAC', 'descripcion' => 'Degeneración macular relacionada con la edad'],
                    ['nombre' => 'Retinopatía Diabética', 'codigo' => 'RETDIAB', 'descripcion' => 'Complicaciones oculares de la diabetes'],
                    ['nombre' => 'Oclusión Venosa Retiniana', 'codigo' => 'OCLUVEN', 'descripcion' => 'Oclusión de venas retinianas'],
                    ['nombre' => 'Membrana Epirretiniana', 'codigo' => 'MEMBEPR', 'descripcion' => 'Membrana epirretiniana o pucker macular'],
                    ['nombre' => 'Agujero Macular', 'codigo' => 'AGUMAC', 'descripcion' => 'Agujero macular'],
                    
                    // Uveítis e inflamación
                    ['nombre' => 'Uveítis', 'codigo' => 'UVEIT', 'descripcion' => 'Inflamaciones del uvea'],
                    ['nombre' => 'Inflamación Ocular', 'codigo' => 'INFLAMOC', 'descripcion' => 'Inflamaciones oculares diversas'],
                    ['nombre' => 'Uveítis Anterior', 'codigo' => 'UVEITANT', 'descripcion' => 'Iritis y ciclitis'],
                    ['nombre' => 'Uveítis Posterior', 'codigo' => 'UVEITPOST', 'descripcion' => 'Coroiditis y retinitis'],
                    ['nombre' => 'Uveítis Intermedia', 'codigo' => 'UVEITINT', 'descripcion' => 'Pars planitis'],
                    
                    // Oculoplástica y vías lagrimales
                    ['nombre' => 'Oculoplástica', 'codigo' => 'OCULOPL', 'descripcion' => 'Cirugía plástica y reconstructiva ocular'],
                    ['nombre' => 'Blefaroplastía', 'codigo' => 'BLEFARO', 'descripcion' => 'Cirugía de párpados'],
                    ['nombre' => 'Ptosis Palpebral', 'codigo' => 'PTOSIS', 'descripcion' => 'Caída del párpado'],
                    ['nombre' => 'Entropión y Ectropión', 'codigo' => 'ENTRECP', 'descripcion' => 'Posición anormal de los párpados'],
                    ['nombre' => 'Vías Lagrimales', 'codigo' => 'VIASLAG', 'descripcion' => 'Enfermedades del sistema lagrimal'],
                    ['nombre' => 'Dacriocistorrinostomía', 'codigo' => 'DACRIO', 'descripcion' => 'Cirugía de vías lagrimales'],
                    ['nombre' => 'Tumoraciones Oculares', 'codigo' => 'TUMOROC', 'descripcion' => 'Tumores del ojo y anexos'],
                    
                    // Neuroftalmología
                    ['nombre' => 'Neuroftalmología', 'codigo' => 'NEUROFT', 'descripcion' => 'Relación entre el sistema nervioso y el ojo'],
                    ['nombre' => 'Parálisis de Nervios Craneales', 'codigo' => 'PARALCR', 'descripcion' => 'Parálisis de nervios que controlan el ojo'],
                    ['nombre' => 'Nistagmus', 'codigo' => 'NISTAG', 'descripcion' => 'Movimientos anormales del ojo'],
                    ['nombre' => 'Papiledema', 'codigo' => 'PAPIL', 'descripcion' => 'Edema del disco óptico'],
                    ['nombre' => 'Neuritis Óptica', 'codigo' => 'NEUROP', 'descripcion' => 'Inflamación del nervio óptico'],
                    ['nombre' => 'Estados Vegetativos y Coma', 'codigo' => 'VEGCOM', 'descripcion' => 'Evaluación ocular en pacientes en coma'],
                    
                    // Estrabismo y motilidad ocular
                    ['nombre' => 'Estrabismo', 'codigo' => 'ESTRAB', 'descripcion' => 'Desviación ocular'],
                    ['nombre' => 'Cirugía de Estrabismo', 'codigo' => 'CIRSTRAB', 'descripcion' => 'Cirugía para corregir desviaciones oculares'],
                    ['nombre' => 'Parálisis Oculomotora', 'codigo' => 'PARALOC', 'descripcion' => 'Parálisis de músculos oculares'],
                    ['nombre' => 'Motilidad Ocular', 'codigo' => 'MOTILOC', 'descripcion' => 'Problemas de movimiento ocular'],
                    ['nombre' => 'Diplopía', 'codigo' => 'DIPLOP', 'descripcion' => 'Visión doble'],
                    
                    // Óptica y refracción
                    ['nombre' => 'Optometría', 'codigo' => 'OPTOM', 'descripcion' => 'Corrección visual y exámenes de refracción'],
                    ['nombre' => 'Óptica Médica', 'codigo' => 'OPTICMED', 'descripcion' => 'Prescripción de lentes y dispositivos ópticos'],
                    ['nombre' => 'Refracción Avanzada', 'codigo' => 'REFRAVAN', 'descripcion' => 'Exámenes refractivos especializados'],
                    ['nombre' => 'Lentes de Contacto', 'codigo' => 'LENTCON', 'descripcion' => 'Adaptación de lentes de contacto'],
                    ['nombre' => 'Lentes Progresivas', 'codigo' => 'LENTPROG', 'descripcion' => 'Lentes multifocales'],
                    ['nombre' => 'Baja Visión', 'codigo' => 'BAJAVIS', 'descripcion' => 'Ayudas visuales para baja visión'],
                    
                    // Otras especialidades
                    ['nombre' => 'Oftalmología Oncológica', 'codigo' => 'OFTALONC', 'descripcion' => 'Cáncer ocular y tratamiento'],
                    ['nombre' => 'Oftalmología Traumatológica', 'codigo' => 'OFTALTRAU', 'descripcion' => 'Trauma ocular'],
                    ['nombre' => 'Oftalmología Quemaduras', 'codigo' => 'OFTALQUEM', 'descripcion' => 'Quemaduras oculares'],
                    ['nombre' => 'Oftalmología Quirúrgica Avanzada', 'codigo' => 'OFTALQUIRAV', 'descripcion' => 'Cirugías oculares complejas'],
                    ['nombre' => 'Ecografía Ocular', 'codigo' => 'ECOGRAFOC', 'descripcion' => 'Ecografía del ojo'],
                    ['nombre' => 'Angiografía Fluoresceínica', 'codigo' => 'ANGIOFLUO', 'descripcion' => 'Estudio de vasos sanguíneos oculares'],
                    ['nombre' => 'Tomografía de Coherencia Óptica (OCT)', 'codigo' => 'OCT', 'descripcion' => 'Imágenes de alta resolución de la retina'],
                    ['nombre' => 'Campimetría', 'codigo' => 'CAMPIM', 'descripcion' => 'Evaluación del campo visual'],
                    ['nombre' => 'Electrofisiología Visual', 'codigo' => 'ELECTROF', 'descripcion' => 'Estudios eléctricos del sistema visual'],
                    ['nombre' => 'Microscopía Confocal', 'codigo' => 'MICROCONF', 'descripcion' => 'Microscopía especializada de la córnea'],
                    ['nombre' => 'Topografía Corneal', 'codigo' => 'TOPOCOR', 'descripcion' => 'Mapeo de la superficie corneal'],
                    ['nombre' => 'Pachimetría Corneal', 'codigo' => 'PACHIM', 'descripcion' => 'Medición del grosor corneal'],
                    ['nombre' => 'Biometría Ocular', 'codigo' => 'BIOMETOC', 'descripcion' => 'Medición de estructuras oculares'],
                    ['nombre' => 'Medicina Ocular Basada en Evidencia', 'codigo' => 'MEDOCBASE', 'descripcion' => 'Tratamiento basado en evidencia científica'],
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