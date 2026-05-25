<?php

return [
    'cuentas' => [
        // Cuentas principales
        'caja' => '1.1.01.001',
        'banco_bs' => '1.1.01.002',
        'banco_usd' => '1.1.01.003',
        'pago_movil' => '1.1.01.004',
        'zelle' => '1.1.01.005',
        'cxc' => '1.1.02.001',
        'iva' => '2.1.01.001',
        'igtf' => '2.1.01.002',
        'ingreso' => '4.1.01',
        'resultado_ejercicio' => '3.2.02',
        'resultado_acumulado' => '3.2.01',
        'gastos_operativos' => '5.1.99.001',  // Cuenta genérica para gastos

        // Cuentas de productos
        'venta_productos' => '4.1.03',
        'venta_medicamentos' => '4.1.04',
        'costo_productos' => '5.1.01.002',
        'costo_medicamentos' => '5.1.01.003',
        'inventario_productos' => '1.1.03.001',
        'inventario_medicamentos' => '1.1.03.002',
    ],

    'metodos_pago' => [
        'efectivo_bs' => '1.1.01.001',
        'efectivo_usd' => '1.1.01.001',
        'transferencia_bs' => '1.1.01.002',
        'transferencia_usd' => '1.1.01.003',
        'pago_movil' => '1.1.01.004',
        'zelle' => '1.1.01.005',
        'paypal' => '1.1.01.005',
        'tarjeta_credito' => '1.1.01.002',
        'tarjeta_debito' => '1.1.01.002',
    ],
];
