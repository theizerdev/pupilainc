# Notas de Débito - Proceso y Normativa Venezuela

## 1. ¿Qué es una Nota de Débito?

La **Nota de Débito** es un documento fiscal que se emite para **aumentar** el monto de una factura previamente emitida. Es el documento contrario a la Nota de Crédito.

### Características
- ✅ **Aumenta el débito fiscal** (IVA a pagar)
- ✅ **Debe referenciar** la factura original
- ✅ **Montos positivos** (adicionales)
- ✅ **Número de control fiscal** secuencial
- ✅ **Motivo obligatorio** documentado

## 2. Marco Legal

### Normativa Aplicable
- **Código Orgánico Tributario (COT)** - Artículos 54-56
- **Ley del IVA** - Artículo 30
- **Providencia SNAT/2022/000013** - Facturación Electrónica
- **Reglamento de la Ley del IVA** - Artículo 62

### Principios Legales
- ❌ **NO se puede modificar** una factura emitida
- ✅ **SE DEBE emitir** Nota de Débito para aumentos
- ✅ **Ambos documentos** quedan registrados
- ✅ **Numeración secuencial** obligatoria
- ✅ **Conservación** por 10 años

## 3. Motivos Válidos para Emitir Nota de Débito

### 3.1 Intereses por Mora
**Situación**: Cliente no pagó en el plazo establecido
```
Factura Original: Bs. 1,000.00 (Vencimiento: 30 días)
Días de mora: 15 días
Interés: 2% mensual = Bs. 10.00
Nota de Débito: Bs. 10.00
Total a Pagar: Bs. 1,010.00
```

### 3.2 Recargos por Servicios Adicionales
**Situación**: Se prestaron servicios adicionales no incluidos en factura original
```
Factura Original: Consulta médica Bs. 500.00
Servicio Adicional: Examen de laboratorio Bs. 200.00
Nota de Débito: Bs. 200.00
Total: Bs. 700.00
```

### 3.3 Corrección de Errores (Monto Menor)
**Situación**: Se facturó un monto menor al correcto
```
Factura Original: Bs. 800.00 (Error: debió ser Bs. 1,000.00)
Diferencia: Bs. 200.00
Nota de Débito: Bs. 200.00
Total Correcto: Bs. 1,000.00
```

### 3.4 Ajuste por Tipo de Cambio
**Situación**: Factura en USD con tasa incorrecta
```
Factura Original: $100 x Bs. 35 = Bs. 3,500.00
Tasa Correcta: $100 x Bs. 38 = Bs. 3,800.00
Diferencia: Bs. 300.00
Nota de Débito: Bs. 300.00
```

### 3.5 Gastos de Cobranza
**Situación**: Gastos incurridos para cobrar la factura
```
Factura Original: Bs. 2,000.00
Gastos de Cobranza: Bs. 50.00
Nota de Débito: Bs. 50.00
Total: Bs. 2,050.00
```

### 3.6 Penalidades Contractuales
**Situación**: Incumplimiento de términos del contrato
```
Factura Original: Bs. 5,000.00
Penalidad por incumplimiento: Bs. 500.00
Nota de Débito: Bs. 500.00
Total: Bs. 5,500.00
```

## 4. Proceso Paso a Paso

### Paso 1: Factura Original Emitida
```
Factura N°: F001-00000100
Control Fiscal: 00000100
Fecha: 15/01/2024
Cliente: Clínica San José (J-12345678-9)
Monto: Bs. 1,000.00
Condición: Crédito 30 días
Vencimiento: 14/02/2024
Estado: APROBADO
```

### Paso 2: Situación que Genera Nota de Débito
```
Fecha Actual: 01/03/2024
Días de Mora: 15 días
Interés Aplicable: 2% mensual
Cálculo: Bs. 1,000.00 x 2% x (15/30) = Bs. 10.00
```

### Paso 3: Emisión de Nota de Débito
```
Nota de Débito N°: ND01-00000001
Control Fiscal: 00000101  ← Siguiente número secuencial
Fecha: 01/03/2024
Factura Afectada: F001-00000100
Cliente: Clínica San José (J-12345678-9)
Concepto: Intereses por mora (15 días)
Monto: Bs. 10.00
Base Imponible: Bs. 10.00
IVA (16%): Bs. 1.60
Total: Bs. 11.60
Estado: APROBADO
```

### Paso 4: Actualización de Saldos
```
Factura F001-00000100:
- Monto Original: Bs. 1,000.00
- Notas de Débito: Bs. 11.60
- Notas de Crédito: Bs. 0.00
- Saldo Total: Bs. 1,011.60
- Estado: APROBADO (pendiente de pago)

Nota de Débito ND01-00000001:
- Estado: APROBADO
- Factura Origen: F001-00000100
- Tipo: Intereses por Mora
```

## 5. Flujo de Numeración

```
Documento              | Serie-Número  | Control Fiscal | Estado    | Monto
-----------------------|---------------|----------------|-----------|----------
Factura                | F001-00000100 | 00000100       | APROBADO  | 1,000.00
Factura                | F001-00000101 | 00000101       | APROBADO  | 2,500.00
Nota de Débito         | ND01-00000001 | 00000102       | APROBADO  | +50.00
Factura                | F001-00000102 | 00000103       | APROBADO  | 800.00
Nota de Crédito        | NC01-00000001 | 00000104       | APROBADO  | -200.00
Nota de Débito         | ND01-00000002 | 00000105       | APROBADO  | +30.00
```

## 6. Implementación en Base de Datos

### Tabla: pagos
```sql
-- Factura Original
INSERT INTO pagos (
    id, tipo_pago, serie, numero, numero_control_fiscal,
    consulta_id, cliente_fiscal_id, fecha, total, estado
) VALUES (
    100, 'factura', 'F001', '00000100', '00000100',
    50, 10, '2024-01-15', 1000.00, 'aprobado'
);

-- Nota de Débito
INSERT INTO pagos (
    id, tipo_pago, serie, numero, numero_control_fiscal,
    pago_origen_id, tipo_nota_debito_id, motivo_nota,
    consulta_id, cliente_fiscal_id, fecha, total, estado
) VALUES (
    101, 'nota_debito', 'ND01', '00000001', '00000101',
    100, 1, 'Intereses por mora - 15 días',
    50, 10, '2024-03-01', 11.60, 'aprobado'
);
```

### Tabla: tipos_nota_debito
```sql
CREATE TABLE tipos_nota_debito (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE,
    nombre VARCHAR(100),
    descripcion TEXT,
    aplica_iva BOOLEAN DEFAULT true,
    requiere_autorizacion BOOLEAN DEFAULT false,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Datos iniciales
INSERT INTO tipos_nota_debito (codigo, nombre, descripcion, aplica_iva) VALUES
('INT_MORA', 'Intereses por Mora', 'Intereses generados por pago fuera de plazo', true),
('SERV_ADIC', 'Servicios Adicionales', 'Servicios prestados adicionales a la factura original', true),
('CORR_MONTO', 'Corrección de Monto', 'Ajuste por error en monto facturado (menor)', true),
('AJUSTE_TC', 'Ajuste Tipo de Cambio', 'Ajuste por diferencia en tasa de cambio', true),
('GAST_COBR', 'Gastos de Cobranza', 'Gastos incurridos en proceso de cobranza', true),
('PENAL_CONT', 'Penalidad Contractual', 'Penalidades por incumplimiento de contrato', true);
```

## 7. Código de Implementación

### Método para Generar Nota de Débito

```php
public function generarNotaDebito(
    Pago $facturaOriginal, 
    int $tipoNotaDebitoId,
    string $motivo, 
    float $monto,
    array $detalles = []
) {
    // Validar que la factura esté aprobada
    if ($facturaOriginal->estado !== Pago::ESTADO_APROBADO) {
        throw new \Exception('Solo se pueden generar notas de débito para facturas aprobadas');
    }

    // Validar monto positivo
    if ($monto <= 0) {
        throw new \Exception('El monto debe ser mayor a cero');
    }

    // Validar tipo de nota
    $tipoNota = TipoNotaDebito::findOrFail($tipoNotaDebitoId);
    if (!$tipoNota->activo) {
        throw new \Exception('Tipo de nota de débito no activo');
    }

    DB::beginTransaction();
    try {
        // Obtener numeración
        $numeracion = Pago::generarNumero(
            'nota_debito',
            $facturaOriginal->empresa_id,
            $facturaOriginal->sucursal_id
        );

        // Calcular impuestos
        $baseImponible = $monto;
        $ivaMonto = $tipoNota->aplica_iva ? ($baseImponible * 0.16) : 0;
        $total = $baseImponible + $ivaMonto;

        // Crear nota de débito
        $notaDebito = Pago::create([
            'tipo_pago' => Pago::TIPO_NOTA_DEBITO,
            'pago_origen_id' => $facturaOriginal->id,
            'tipo_nota_debito_id' => $tipoNotaDebitoId,
            'serie_id' => $numeracion['serie_id'],
            'serie' => $numeracion['serie'],
            'numero' => $numeracion['numero'],
            'numero_control_fiscal' => $numeracion['control_fiscal'],
            'motivo_nota' => $motivo,
            'consulta_id' => $facturaOriginal->consulta_id,
            'cliente_fiscal_id' => $facturaOriginal->cliente_fiscal_id,
            'caja_id' => $facturaOriginal->caja_id,
            'fecha' => now(),
            'user_id' => auth()->id(),
            'subtotal' => $baseImponible,
            'base_imponible' => $baseImponible,
            'iva_porcentaje' => $tipoNota->aplica_iva ? 16 : 0,
            'iva_monto' => $ivaMonto,
            'total' => $total,
            'total_usd' => $total,
            'total_bs' => $total * $facturaOriginal->tasa_cambio_usd,
            'tasa_cambio_usd' => $facturaOriginal->tasa_cambio_usd,
            'metodo_pago' => $facturaOriginal->metodo_pago,
            'estado' => Pago::ESTADO_APROBADO,
            'empresa_id' => $facturaOriginal->empresa_id,
            'sucursal_id' => $facturaOriginal->sucursal_id,
            'es_factura_fiscal' => $facturaOriginal->es_factura_fiscal,
        ]);

        // Crear detalles
        if (empty($detalles)) {
            // Detalle por defecto
            $notaDebito->detalles()->create([
                'descripcion' => $tipoNota->nombre . ': ' . $motivo,
                'cantidad' => 1,
                'precio_unitario' => $baseImponible * $facturaOriginal->tasa_cambio_usd,
                'subtotal' => $baseImponible * $facturaOriginal->tasa_cambio_usd,
                'aplica_iva' => $tipoNota->aplica_iva,
                'exento_iva' => !$tipoNota->aplica_iva,
                'iva_alicuota' => $tipoNota->aplica_iva ? 16 : 0,
            ]);
        } else {
            // Detalles personalizados
            foreach ($detalles as $detalle) {
                $notaDebito->detalles()->create([
                    'baremo_id' => $detalle['baremo_id'] ?? null,
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'] * $facturaOriginal->tasa_cambio_usd,
                    'subtotal' => $detalle['subtotal'] * $facturaOriginal->tasa_cambio_usd,
                    'aplica_iva' => $detalle['aplica_iva'] ?? true,
                    'exento_iva' => $detalle['exento_iva'] ?? false,
                    'iva_alicuota' => ($detalle['exento_iva'] ?? false) ? 0 : 16,
                ]);
            }
        }

        // Registrar auditoría
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'crear_nota_debito',
            'model_type' => 'Pago',
            'model_id' => $notaDebito->id,
            'metadata' => [
                'factura_origen_id' => $facturaOriginal->id,
                'factura_numero' => $facturaOriginal->numero_completo,
                'tipo_nota' => $tipoNota->nombre,
                'monto' => $total,
            ],
        ]);

        DB::commit();
        return $notaDebito;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Cálculo de Saldo Total de Factura

```php
public function getSaldoTotalAttribute(): float
{
    $montoOriginal = $this->total;
    
    // Sumar notas de débito
    $notasDebito = $this->notasDebito()
        ->where('estado', Pago::ESTADO_APROBADO)
        ->sum('total');
    
    // Restar notas de crédito
    $notasCredito = $this->notasCredito()
        ->where('estado', Pago::ESTADO_APROBADO)
        ->sum('total');
    
    return $montoOriginal + $notasDebito + abs($notasCredito);
}
```

## 8. Validaciones Obligatorias

### Al Crear Nota de Débito
```php
// 1. Factura debe existir y estar aprobada
if (!$factura || $factura->estado !== 'aprobado') {
    return error('Factura no válida');
}

// 2. Monto debe ser positivo
if ($monto <= 0) {
    return error('El monto debe ser mayor a cero');
}

// 3. Tipo de nota debe ser válido
if (!$tipoNota || !$tipoNota->activo) {
    return error('Tipo de nota no válido');
}

// 4. Motivo obligatorio (mínimo 10 caracteres)
if (strlen($motivo) < 10) {
    return error('Debe especificar el motivo detalladamente');
}

// 5. Usuario debe tener permisos
if (!auth()->user()->can('crear_notas_debito')) {
    return error('No tiene permisos');
}

// 6. Requiere autorización (según tipo)
if ($tipoNota->requiere_autorizacion && !$autorizacionId) {
    return error('Este tipo de nota requiere autorización');
}
```

## 9. Diferencias: Nota de Crédito vs Nota de Débito

| Aspecto | Nota de Crédito | Nota de Débito |
|---------|----------------|----------------|
| **Propósito** | Disminuir monto | Aumentar monto |
| **Signo** | Negativo (-) | Positivo (+) |
| **Efecto IVA** | Reduce débito fiscal | Aumenta débito fiscal |
| **Motivos** | Anulación, devolución, descuento | Intereses, recargos, correcciones |
| **Estado Factura** | Puede cambiar a CANCELADO | Permanece APROBADO |
| **Saldo** | Disminuye | Aumenta |

## 10. Reportes y Consultas

### Reporte de Notas de Débito
```sql
SELECT 
    nd.fecha,
    nd.numero_completo as nota_numero,
    nd.numero_control_fiscal,
    f.numero_completo as factura_numero,
    tnd.nombre as tipo_nota,
    nd.motivo_nota,
    cf.razon_social as cliente,
    nd.base_imponible,
    nd.iva_monto,
    nd.total_bs,
    nd.estado,
    u.name as usuario
FROM pagos nd
INNER JOIN pagos f ON nd.pago_origen_id = f.id
LEFT JOIN tipos_nota_debito tnd ON nd.tipo_nota_debito_id = tnd.id
LEFT JOIN clientes_fiscales cf ON nd.cliente_fiscal_id = cf.id
LEFT JOIN users u ON nd.user_id = u.id
WHERE nd.tipo_pago = 'nota_debito'
  AND nd.empresa_id = ?
  AND nd.fecha BETWEEN ? AND ?
ORDER BY nd.numero_control_fiscal ASC;
```

### Saldo por Factura
```sql
SELECT 
    f.numero_completo,
    f.fecha,
    cf.razon_social,
    f.total as monto_original,
    COALESCE(SUM(CASE WHEN nd.tipo_pago = 'nota_debito' THEN nd.total ELSE 0 END), 0) as notas_debito,
    COALESCE(SUM(CASE WHEN nc.tipo_pago = 'nota_credito' THEN ABS(nc.total) ELSE 0 END), 0) as notas_credito,
    f.total + 
    COALESCE(SUM(CASE WHEN nd.tipo_pago = 'nota_debito' THEN nd.total ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN nc.tipo_pago = 'nota_credito' THEN ABS(nc.total) ELSE 0 END), 0) as saldo_total
FROM pagos f
LEFT JOIN pagos nd ON f.id = nd.pago_origen_id AND nd.tipo_pago = 'nota_debito' AND nd.estado = 'aprobado'
LEFT JOIN pagos nc ON f.id = nc.pago_origen_id AND nc.tipo_pago = 'nota_credito' AND nc.estado = 'aprobado'
LEFT JOIN clientes_fiscales cf ON f.cliente_fiscal_id = cf.id
WHERE f.tipo_pago = 'factura'
  AND f.empresa_id = ?
GROUP BY f.id
HAVING saldo_total > 0
ORDER BY f.fecha DESC;
```

## 11. Casos de Uso Prácticos

### Caso 1: Intereses por Mora
```php
$factura = Pago::find(100);
$diasMora = now()->diffInDays($factura->fecha_vencimiento);
$tasaInteres = 0.02; // 2% mensual
$interes = $factura->total * $tasaInteres * ($diasMora / 30);

$notaDebito = generarNotaDebito(
    facturaOriginal: $factura,
    tipoNotaDebitoId: 1, // INT_MORA
    motivo: "Intereses por mora de {$diasMora} días",
    monto: $interes
);
```

### Caso 2: Servicios Adicionales
```php
$serviciosAdicionales = [
    ['descripcion' => 'Examen de sangre', 'cantidad' => 1, 'precio' => 150],
    ['descripcion' => 'Radiografía', 'cantidad' => 1, 'precio' => 200],
];

$montoTotal = array_sum(array_column($serviciosAdicionales, 'precio'));

$notaDebito = generarNotaDebito(
    facturaOriginal: $factura,
    tipoNotaDebitoId: 2, // SERV_ADIC
    motivo: "Servicios médicos adicionales prestados",
    monto: $montoTotal,
    detalles: $serviciosAdicionales
);
```

### Caso 3: Corrección de Monto
```php
$montoFacturado = 800;
$montoCorrecto = 1000;
$diferencia = $montoCorrecto - $montoFacturado;

$notaDebito = generarNotaDebito(
    facturaOriginal: $factura,
    tipoNotaDebitoId: 3, // CORR_MONTO
    motivo: "Corrección de monto facturado incorrectamente",
    monto: $diferencia
);
```

## 12. Mejores Prácticas

1. ✅ **Documentar claramente** el motivo de cada nota
2. ✅ **Obtener autorización** para montos significativos
3. ✅ **Notificar al cliente** antes de emitir la nota
4. ✅ **Conservar evidencia** que justifique la nota
5. ✅ **Revisar cálculos** antes de emitir
6. ✅ **Registrar en auditoría** todas las notas
7. ✅ **Actualizar saldos** inmediatamente
8. ✅ **Generar reporte** mensual de notas emitidas

## 13. Errores Comunes a Evitar

❌ **NO usar nota de débito para:**
- Cobrar servicios nuevos (emitir nueva factura)
- Corregir datos del cliente (emitir nota de crédito y nueva factura)
- Aumentar precios retroactivamente sin justificación

✅ **SÍ usar nota de débito para:**
- Intereses contractuales por mora
- Servicios adicionales relacionados con la factura original
- Correcciones de montos facturados incorrectamente (menor)
- Ajustes autorizados por contrato

---

**Fecha de Documentación**: Febrero 2024
**Normativa**: COT, Ley IVA, SNAT/2022/000013
**Desarrollado por**: TheizerDev
