<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Sucursal;
use App\Models\Consultorio;

class ConsultoriosSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = Sucursal::all();
        if ($sucursales->isEmpty()) {
            return;
        }

        $base = [
            ['nombre' => 'Consultorio A', 'ubicacion' => 'Planta Baja - A'],
            ['nombre' => 'Consultorio B', 'ubicacion' => 'Planta Baja - B'],
            ['nombre' => 'Consultorio C', 'ubicacion' => 'Primer Piso - C'],
            ['nombre' => 'Consultorio D', 'ubicacion' => 'Primer Piso - D'],
            ['nombre' => 'Sala 1', 'ubicacion' => 'Emergencias - 1'],
            ['nombre' => 'Sala 2', 'ubicacion' => 'Emergencias - 2'],
        ];

        foreach ($sucursales as $sucursal) {
            foreach ($base as $item) {
                Consultorio::firstOrCreate(
                    [
                        'nombre' => $item['nombre'],
                        'empresa_id' => $sucursal->empresa_id,
                        'sucursal_id' => $sucursal->id,
                    ],
                    [
                        'ubicacion' => $item['ubicacion'],
                        'descripcion' => 'Consultorio base para atención médica',
                        'status' => true,
                    ]
                );
            }
        }
    }
}
