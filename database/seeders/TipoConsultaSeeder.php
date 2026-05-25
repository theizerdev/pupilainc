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
                'nombre' => 'Primera vez adulto',
                'descripcion' => 'Consulta médica general para evaluación de síntomas y diagnóstico',
                'color' => '#3b76f6',
                'icono' => 'fa-stethoscope',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Subsecuente adulto',
                'descripcion' => 'Consulta médica general para evaluación de síntomas y diagnóstico',
                'color' => '#11336b',
                'icono' => 'fa-stethoscope',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Primera vez infantil',
                'descripcion' => 'Consulta médica general para evaluación de síntomas y diagnóstico',
                'color' => '#9110b9',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Tamiz del recién nacido',
                'descripcion' => 'Estudio de detección en recién nacidos.',
                'color' => '#c887e2',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Subsecuente infantil',
                'descripcion' => 'Paciente menor de edad con citas previas',
                'color' => '#430169',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Estudios',
                'descripcion' => 'Citas para estudios diagnósticos: campimetría, OCT, etc.',
                'color' => '#d39014',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
            [
                'nombre' => 'Cirugía',
                'descripcion' => 'Procedimientos quirúrgicos agendados',
                'color' => '#fffb00',
                'icono' => 'fa-heartbeat',
                'codigo' => $this->generarCodigoUnico($codigosGenerados),
                'status' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
            ],
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