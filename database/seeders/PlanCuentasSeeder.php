<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CuentaContable;
use App\Models\Empresa;

class PlanCuentasSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $this->crearPlanCuentas($empresa->id, $empresa->sucursales->first()?->id);
        }
    }

    private function crearPlanCuentas($empresaId, $sucursalId)
    {
        $cuentas = [
            // 1. ACTIVO
            ['codigo' => '1', 'nombre' => 'ACTIVO', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '1.1', 'nombre' => 'ACTIVO CORRIENTE', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '1'],
            
            // 1.1.01 - EFECTIVO Y EQUIVALENTES
            ['codigo' => '1.1.01', 'nombre' => 'EFECTIVO Y EQUIVALENTES', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '1.1'],
            ['codigo' => '1.1.01.001', 'nombre' => 'Caja Chica', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.01'],
            ['codigo' => '1.1.01.002', 'nombre' => 'Banco Nacional Bs', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.01'],
            ['codigo' => '1.1.01.003', 'nombre' => 'Banco Nacional USD', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.01'],
            ['codigo' => '1.1.01.004', 'nombre' => 'Pago Móvil', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.01'],
            ['codigo' => '1.1.01.005', 'nombre' => 'Zelle/PayPal', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.01'],

            // 1.1.02 - CUENTAS POR COBRAR
            ['codigo' => '1.1.02', 'nombre' => 'CUENTAS POR COBRAR', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '1.1'],
            ['codigo' => '1.1.02.001', 'nombre' => 'Cuentas por Cobrar Pacientes', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.02'],

            // 1.1.03 - INVENTARIOS
            ['codigo' => '1.1.03', 'nombre' => 'INVENTARIOS', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '1.1'],
            ['codigo' => '1.1.03.001', 'nombre' => 'Inventario de Productos', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.03'],
            ['codigo' => '1.1.03.002', 'nombre' => 'Inventario de Medicamentos', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '1.1.03'],

            // 2. PASIVO
            ['codigo' => '2', 'nombre' => 'PASIVO', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '2.1', 'nombre' => 'PASIVO CORRIENTE', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '2'],
            
            // 2.1.01 - IMPUESTOS POR PAGAR
            ['codigo' => '2.1.01', 'nombre' => 'IMPUESTOS POR PAGAR', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '2.1'],
            ['codigo' => '2.1.01.001', 'nombre' => 'IVA por Pagar', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '2.1.01'],
            ['codigo' => '2.1.01.002', 'nombre' => 'IGTF por Pagar', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '2.1.01'],

            // 2.1.02 - CUENTAS POR PAGAR
            ['codigo' => '2.1.02', 'nombre' => 'CUENTAS POR PAGAR', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '2.1'],
            ['codigo' => '2.1.02.001', 'nombre' => 'Cuentas por Pagar Médicos', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '2.1.02'],
            ['codigo' => '2.1.02.002', 'nombre' => 'Cuentas por Pagar Proveedores', 'tipo' => 'pasivo', 'naturaleza' => 'acreedora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '2.1.02'],

            // 3. PATRIMONIO
            ['codigo' => '3', 'nombre' => 'PATRIMONIO', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '3.1', 'nombre' => 'CAPITAL', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '3'],
            ['codigo' => '3.1.01', 'nombre' => 'Capital Social', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '3.1'],
            
            ['codigo' => '3.2', 'nombre' => 'RESULTADOS', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '3'],
            ['codigo' => '3.2.01', 'nombre' => 'Resultados Acumulados', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '3.2'],
            ['codigo' => '3.2.02', 'nombre' => 'Resultado del Ejercicio', 'tipo' => 'patrimonio', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '3.2'],

            // 4. INGRESOS
            ['codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '4.1', 'nombre' => 'INGRESOS OPERACIONALES', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '4'],
            ['codigo' => '4.1.01', 'nombre' => 'Ingresos por Consultas Médicas', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '4.1'],
            ['codigo' => '4.1.02', 'nombre' => 'Ingresos por Procedimientos', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '4.1'],
            ['codigo' => '4.1.03', 'nombre' => 'Ventas de Productos', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '4.1'],
            ['codigo' => '4.1.04', 'nombre' => 'Ventas de Medicamentos', 'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '4.1'],

            ['codigo' => '4.2', 'nombre' => 'DEVOLUCIONES Y REBAJAS', 'tipo' => 'ingreso', 'naturaleza' => 'deudora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '4'],
            ['codigo' => '4.2.01', 'nombre' => 'Devoluciones en Ventas', 'tipo' => 'ingreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => true, 'padre' => '4.2'],

            // 5. GASTOS
            ['codigo' => '5', 'nombre' => 'GASTOS', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 1, 'acepta_movimientos' => false],
            ['codigo' => '5.1', 'nombre' => 'COSTOS Y GASTOS OPERACIONALES', 'tipo' => 'costo', 'naturaleza' => 'deudora', 'nivel' => 2, 'acepta_movimientos' => false, 'padre' => '5'],
            
            // 5.1.01 - COSTO DE VENTAS
            ['codigo' => '5.1.01', 'nombre' => 'COSTO DE VENTAS', 'tipo' => 'costo', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '5.1'],
            ['codigo' => '5.1.01.001', 'nombre' => 'Honorarios Médicos', 'tipo' => 'costo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.01'],
            ['codigo' => '5.1.01.002', 'nombre' => 'Costo de Ventas - Productos', 'tipo' => 'costo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.01'],
            ['codigo' => '5.1.01.003', 'nombre' => 'Costo de Ventas - Medicamentos', 'tipo' => 'costo', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.01'],

            // 5.1.02 - GASTOS DE PERSONAL
            ['codigo' => '5.1.02', 'nombre' => 'GASTOS DE PERSONAL', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '5.1'],
            ['codigo' => '5.1.02.001', 'nombre' => 'Sueldos y Salarios', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.02'],

            // 5.1.03 - GASTOS ADMINISTRATIVOS
            ['codigo' => '5.1.03', 'nombre' => 'GASTOS ADMINISTRATIVOS', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 3, 'acepta_movimientos' => false, 'padre' => '5.1'],
            ['codigo' => '5.1.03.001', 'nombre' => 'Servicios Públicos', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.03'],
            ['codigo' => '5.1.03.002', 'nombre' => 'Alquiler', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.03'],
            ['codigo' => '5.1.03.003', 'nombre' => 'Materiales y Suministros Médicos', 'tipo' => 'egreso', 'naturaleza' => 'deudora', 'nivel' => 4, 'acepta_movimientos' => true, 'padre' => '5.1.03'],
        ];

        $cuentasCreadas = [];

        foreach ($cuentas as $cuentaData) {
            $cuentaPadreId = null;
            
            if (isset($cuentaData['padre'])) {
                $cuentaPadreId = $cuentasCreadas[$cuentaData['padre']] ?? null;
            }

            $cuenta = CuentaContable::create([
                'codigo' => $cuentaData['codigo'],
                'nombre' => $cuentaData['nombre'],
                'tipo' => $cuentaData['tipo'],
                'naturaleza' => $cuentaData['naturaleza'],
                'nivel' => $cuentaData['nivel'],
                'cuenta_padre_id' => $cuentaPadreId,
                'acepta_movimientos' => $cuentaData['acepta_movimientos'],
                'descripcion' => 'Cuenta creada automáticamente por el sistema',
                'activo' => true,
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
            ]);

            $cuentasCreadas[$cuentaData['codigo']] = $cuenta->id;
        }

        $this->command->info("Plan de cuentas creado para empresa ID: {$empresaId}");
    }
}