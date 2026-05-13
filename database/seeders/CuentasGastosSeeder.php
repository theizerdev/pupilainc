<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CuentaContable;
use App\Models\Empresa;

class CuentasGastosSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $cuentas = [
                // Gastos Operativos Genéricos
                ['codigo' => '5.1.99.001', 'nombre' => 'Otros Gastos Operativos', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                
                // Gastos por Categoría
                ['codigo' => '5.1.04.001', 'nombre' => 'Gastos de Combustible', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.05.001', 'nombre' => 'Materiales y Suministros', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.06.001', 'nombre' => 'Servicios Públicos', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.07.001', 'nombre' => 'Mantenimiento y Reparaciones', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.08.001', 'nombre' => 'Gastos de Transporte', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.09.001', 'nombre' => 'Alimentación', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
                ['codigo' => '5.1.10.001', 'nombre' => 'Papelería y Útiles', 'tipo' => 'egreso', 'naturaleza' => 'deudora'],
            ];

            foreach ($cuentas as $cuentaData) {
                CuentaContable::firstOrCreate(
                    [
                        'codigo' => $cuentaData['codigo'],
                        'empresa_id' => $empresa->id,
                    ],
                    [
                        'nombre' => $cuentaData['nombre'],
                        'tipo' => $cuentaData['tipo'],
                        'naturaleza' => $cuentaData['naturaleza'],
                        'nivel' => 4,
                        'cuenta_padre_id' => null,
                        'acepta_movimientos' => true,
                        'activo' => true,
                    ]
                );
            }

            echo "Cuentas de gastos creadas para empresa: {$empresa->nombre}\n";
        }
    }
}
