<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class UpdateProductosFiscalSeeder extends Seeder
{
    public function run(): void
    {
        // Actualizar productos existentes con configuración fiscal por defecto
        Producto::whereNull('aplica_iva')->update([
            'aplica_iva' => true,
            'exento_iva' => false,
            'iva_alicuota' => 16.00,
            'categoria_fiscal' => 'producto'
        ]);

        // Configurar medicamentos como exentos de IVA (si aplica según legislación)
        Producto::where('es_medicamento', true)->update([
            'aplica_iva' => false,
            'exento_iva' => true,
            'iva_alicuota' => 0.00,
            'categoria_fiscal' => 'medicamento'
        ]);

        $this->command->info('Productos actualizados con configuración fiscal');
    }
}