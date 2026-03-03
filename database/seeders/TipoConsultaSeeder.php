<?php

namespace Database\Seeders;

use App\Models\TipoConsulta;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoConsultaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener empresa y sucursal por defecto
        $empresa = Empresa::first();
        $sucursal = Sucursal::first();
        
        if (!$empresa || !$sucursal) {
            $this->command->error('No se encontró empresa o sucursal. Ejecute primero los seeders de Empresa y Sucursal.');
            return;
        }

        // Generar códigos únicos de 6 dígitos
        $codigosGenerados = [];
        
        $tiposConsultas = [
            [
                'nombre' => 'Primera vez',
                'descripcion' => 'Consulta médica general para evaluación de síntomas y diagnóstico',
                'color' => '#f6763b',
                'icono' => 'fa-stethoscope',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Consulta General',
                'descripcion' => 'Consulta médica general para evaluación de síntomas y diagnóstico',
                'color' => '#3B82F6',
                'icono' => 'fa-stethoscope',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Control de Rutina',
                'descripcion' => 'Control médico periódico para seguimiento de condiciones crónicas',
                'color' => '#10B981',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Urgencia',
                'descripcion' => 'Atención médica urgente para casos que requieren atención inmediata',
                'color' => '#EF4444',
                'icono' => 'fa-ambulance',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Consulta Especializada',
                'descripcion' => 'Consulta con médico especialista en área específica',
                'color' => '#8B5CF6',
                'icono' => 'fa-user-md',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Revisión de Exámenes',
                'descripcion' => 'Consulta para revisión e interpretación de resultados de exámenes',
                'color' => '#06B6D4',
                'icono' => 'fa-file-medical',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Consulta Prenatal',
                'descripcion' => 'Control y seguimiento del embarazo',
                'color' => '#EC4899',
                'icono' => 'fa-baby',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Vacunación',
                'descripcion' => 'Aplicación de vacunas y esquemas de vacunación',
                'color' => '#F59E0B',
                'icono' => 'fa-syringe',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Cirugía Ambulatoria',
                'descripcion' => 'Procedimientos quirúrgicos que no requieren hospitalización',
                'color' => '#6366F1',
                'icono' => 'fa-scissors',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Terapia',
                'descripcion' => 'Sesiones de terapia física, ocupacional o psicológica',
                'color' => '#84CC16',
                'icono' => 'fa-hands-helping',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ],
            [
                'nombre' => 'Emergencia',
                'descripcion' => 'Atención de emergencia médica crítica',
                'color' => '#DC2626',
                'icono' => 'fa-plus-circle',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                 'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,

            ]
        ];

        foreach ($tiposConsultas as $tipoConsulta) {
            // Verificar si ya existe un tipo de consulta con el mismo código
            $existente = TipoConsulta::where('codigo', $tipoConsulta['codigo'])->first();
            
            if (!$existente) {
                TipoConsulta::create($tipoConsulta);
                $this->command->info("Tipo de consulta creado: " . $tipoConsulta['nombre']);
            } else {
                $this->command->warn("Tipo de consulta ya existe: " . $tipoConsulta['nombre']);
            }
        }

        $this->command->info('Seeder de tipos de consulta ejecutado exitosamente.');
    }

    /**
     * Genera un código único de 6 dígitos
     */
    private function generarCodigoUnico(array &$codigosGenerados): int
    {
        do {
            $codigo = random_int(100000, 999999); // Número aleatorio de 6 dígitos
        } while (in_array($codigo, $codigosGenerados));
        
        $codigosGenerados[] = $codigo;
        return $codigo;
    }

    /**
     * Reverse the migrations (rollback).
     */
    public function down(): void
    {
        // Eliminar todos los tipos de consulta creados por este seeder
        // (aquí podrías implementar una lógica más específica si es necesario)
        DB::table('tipo_consultas')->where('codigo', '>=', 100000)->delete();

        $this->command->info('Tipos de consulta eliminados exitosamente.');
    }
}