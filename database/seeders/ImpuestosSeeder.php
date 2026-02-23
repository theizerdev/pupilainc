<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImpuestoConfiguracion;
use App\Models\Empresa;

class ImpuestosSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::with('sucursales')->get();

        foreach ($empresas as $empresa) {
            foreach ($empresa->sucursales as $sucursal) {
                // IVA - 16%
                ImpuestoConfiguracion::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'sucursal_id' => $sucursal->id,
                        'codigo' => 'IVA'
                    ],
                [
                    'nombre' => 'Impuesto al Valor Agregado',
                    'tipo' => 'porcentaje',
                    'porcentaje' => 16.00,
                    'aplica_servicios' => true,
                    'aplica_productos' => true,
                    'metodos_pago_aplicables' => null, // Aplica a todos
                    'coletilla_fiscal' => null,
                    'activo' => true,
                    'orden' => 1
                ]
            );

                // IGTF - 3% con coletilla obligatoria
                ImpuestoConfiguracion::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'sucursal_id' => $sucursal->id,
                        'codigo' => 'IGTF'
                    ],
                [
                    'nombre' => 'Impuesto a las Grandes Transacciones Financieras',
                    'tipo' => 'porcentaje',
                    'porcentaje' => 3.00,
                    'aplica_servicios' => true,
                    'aplica_productos' => true,
                    'metodos_pago_aplicables' => [
                        'efectivo_usd',
                        'transferencia_usd',
                        'zelle',
                        'paypal'
                    ],
                    'coletilla_fiscal' => 'De conformidad con lo establecido en el artículo 1 del Decreto Constituyente mediante el cual se crea el Impuesto a las Grandes Transacciones Financieras, publicado en la Gaceta Oficial de la República Bolivariana de Venezuela N° 6.210 Extraordinario, de fecha 28 de febrero de 2016, y sus modificaciones, el monto de esta operación incluye el tres por ciento (3%) correspondiente al IGTF.',
                    'activo' => true,
                    'orden' => 2
                ]
                );
            }
        }
    }
}
