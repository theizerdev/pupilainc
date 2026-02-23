<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CuentaContable;
use App\Models\Empresa;

class PlanCuentasSeeder extends Seeder
{
    public function run()
    {
        $empresa = Empresa::first();
        
        if (!$empresa) {
            $this->command->error('No hay empresas registradas');
            return;
        }

        $cuentas = [
            // ACTIVOS
            ['codigo' => '1', 'nombre' => 'ACTIVO', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '1.1', 'nombre' => 'ACTIVO CORRIENTE', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 2, 'padre' => '1', 'acepta_movimientos' => false],
            ['codigo' => '1.1.01', 'nombre' => 'CAJA Y BANCOS', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 3, 'padre' => '1.1', 'acepta_movimientos' => false],
            ['codigo' => '1.1.01.001', 'nombre' => 'Caja General', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'padre' => '1.1.01', 'acepta_movimientos' => true],
            ['codigo' => '1.1.01.002', 'nombre' => 'Banco Cuenta Corriente', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'padre' => '1.1.01', 'acepta_movimientos' => true],
            ['codigo' => '1.1.02', 'nombre' => 'CUENTAS POR COBRAR', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 3, 'padre' => '1.1', 'acepta_movimientos' => false],
            ['codigo' => '1.1.02.001', 'nombre' => 'Cuentas por Cobrar Pacientes', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'padre' => '1.1.02', 'acepta_movimientos' => true],

            // PASIVOS
            ['codigo' => '2', 'nombre' => 'PASIVO', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '2.1', 'nombre' => 'PASIVO CORRIENTE', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '2', 'acepta_movimientos' => false],
            ['codigo' => '2.1.01', 'nombre' => 'IMPUESTOS POR PAGAR', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'acepta_movimientos' => false],
            ['codigo' => '2.1.01.001', 'nombre' => 'IVA por Pagar', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'padre' => '2.1.01', 'acepta_movimientos' => true],
            ['codigo' => '2.1.01.002', 'nombre' => 'IGTF por Pagar', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'padre' => '2.1.01', 'acepta_movimientos' => true],

            // PATRIMONIO
            ['codigo' => '3', 'nombre' => 'PATRIMONIO', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '3.1', 'nombre' => 'CAPITAL', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '3', 'acepta_movimientos' => false],
            ['codigo' => '3.1.01', 'nombre' => 'Capital Social', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '3.1', 'acepta_movimientos' => true],

            // INGRESOS
            ['codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '4.1', 'nombre' => 'INGRESOS OPERACIONALES', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '4', 'acepta_movimientos' => false],
            ['codigo' => '4.1.01', 'nombre' => 'Ingresos por Consultas Médicas', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '4.1', 'acepta_movimientos' => true],
            ['codigo' => '4.1.02', 'nombre' => 'Ingresos por Procedimientos', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '4.1', 'acepta_movimientos' => true],

            // EGRESOS
            ['codigo' => '5', 'nombre' => 'EGRESOS', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '5.1', 'nombre' => 'GASTOS OPERACIONALES', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 2, 'padre' => '5', 'acepta_movimientos' => false],
            ['codigo' => '5.1.01', 'nombre' => 'Gastos de Personal', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'padre' => '5.1', 'acepta_movimientos' => true],
            ['codigo' => '5.1.02', 'nombre' => 'Gastos de Servicios', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'padre' => '5.1', 'acepta_movimientos' => true],

            // CUENTAS ESPECIALES
            ['codigo' => '4.2', 'nombre' => 'DEVOLUCIONES Y REBAJAS', 'tipo' => 'ingreso', 'naturaleza' => 'deudora', 'nivel' => 2, 'padre' => '4', 'acepta_movimientos' => false],
            ['codigo' => '4.2.01', 'nombre' => 'Devoluciones en Ventas', 'tipo' => 'ingreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'padre' => '4.2', 'acepta_movimientos' => true],
        ];

        $cuentasCreadas = [];

        foreach ($cuentas as $cuenta) {
            $padre_id = null;
            if (isset($cuenta['padre'])) {
                $padre_id = $cuentasCreadas[$cuenta['padre']]->id ?? null;
            }

            $cuentaCreada = CuentaContable::create([
                'codigo' => $cuenta['codigo'],
                'nombre' => $cuenta['nombre'],
                'tipo' => $cuenta['tipo'],
                'naturaleza' => $cuenta['naturaleza'],
                'nivel' => $cuenta['nivel'],
                'cuenta_padre_id' => $padre_id,
                'acepta_movimientos' => $cuenta['acepta_movimientos'],
                'activo' => true,
                'empresa_id' => $empresa->id,
                'sucursal_id' => null
            ]);

            $cuentasCreadas[$cuenta['codigo']] = $cuentaCreada;
        }

        $this->command->info('Plan de cuentas creado exitosamente');
    }
}
