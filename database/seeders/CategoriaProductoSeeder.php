<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaProducto;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = \App\Models\Empresa::first();
        $sucursal = \App\Models\Sucursal::first();

        $categorias = [
            [
                'nombre'      => 'Medicamentos',
                'descripcion' => 'Fármacos, medicamentos de venta libre y bajo receta médica',
                'color'       => '#3B82F6',
                'icono'       => 'ri ri-medicine-bottle-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Insumos Médicos',
                'descripcion' => 'Material médico desechable, guantes, jeringas y equipos de curación',
                'color'       => '#10B981',
                'icono'       => 'ri ri-first-aid-kit-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Equipos y Dispositivos',
                'descripcion' => 'Equipos médicos, dispositivos de diagnóstico y monitoreo',
                'color'       => '#8B5CF6',
                'icono'       => 'ri ri-stethoscope-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Laboratorio',
                'descripcion' => 'Reactivos, tubos de ensayo y materiales para análisis clínicos',
                'color'       => '#F59E0B',
                'icono'       => 'ri ri-test-tube-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Óptica',
                'descripcion' => 'Lentes, monturas, soluciones oftálmicas y accesorios ópticos',
                'color'       => '#06B6D4',
                'icono'       => 'ri ri-eye-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Suplementos y Nutrición',
                'descripcion' => 'Vitaminas, suplementos alimenticios y productos nutricionales',
                'color'       => '#EC4899',
                'icono'       => 'ri ri-capsule-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Higiene y Cuidado Personal',
                'descripcion' => 'Productos de higiene, antisépticos y cuidado personal',
                'color'       => '#14B8A6',
                'icono'       => 'ri ri-drop-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Papelería y Oficina',
                'descripcion' => 'Formularios médicos, papelería administrativa y artículos de oficina',
                'color'       => '#6B7280',
                'icono'       => 'ri ri-file-text-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Limpieza y Desinfección',
                'descripcion' => 'Productos de limpieza, desinfectantes y esterilización',
                'color'       => '#EF4444',
                'icono'       => 'ri ri-recycle-line',
                'status'      => true,
            ],
            [
                'nombre'      => 'Tecnología Médica',
                'descripcion' => 'Software, hardware y accesorios tecnológicos para uso clínico',
                'color'       => '#1D4ED8',
                'icono'       => 'ri ri-computer-line',
                'status'      => true,
            ],
        ];

        foreach ($categorias as $data) {
            CategoriaProducto::firstOrCreate(
                [
                    'nombre'      => $data['nombre'],
                    'empresa_id'  => $empresa?->id,
                    'sucursal_id' => $sucursal?->id,
                ],
                array_merge($data, [
                    'empresa_id'  => $empresa?->id,
                    'sucursal_id' => $sucursal?->id,
                ])
            );
        }

        $this->command->info('✅ Categorías de producto creadas: ' . count($categorias));
    }
}
