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
            
            ['nombre' => 'Oftalmología', 'codigo' => 'OFTAL', 'color' => '#28a745', 'costo_consulta' => 50.00],
    
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

       
        
        $this->command->info('✓ ' . count($especialidadesData) . ' especialidades creadas exitosamente.');
    }
}