<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener primer empresa y sucursal disponibles
        $empresa = \App\Models\Empresa::first();
        $sucursal = \App\Models\Sucursal::first();

        $categorias = [
            [
                'nombre' => 'Consulta Externa',
                'descripcion' => 'Consultas médicas generales y especializadas en régimen ambulatorio',
                'color' => '#3B82F6',
                'icono' => 'ri-stethoscope-line',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'Emergencias',
                'descripcion' => 'Atención de urgencias y emergencias médicas las 24 horas',
                'color' => '#EF4444',
                'icono' => 'ri-heart-pulse-line',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'nombre' => 'Hospitalización',
                'descripcion' => 'Servicio de internamiento para pacientes que requieren observación continua',
                'color' => '#8B5CF6',
                'icono' => 'ri-hospital-line',
                'orden' => 3,
                'activo' => true,
            ],
            [
                'nombre' => 'Diagnóstico',
                'descripcion' => 'Pruebas diagnósticas, laboratorios y estudios de imagen',
                'color' => '#10B981',
                'icono' => 'ri-microscope-line',
                'orden' => 4,
                'activo' => true,
            ],
            [
                'nombre' => 'Quirófano',
                'descripcion' => 'Procedimientos quirúrgicos y salas de operación',
                'color' => '#F59E0B',
                'icono' => 'ri-surgical-mask-line',
                'orden' => 5,
                'activo' => true,
            ],
            [
                'nombre' => 'Terapias',
                'descripcion' => 'Servicios de terapia física, ocupacional y rehabilitación',
                'color' => '#EC4899',
                'icono' => 'ri-hands-line',
                'orden' => 6,
                'activo' => true,
            ],
            [
                'nombre' => 'Farmacia',
                'descripcion' => 'Dispensación de medicamentos y productos farmacéuticos',
                'color' => '#06B6D4',
                'icono' => 'ri-capsule-line',
                'orden' => 7,
                'activo' => true,
            ],
            [
                'nombre' => 'Administrativo',
                'descripcion' => 'Trámites administrativos, facturación y atención al cliente',
                'color' => '#6B7280',
                'icono' => 'ri-file-text-line',
                'orden' => 8,
                'activo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                [
                    'nombre' => $categoria['nombre'],
                    'empresa_id' => $empresa?->id,
                    'sucursal_id' => $sucursal?->id,
                ],
                $categoria
            );
        }

        $this->command->info('✅ Categorías creadas exitosamente.');
    }
}
