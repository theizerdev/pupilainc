# Debido Proceso de Pagos y Facturación - Venezuela

## 1. Marco Legal y Normativo

### Leyes Aplicables
- **Código Orgánico Tributario (COT)**
- **Ley del Impuesto al Valor Agregado (IVA)**
- **Providencia Administrativa SNAT/2022/000013** - Facturación Electrónica
- **Providencia SNAT/2019/000071** - Libros Fiscales
- **Ley del IGTF** - Impuesto a las Grandes Transacciones Financieras

### Principios Fundamentales
✅ **Trazabilidad**: Todo pago debe ser rastreable
✅ **Integridad**: Los datos no pueden ser alterados
✅ **Secuencialidad**: Numeración sin saltos
✅ **Conservación**: Documentos por 10 años mínimo
✅ **Transparencia**: Información clara y completa

## 2. Tipos de Documentos Fiscales

### 2.1 Factura
**Uso**: Venta de bienes o prestación de servicios
**Características**:
- Genera débito fiscal (IVA)
- Debe incluir todos los datos fiscales
- Numeración secuencial obligatoria
- No se puede eliminar, solo anular con nota de crédito

### 2.2 Boleta
**Uso**: Ventas menores, servicios simples
**Características**:
- Versión simplificada de factura
- Menos requisitos formales
- Válida para montos menores
- También requiere numeración secuencial

### 2.3 Recibo
**Uso**: Comprobante de pago recibido
**Características**:
- No genera débito fiscal
- Comprueba recepción de dinero
- Puede ser por abonos o pagos parciales
- Debe referenciar la factura pagada

### 2.4 Nota de Crédito
**Uso**: Anular o reducir facturas emitidas
**Características**:
- Reduce el débito fiscal
- Debe referenciar factura original
- Montos negativos
- Motivo obligatorio

### 2.5 Nota de Débito
**Uso**: Aumentar el monto de una factura
**Características**:
- Aumenta el débito fiscal
- Por intereses, recargos, correcciones
- Debe referenciar factura original
- Montos positivos adicionales

## 3. Flujo Completo del Proceso de Pago

### 3.1 Apertura de Caja

#### Requisitos Previos
- ✅ Usuario autorizado
- ✅ Fecha y hora de apertura
- ✅ Monto inicial en caja
- ✅ Observaciones de apertura

#### Proceso
```php
// 1. Verificar que no exista caja abierta
$cajaAbierta = Caja::obtenerCajaAbierta($empresaId, $sucursalId);
if ($cajaAbierta) {
    throw new Exception('Ya existe una caja abierta');
}

// 2. Crear nueva caja
$caja = Caja::crearCajaDiaria(
    empresaId: $empresaId,
    sucursalId: $sucursalId,
    montoInicial: $montoInicial,
    observaciones: $observaciones,
    userId: auth()->id()
);
```

#### Validaciones
- ❌ No puede haber dos cajas abiertas simultáneamente
- ✅ Debe registrarse el monto inicial
- ✅ Debe haber un responsable asignado
- ✅ Fecha y hora deben ser correctas

### 3.2 Registro de Consulta Médica

#### Datos Requeridos
- Paciente (con datos fiscales si aplica)
- Médico tratante
- Tipo de consulta
- Diagnóstico
- Tratamiento
- Fecha y hora

#### Estado de Consulta
```
PENDIENTE → EN_CURSO → FINALIZADA → PAGADA
```

### 3.3 Generación de Factura

#### Paso 1: Validaciones Previas
```php
// Verificar caja abierta
if (!$caja || $caja->estado !== 'abierta') {
    throw new Exception('No hay caja abierta');
}

// Verificar consulta finalizada
if ($consulta->estado !== 'finalizada') {
    throw new Exception('La consulta debe estar finalizada');
}

// Verificar que no esté ya facturada
if ($consulta->pagos()->where('estado', 'aprobado')->exists()) {
    throw new Exception('Esta consulta ya tiene factura');
}
```

#### Paso 2: Obtener Numeración
```php
$numeracion = Pago::generarNumero(
    tipo: 'factura',
    empresaId: $empresa->id,
    sucursalId: $sucursal->id
);

// Resultado:
// [
//     'serie_id' => 1,
//     'serie' => 'F001',
//     'numero' => '00000123',
//     'control_fiscal' => '00000123'
// ]
```

#### Paso 3: Calcular Impuestos

##### IVA (16%)
```php
// Base Imponible: Servicios gravados
$baseImponible = 0;
$montoExento = 0;

foreach ($detalles as $detalle) {
    if ($detalle->exento_iva) {
        $montoExento += $detalle->subtotal;
    } elseif ($detalle->aplica_iva) {
        $baseImponible += $detalle->subtotal;
    }
}

$ivaMonto = $baseImponible * 0.16;
```

##### IGTF (3%)
```php
// Se aplica a pagos en divisas o medios electrónicos internacionales
$aplicaIGTF = in_array($metodoPago, [
    'efectivo_usd',
    'transferencia_usd',
    'zelle',
    'paypal',
    'tarjeta_internacional'
]);

$igtfMonto = $aplicaIGTF ? ($baseImponible + $ivaMonto) * 0.03 : 0;
```

##### Total
```php
$total = $baseImponible + $montoExento + $ivaMonto + $igtfMonto - $descuento;
```

#### Paso 4: Crear Factura
```php
DB::beginTransaction();
try {
    $factura = Pago::create([
        'tipo_pago' => 'factura',
        'consulta_id' => $consulta->id,
        'caja_id' => $caja->id,
        'serie_id' => $numeracion['serie_id'],
        'serie' => $numeracion['serie'],
        'numero' => $numeracion['numero'],
        'numero_control_fiscal' => $numeracion['control_fiscal'],
        'cliente_fiscal_id' => $clienteFiscal->id,
        'fecha' => now(),
        'user_id' => auth()->id(),
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'base_imponible' => $baseImponible,
        'monto_exento' => $montoExento,
        'iva_porcentaje' => 16,
        'iva_monto' => $ivaMonto,
        'igtf_porcentaje' => 3,
        'igtf_monto' => $igtfMonto,
        'aplica_igtf' => $aplicaIGTF,
        'total' => $total,
        'total_usd' => $total,
        'total_bs' => $total * $tasaBCV,
        'tasa_cambio_usd' => $tasaBCV,
        'metodo_pago' => $metodoPago,
        'referencia' => $referencia,
        'estado' => 'aprobado',
        'es_factura_fiscal' => true,
        'condicion_pago' => 'contado',
        'empresa_id' => $empresa->id,
        'sucursal_id' => $sucursal->id,
    ]);

    // Crear detalles
    foreach ($servicios as $servicio) {
        $factura->detalles()->create([
            'baremo_id' => $servicio->id,
            'descripcion' => $servicio->nombre,
            'cantidad' => $servicio->cantidad,
            'precio_unitario' => $servicio->precio,
            'subtotal' => $servicio->cantidad * $servicio->precio,
            'aplica_iva' => $servicio->aplica_iva,
            'exento_iva' => $servicio->exento_iva,
            'iva_alicuota' => $servicio->exento_iva ? 0 : 16,
        ]);
    }

    // Actualizar estado de consulta
    $consulta->update(['estado' => 'pagada']);

    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 3.4 Métodos de Pago

#### Efectivo en Bolívares
```php
'metodo_pago' => 'efectivo_bs',
'aplica_igtf' => false,
'total_bs' => $total * $tasaBCV,
'total_usd' => $total,
```

#### Efectivo en Dólares
```php
'metodo_pago' => 'efectivo_usd',
'aplica_igtf' => true,
'igtf_monto' => $total * 0.03,
'total_usd' => $total + ($total * 0.03),
'total_bs' => ($total + ($total * 0.03)) * $tasaBCV,
```

#### Transferencia Bancaria
```php
'metodo_pago' => 'transferencia_bs',
'referencia' => $numeroReferencia, // OBLIGATORIO
'aplica_igtf' => false,
```

#### Pago Móvil
```php
'metodo_pago' => 'pago_movil',
'referencia' => $numeroReferencia, // OBLIGATORIO (últimos 4 dígitos)
'aplica_igtf' => false,
```

#### Zelle / PayPal
```php
'metodo_pago' => 'zelle', // o 'paypal'
'referencia' => $transactionId,
'aplica_igtf' => true,
'igtf_monto' => $total * 0.03,
```

#### Pago Mixto
```php
'metodo_pago' => 'pago_mixto',
'es_pago_mixto' => true,
'detalles_pago_mixto' => [
    [
        'metodo' => 'efectivo_bs',
        'monto' => 500.00,
        'monto_bs' => 500.00,
        'referencia' => null
    ],
    [
        'metodo' => 'transferencia_bs',
        'monto' => 300.00,
        'monto_bs' => 300.00,
        'referencia' => '1234567890'
    ],
    [
        'metodo' => 'zelle',
        'monto_usd' => 20.00,
        'monto_bs' => 20.00 * $tasaBCV,
        'referencia' => 'ZELLE123',
        'aplica_igtf' => true
    ]
],
```

### 3.5 Anulación de Factura (Nota de Crédito)

#### Motivos Válidos
1. **Error en facturación**: Datos incorrectos
2. **Devolución de servicio**: Cliente insatisfecho
3. **Cancelación de consulta**: Consulta no realizada
4. **Descuento posterior**: Ajuste de precio
5. **Corrección de monto**: Error en cálculo

#### Proceso
```php
// 1. Validar factura
if ($factura->estado !== 'aprobado') {
    throw new Exception('Solo se pueden anular facturas aprobadas');
}

// 2. Verificar saldo disponible
$saldoDisponible = $factura->saldo_disponible;
if ($montoNota > $saldoDisponible) {
    throw new Exception('El monto excede el saldo disponible');
}

// 3. Generar numeración de nota de crédito
$numeracion = Pago::generarNumero('nota_credito', $empresa->id, $sucursal->id);

// 4. Crear nota de crédito
$notaCredito = Pago::create([
    'tipo_pago' => 'nota_credito',
    'pago_origen_id' => $factura->id,
    'serie_id' => $numeracion['serie_id'],
    'serie' => $numeracion['serie'],
    'numero' => $numeracion['numero'],
    'numero_control_fiscal' => $numeracion['control_fiscal'],
    'motivo_nota' => $motivo,
    'consulta_id' => $factura->consulta_id,
    'cliente_fiscal_id' => $factura->cliente_fiscal_id,
    'caja_id' => $factura->caja_id,
    'fecha' => now(),
    'user_id' => auth()->id(),
    'subtotal' => -$montoNota,
    'total' => -$montoNota,
    'total_usd' => -$montoNota,
    'total_bs' => -($montoNota * $factura->tasa_cambio_usd),
    'tasa_cambio_usd' => $factura->tasa_cambio_usd,
    'metodo_pago' => $factura->metodo_pago,
    'estado' => 'aprobado',
    'empresa_id' => $factura->empresa_id,
    'sucursal_id' => $factura->sucursal_id,
]);

// 5. Copiar detalles proporcionalmente
$factorProporcional = $montoNota / $factura->total;
foreach ($factura->detalles as $detalle) {
    $notaCredito->detalles()->create([
        'baremo_id' => $detalle->baremo_id,
        'descripcion' => $detalle->descripcion,
        'cantidad' => -($detalle->cantidad * $factorProporcional),
        'precio_unitario' => $detalle->precio_unitario,
        'subtotal' => -($detalle->subtotal * $factorProporcional),
    ]);
}

// 6. Actualizar estado de factura si es anulación total
if ($factura->saldo_disponible <= 0) {
    $factura->update(['estado' => 'cancelado']);
}
```

### 3.6 Cierre de Caja

#### Proceso
```php
// 1. Calcular totales por método de pago
$caja->calcularTotales();

// Resultado:
// - total_efectivo: Suma de pagos en efectivo
// - total_transferencias: Suma de transferencias
// - total_tarjetas: Suma de tarjetas
// - total_ingresos: Suma total
// - monto_final: monto_inicial + total_ingresos

// 2. Verificar cuadre
$diferencia = $montoFisicoContado - $caja->monto_final;
if (abs($diferencia) > 0.01) {
    // Registrar diferencia en observaciones
    $observaciones .= " | Diferencia: Bs. " . format_money($diferencia, 2);
}

// 3. Cerrar caja
$caja->cerrar($observaciones);

// 4. Generar reporte
$reporte = [
    'fecha' => $caja->fecha,
    'usuario' => $caja->usuario->name,
    'monto_inicial' => $caja->monto_inicial,
    'total_ingresos' => $caja->total_ingresos,
    'monto_final' => $caja->monto_final,
    'efectivo' => $caja->total_efectivo,
    'transferencias' => $caja->total_transferencias,
    'tarjetas' => $caja->total_tarjetas,
    'cantidad_facturas' => $caja->pagos()->where('tipo_pago', 'factura')->count(),
    'cantidad_notas_credito' => $caja->pagos()->where('tipo_pago', 'nota_credito')->count(),
];

// 5. Enviar notificación (opcional)
// WhatsApp, Email, etc.
```

## 4. Validaciones Obligatorias

### 4.1 Al Crear Factura
```php
// Validar caja abierta
if (!$caja || $caja->estado !== 'abierta') {
    return error('No hay caja abierta');
}

// Validar consulta
if (!$consulta || $consulta->estado !== 'finalizada') {
    return error('Consulta no válida');
}

// Validar cliente fiscal (si es factura fiscal)
if ($esFiscal && !$clienteFiscal) {
    return error('Debe registrar datos fiscales del cliente');
}

// Validar servicios
if (empty($servicios)) {
    return error('Debe agregar al menos un servicio');
}

// Validar método de pago
if (!in_array($metodoPago, $metodosValidos)) {
    return error('Método de pago no válido');
}

// Validar referencia (si aplica)
if (in_array($metodoPago, ['transferencia', 'pago_movil']) && empty($referencia)) {
    return error('Debe ingresar número de referencia');
}

// Validar tasa de cambio
if ($tasaBCV <= 0) {
    return error('Tasa de cambio no válida');
}
```

### 4.2 Al Crear Nota de Crédito
```php
// Validar factura origen
if (!$factura || $factura->tipo_pago !== 'factura') {
    return error('Factura no válida');
}

// Validar estado
if ($factura->estado !== 'aprobado') {
    return error('Solo se pueden anular facturas aprobadas');
}

// Validar saldo
if ($montoNota > $factura->saldo_disponible) {
    return error('Monto excede saldo disponible');
}

// Validar motivo
if (strlen($motivo) < 10) {
    return error('Debe especificar el motivo (mínimo 10 caracteres)');
}

// Validar permisos
if (!auth()->user()->can('crear_notas_credito')) {
    return error('No tiene permisos para crear notas de crédito');
}
```

### 4.3 Al Cerrar Caja
```php
// Validar que esté abierta
if ($caja->estado !== 'abierta') {
    return error('La caja ya está cerrada');
}

// Validar que sea el mismo usuario (opcional)
if ($caja->user_id !== auth()->id() && !auth()->user()->hasRole('Administrador')) {
    return error('Solo el usuario que abrió la caja puede cerrarla');
}

// Validar que no haya pagos pendientes
$pagosPendientes = $caja->pagos()->where('estado', 'pendiente')->count();
if ($pagosPendientes > 0) {
    return error("Hay {$pagosPendientes} pagos pendientes");
}
```

## 5. Reportes Fiscales Obligatorios

### 5.1 Libro de Ventas
```sql
SELECT 
    p.fecha,
    p.tipo_pago,
    p.numero_completo,
    p.numero_control_fiscal,
    COALESCE(cf.razon_social, pac.nombre_completo) as cliente,
    COALESCE(cf.documento_completo, pac.documento_identidad) as rif_ci,
    p.base_imponible,
    p.monto_exento,
    p.iva_monto,
    p.igtf_monto,
    p.total_bs,
    p.estado
FROM pagos p
LEFT JOIN clientes_fiscales cf ON p.cliente_fiscal_id = cf.id
LEFT JOIN consultas c ON p.consulta_id = c.id
LEFT JOIN pacientes pac ON c.paciente_id = pac.id
WHERE p.empresa_id = ?
  AND p.fecha BETWEEN ? AND ?
  AND p.tipo_pago IN ('factura', 'nota_credito', 'nota_debito')
ORDER BY p.numero_control_fiscal ASC;
```

### 5.2 Resumen de IVA
```sql
SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as periodo,
    SUM(CASE WHEN tipo_pago = 'factura' THEN base_imponible ELSE 0 END) as ventas_gravadas,
    SUM(CASE WHEN tipo_pago = 'factura' THEN monto_exento ELSE 0 END) as ventas_exentas,
    SUM(CASE WHEN tipo_pago = 'factura' THEN iva_monto ELSE 0 END) as iva_debito,
    SUM(CASE WHEN tipo_pago = 'nota_credito' THEN ABS(iva_monto) ELSE 0 END) as iva_credito,
    SUM(CASE WHEN tipo_pago = 'factura' THEN iva_monto ELSE 0 END) - 
    SUM(CASE WHEN tipo_pago = 'nota_credito' THEN ABS(iva_monto) ELSE 0 END) as iva_a_pagar
FROM pagos
WHERE empresa_id = ?
  AND fecha BETWEEN ? AND ?
  AND estado = 'aprobado'
GROUP BY DATE_FORMAT(fecha, '%Y-%m');
```

### 5.3 Resumen de IGTF
```sql
SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as periodo,
    metodo_pago,
    COUNT(*) as cantidad_transacciones,
    SUM(total_usd) as monto_base_usd,
    SUM(igtf_monto) as igtf_total,
    SUM(total_bs) as total_bs
FROM pagos
WHERE empresa_id = ?
  AND fecha BETWEEN ? AND ?
  AND aplica_igtf = true
  AND estado = 'aprobado'
GROUP BY DATE_FORMAT(fecha, '%Y-%m'), metodo_pago;
```

### 5.4 Arqueo de Caja
```sql
SELECT 
    c.fecha,
    c.numero_corte,
    u.name as cajero,
    c.monto_inicial,
    c.total_efectivo,
    c.total_transferencias,
    c.total_tarjetas,
    c.total_ingresos,
    c.monto_final,
    COUNT(p.id) as cantidad_transacciones,
    c.fecha_apertura,
    c.fecha_cierre,
    TIMESTAMPDIFF(HOUR, c.fecha_apertura, c.fecha_cierre) as horas_operacion
FROM cajas c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN pagos p ON c.id = p.caja_id AND p.estado = 'aprobado'
WHERE c.empresa_id = ?
  AND c.fecha BETWEEN ? AND ?
GROUP BY c.id
ORDER BY c.fecha DESC, c.numero_corte DESC;
```

## 6. Auditoría y Trazabilidad

### 6.1 Log de Auditoría
Cada operación debe registrar:
```php
AuditLog::create([
    'user_id' => auth()->id(),
    'action' => 'crear_factura', // o 'anular_factura', 'cerrar_caja'
    'model_type' => 'Pago',
    'model_id' => $pago->id,
    'old_values' => null,
    'new_values' => $pago->toArray(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'metadata' => [
        'caja_id' => $caja->id,
        'consulta_id' => $consulta->id,
        'monto' => $pago->total,
    ],
]);
```

### 6.2 Verificación de Integridad
```php
// Verificar secuencia de números de control fiscal
$saltos = DB::select("
    SELECT 
        a.numero_control_fiscal as anterior,
        b.numero_control_fiscal as siguiente,
        (CAST(b.numero_control_fiscal AS UNSIGNED) - 
         CAST(a.numero_control_fiscal AS UNSIGNED)) as diferencia
    FROM pagos a
    JOIN pagos b ON b.id = (
        SELECT MIN(id) 
        FROM pagos 
        WHERE numero_control_fiscal > a.numero_control_fiscal
        AND empresa_id = a.empresa_id
    )
    WHERE a.empresa_id = ?
    HAVING diferencia > 1
", [$empresaId]);

if (count($saltos) > 0) {
    // Alertar sobre saltos en numeración
    Log::warning('Saltos detectados en numeración fiscal', $saltos);
}
```

## 7. Mejores Prácticas

### 7.1 Seguridad
- ✅ Usar transacciones de base de datos
- ✅ Validar todos los inputs
- ✅ Registrar todas las operaciones
- ✅ Implementar permisos por rol
- ✅ Encriptar datos sensibles
- ✅ Backup diario automático

### 7.2 Operación
- ✅ Apertura de caja al inicio del día
- ✅ Cierre de caja al final del día
- ✅ Cuadre de caja diario
- ✅ Revisión de secuencia de numeración
- ✅ Conciliación bancaria semanal
- ✅ Declaración de IVA mensual

### 7.3 Documentación
- ✅ Conservar facturas físicas y digitales
- ✅ Mantener respaldos por 10 años
- ✅ Documentar procedimientos
- ✅ Capacitar al personal
- ✅ Actualizar según cambios legales

## 8. Casos Especiales

### 8.1 Factura a Crédito
```php
'condicion_pago' => 'credito',
'fecha_vencimiento_fiscal' => now()->addDays(30),
'estado' => 'aprobado', // Factura aprobada pero pago pendiente
```

### 8.2 Abonos Parciales
```php
// Crear recibo por cada abono
$recibo = Pago::create([
    'tipo_pago' => 'recibo',
    'pago_origen_id' => $factura->id,
    'total' => $montoAbono,
    'observaciones' => "Abono {$numeroAbono} de {$totalAbonos}",
]);
```

### 8.3 Devolución de Dinero
```php
// 1. Crear nota de crédito
$notaCredito = generarNotaCredito($factura, 'Devolución', $monto);

// 2. Registrar salida de caja
$caja->registrarEgreso([
    'concepto' => 'Devolución NC ' . $notaCredito->numero_completo,
    'monto' => $monto,
    'metodo' => $metodoPago,
]);
```

## 9. Checklist de Cumplimiento

### Diario
- [ ] Apertura de caja registrada
- [ ] Todas las facturas tienen número de control fiscal
- [ ] Referencias bancarias registradas
- [ ] Cierre de caja con cuadre
- [ ] Backup realizado

### Semanal
- [ ] Revisión de secuencia de numeración
- [ ] Conciliación bancaria
- [ ] Revisión de notas de crédito
- [ ] Verificación de saldos

### Mensual
- [ ] Libro de ventas generado
- [ ] Declaración de IVA
- [ ] Declaración de IGTF
- [ ] Reporte de ingresos
- [ ] Auditoría de documentos

### Anual
- [ ] Declaración de ISLR
- [ ] Auditoría externa
- [ ] Renovación de certificados
- [ ] Actualización de sistema
- [ ] Capacitación de personal

---

**Fecha de Documentación**: Febrero 2024
**Normativa Aplicable**: COT, Ley IVA, SNAT/2022/000013
**Desarrollado por**: TheizerDev
