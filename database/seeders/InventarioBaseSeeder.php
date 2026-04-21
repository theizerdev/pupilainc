<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Almacen;
use App\Models\Proveedor;

class InventarioBaseSeeder extends Seeder
{
    public function run(): void
    {
        $empresa  = \App\Models\Empresa::first();
        $sucursal = \App\Models\Sucursal::first();

        // Almacenes
        $almacenes = [
            ['nombre' => 'Almacén Principal',   'descripcion' => 'Almacén central de la clínica',          'ubicacion' => 'Planta baja',  'es_principal' => true],
            ['nombre' => 'Farmacia Interna',     'descripcion' => 'Medicamentos y productos farmacéuticos', 'ubicacion' => 'Planta baja',  'es_principal' => false],
            ['nombre' => 'Sala de Enfermería',   'descripcion' => 'Insumos de uso inmediato en enfermería', 'ubicacion' => 'Piso 1',       'es_principal' => false],
            ['nombre' => 'Consultorio General',  'descripcion' => 'Materiales de uso en consultorios',      'ubicacion' => 'Piso 2',       'es_principal' => false],
        ];

        foreach ($almacenes as $data) {
            Almacen::firstOrCreate(
                ['nombre' => $data['nombre'], 'empresa_id' => $empresa?->id, 'sucursal_id' => $sucursal?->id],
                array_merge($data, ['status' => true, 'empresa_id' => $empresa?->id, 'sucursal_id' => $sucursal?->id])
            );
        }

        // Proveedores
        $proveedores = [
            ['nombre' => 'Distribuidora Médica Nacional', 'documento' => 'J-12345678-9', 'contacto' => 'Carlos Pérez',   'telefono' => '+58 212 555-0001', 'email' => 'ventas@dmn.com',      'condiciones_pago' => '30 días'],
            ['nombre' => 'Farmacéutica del Sur C.A.',     'documento' => 'J-98765432-1', 'contacto' => 'María González', 'telefono' => '+58 212 555-0002', 'email' => 'pedidos@farmsur.com', 'condiciones_pago' => 'Contado'],
            ['nombre' => 'Insumos Clínicos Express',      'documento' => 'J-11223344-5', 'contacto' => 'Luis Rodríguez', 'telefono' => '+58 212 555-0003', 'email' => 'info@icexpress.com',  'condiciones_pago' => '15 días'],
            ['nombre' => 'TechMed Equipos S.A.',          'documento' => 'J-55667788-0', 'contacto' => 'Ana Martínez',   'telefono' => '+58 212 555-0004', 'email' => 'ventas@techmed.com',  'condiciones_pago' => '60 días'],
        ];

        foreach ($proveedores as $data) {
            Proveedor::firstOrCreate(
                ['nombre' => $data['nombre'], 'empresa_id' => $empresa?->id, 'sucursal_id' => $sucursal?->id],
                array_merge($data, ['status' => true, 'empresa_id' => $empresa?->id, 'sucursal_id' => $sucursal?->id])
            );
        }

        $this->command->info('✅ Almacenes creados: ' . count($almacenes));
        $this->command->info('✅ Proveedores creados: ' . count($proveedores));
    }
}
