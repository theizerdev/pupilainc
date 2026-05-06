<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Baremo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Especialidad;
use App\Models\Categoria;

class BaremoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener registros básicos
        $empresa = Empresa::first();
        $sucursal = Sucursal::first();
        $especialidad = Especialidad::first(); // Usamos la primera especialidad disponible
        $categoria = Categoria::first(); // Usamos la primera categoría disponible
        
        if (!$empresa) {
            $this->command->error('No se encontró ninguna empresa. Ejecute primero el seeder de empresas.');
            return;
        }
        
        if (!$sucursal) {
            $this->command->error('No se encontró ninguna sucursal. Ejecute primero el seeder de sucursales.');
            return;
        }

        // Definir los servicios médicos
        $servicios = [
            [
                'nombre_servicio' => 'CONSULTA OFTALMOLOGICA',
                'descripcion' => 'Consulta oftalmológica completa',
                'costo_usd' => 5000.00,
                'duracion_minutos' => 30,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'CONSULTA PERSONAL DE SALUD',
                'descripcion' => 'Consulta general al personal de salud',
                'costo_usd' => 4000.00,
                'duracion_minutos' => 25,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'CONSULTA OFTAMOLOGICA PEDIATRICA',
                'descripcion' => 'Consulta oftalmológica especializada para niños',
                'costo_usd' => 5500.00,
                'duracion_minutos' => 35,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'SEGUIMIENTO ADULTOS',
                'descripcion' => 'Consulta de seguimiento para adultos',
                'costo_usd' => 3500.00,
                'duracion_minutos' => 20,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'SEGUIMIENTO NIÑOS',
                'descripcion' => 'Consulta de seguimiento para niños',
                'costo_usd' => 3500.00,
                'duracion_minutos' => 20,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'SUBSECUENTE ADULTOS',
                'descripcion' => 'Consulta subsequente para adultos',
                'costo_usd' => 3000.00,
                'duracion_minutos' => 15,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'SUBSECUENTE NIÑOS',
                'descripcion' => 'Consulta subsequente para niños',
                'costo_usd' => 3000.00,
                'duracion_minutos' => 15,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'TAMIZ RECIEN NACIDO',
                'descripcion' => 'Tamizaje para recién nacidos',
                'costo_usd' => 4500.00,
                'duracion_minutos' => 25,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'TAMIZ EMMA WILLARD',
                'descripcion' => 'Tamizaje específico Emma Willard',
                'costo_usd' => 4500.00,
                'duracion_minutos' => 25,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'TOPOGRAFIA',
                'descripcion' => 'Topografía corneal',
                'costo_usd' => 6000.00,
                'duracion_minutos' => 20,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'ULTRASONIDO',
                'descripcion' => 'Ultrasonido médico',
                'costo_usd' => 7000.00,
                'duracion_minutos' => 30,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
            [
                'nombre_servicio' => 'LASER',
                'descripcion' => 'Tratamiento con láser médico',
                'costo_usd' => 12000.00,
                'duracion_minutos' => 45,
                'porcentaje_medico' => 0.00,
                'porcentaje_clinica' => 100.00,
            ],
        ];

        // Crear los servicios
        foreach ($servicios as $servicio) {
            // Generar código único
            $codigo = $this->generateUniqueCode();
            
            Baremo::firstOrCreate(
                [
                    'codigo' => $codigo,
                    'nombre_servicio' => $servicio['nombre_servicio'],
                ],
                [
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                    'especialidad_id' => $especialidad ? $especialidad->id : null,
                    'categoria_id' => $categoria ? $categoria->id : null,
                    'codigo' => $codigo,
                    'nombre_servicio' => $servicio['nombre_servicio'],
                    'descripcion' => $servicio['descripcion'],
                    'costo_usd' => $servicio['costo_usd'],
                    'aplica_iva' => true,
                    'exento_iva' => false,
                    'duracion_minutos' => $servicio['duracion_minutos'],
                    'porcentaje_medico' =>$servicio['porcentaje_medico'],
                    'porcentaje_clinica' => $servicio['porcentaje_clinica'],
                    'activo' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($servicios) . ' servicios médicos creados exitosamente.');
    }

    private function generateUniqueCode()
    {
        $existingCodes = Baremo::pluck('codigo')->toArray();
        
        do {
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (in_array($codigo, $existingCodes));
        
        return $codigo;
    }
}