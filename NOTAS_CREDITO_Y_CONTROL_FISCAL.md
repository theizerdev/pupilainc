# Proceso de Notas de Crédito y Número de Control Fiscal - Venezuela

## 1. Número de Control Fiscal

### ¿Qué es el Número de Control Fiscal?

El **Número de Control Fiscal** es un número secuencial único asignado a cada documento fiscal (factura, nota de crédito, nota de débito) que permite al SENIAT llevar un control estricto de todos los documentos emitidos por un contribuyente.

### Características del Número de Control Fiscal

✅ **Secuencial**: Debe ser consecutivo sin saltos
✅ **Único**: No puede repetirse
✅ **Independiente**: Es diferente al número de factura
✅ **Obligatorio**: Para facturas fiscales autorizadas por SENIAT
✅ **Paralelo**: Corre en paralelo con la numeración de factura

### Ejemplo de Numeración

```
Factura N°: F001-00000123
Control Fiscal: 00000123

Factura N°: F001-00000124  
Control Fiscal: 00000124

Nota de Crédito N°: NC01-00000001
Control Fiscal: 00000125  ← Continúa la secuencia
```

### Implementación en el Sistema

```php
// Tabla: series
- serie: 'F001'
- correlativo_actual: 123
- control_fiscal_actual: 123
- longitud_correlativo: 8
- longitud_control_fiscal: 8
```

## 2. Proceso de Anulación con Nota de Crédito

### Marco Legal

Según la **Providencia Administrativa SNAT/2022/000013** y el **Código Orgánico Tributario**:

- ❌ **NO se pueden anular facturas** eliminándolas del sistema
- ❌ **NO se pueden saltar números** de factura o control fiscal
- ✅ **SE DEBE emitir una Nota de Crédito** para anular/reversar una factura
- ✅ **Ambos documentos quedan registrados** en el sistema

### Tipos de Notas de Crédito

1. **Anulación Total**: Reversa el 100% de la factura
2. **Anulación Parcial**: Reversa solo una parte del monto
3. **Devolución**: Por devolución de mercancía o servicios
4. **Descuento Posterior**: Descuentos aplicados después de la emisión
5. **Corrección de Errores**: Errores en datos o montos

### Proceso Paso a Paso

#### Paso 1: Factura Original Emitida
```
Factura N°: F001-00000100
Control Fiscal: 00000100
Fecha: 15/01/2024
Cliente: Juan Pérez (V-12345678)
Monto: Bs. 1,000.00
Estado: APROBADO
```

#### Paso 2: Cliente Solicita Anulación
- Motivo: Error en el servicio / Cancelación / Devolución
- Se debe verificar que la factura esté pagada o no

#### Paso 3: Emisión de Nota de Crédito
```
Nota de Crédito N°: NC01-00000001
Control Fiscal: 00000101  ← Siguiente número secuencial
Fecha: 20/01/2024
Factura Afectada: F001-00000100
Cliente: Juan Pérez (V-12345678)
Monto: Bs. 1,000.00
Motivo: Anulación por error en servicio
Estado: APROBADO
```

#### Paso 4: Actualización de Estados
```
Factura F001-00000100:
- Estado: ANULADO (o CANCELADO)
- Nota de Crédito Asociada: NC01-00000001
- Saldo Disponible: Bs. 0.00

Nota de Crédito NC01-00000001:
- Estado: APROBADO
- Factura Origen: F001-00000100
- Tipo: Anulación Total
```

### Flujo de Numeración Completo

```
Documento                    | N° Factura      | N° Control | Estado
----------------------------|-----------------|------------|----------
Factura                     | F001-00000098   | 00000098   | APROBADO
Factura                     | F001-00000099   | 00000099   | APROBADO
Factura (a anular)          | F001-00000100   | 00000100   | ANULADO
Nota de Crédito             | NC01-00000001   | 00000101   | APROBADO
Factura                     | F001-00000101   | 00000102   | APROBADO
Nota de Débito              | ND01-00000001   | 00000103   | APROBADO
Factura                     | F001-00000102   | 00000104   | APROBADO
```

### Reglas Importantes

1. ✅ **El número de control fiscal NUNCA se salta**
2. ✅ **Cada tipo de documento tiene su propia serie** (F001, NC01, ND01)
3. ✅ **El control fiscal es compartido** entre todos los tipos de documentos
4. ✅ **La factura anulada NO se elimina**, solo cambia de estado
5. ✅ **La nota de crédito debe referenciar** la factura original
6. ✅ **Ambos documentos deben conservarse** por 10 años

## 3. Implementación en Base de Datos

### Tabla: series

```sql
CREATE TABLE series (
    id BIGINT PRIMARY KEY,
    tipo_documento VARCHAR(50),        -- 'factura', 'nota_credito', 'nota_debito'
    serie VARCHAR(20),                 -- 'F001', 'NC01', 'ND01'
    correlativo_actual INT,            -- Número de documento actual
    control_fiscal_actual VARCHAR(20), -- Número de control fiscal actual
    longitud_correlativo INT,          -- Longitud del correlativo (ej: 8)
    longitud_control_fiscal INT,       -- Longitud del control fiscal (ej: 8)
    activo BOOLEAN,
    empresa_id BIGINT,
    sucursal_id BIGINT
);
```

### Tabla: pagos (facturas)

```sql
ALTER TABLE pagos ADD COLUMN numero_control_fiscal VARCHAR(20);
ALTER TABLE pagos ADD COLUMN pago_origen_id BIGINT; -- Referencia a factura original
ALTER TABLE pagos ADD COLUMN motivo_nota TEXT;      -- Motivo de la nota de crédito
```

### Ejemplo de Registros

```sql
-- Serie de Facturas
INSERT INTO series VALUES (1, 'factura', 'F001', 100, '00000100', 8, 8, true, 1, 1);

-- Serie de Notas de Crédito
INSERT INTO series VALUES (2, 'nota_credito', 'NC01', 1, '00000101', 8, 8, true, 1, 1);

-- Factura Original
INSERT INTO pagos (id, tipo_pago, serie, numero, numero_control_fiscal, total, estado)
VALUES (100, 'factura', 'F001', '00000100', '00000100', 1000.00, 'anulado');

-- Nota de Crédito
INSERT INTO pagos (id, tipo_pago, serie, numero, numero_control_fiscal, pago_origen_id, motivo_nota, total, estado)
VALUES (101, 'nota_credito', 'NC01', '00000001', '00000101', 100, 'Anulación por error', -1000.00, 'aprobado');
```

## 4. Código de Implementación

### Método para Generar Nota de Crédito

```php
public function generarNotaCredito(Pago $facturaOriginal, $motivo, $monto = null)
{
    // Validar que la factura esté aprobada
    if ($facturaOriginal->estado !== Pago::ESTADO_APROBADO) {
        throw new \Exception('Solo se pueden anular facturas aprobadas');
    }

    // Validar que no esté ya anulada
    if ($facturaOriginal->notasCredito()->where('estado', Pago::ESTADO_APROBADO)->exists()) {
        throw new \Exception('Esta factura ya tiene una nota de crédito');
    }

    DB::beginTransaction();
    try {
        // Obtener numeración para nota de crédito
        $numeracion = Pago::generarNumero(
            'nota_credito',
            $facturaOriginal->empresa_id,
            $facturaOriginal->sucursal_id
        );

        // Obtener número de control fiscal (siguiente en la secuencia)
        $serie = Serie::find($numeracion['serie_id']);
        $numeroControlFiscal = $serie->numero_control_fiscal;

        // Crear nota de crédito
        $notaCredito = Pago::create([
            'tipo_pago' => Pago::TIPO_NOTA_CREDITO,
            'pago_origen_id' => $facturaOriginal->id,
            'serie_id' => $numeracion['serie_id'],
            'serie' => $numeracion['serie'],
            'numero' => $numeracion['numero'],
            'numero_control_fiscal' => $numeroControlFiscal,
            'motivo_nota' => $motivo,
            'consulta_id' => $facturaOriginal->consulta_id,
            'cliente_fiscal_id' => $facturaOriginal->cliente_fiscal_id,
            'fecha' => now(),
            'user_id' => auth()->id(),
            'subtotal' => -($monto ?? $facturaOriginal->subtotal),
            'total' => -($monto ?? $facturaOriginal->total),
            'total_bs' => -($monto ?? $facturaOriginal->total_bs),
            'total_usd' => -($monto ?? $facturaOriginal->total_usd),
            'tasa_cambio_usd' => $facturaOriginal->tasa_cambio_usd,
            'metodo_pago' => $facturaOriginal->metodo_pago,
            'estado' => Pago::ESTADO_APROBADO,
            'empresa_id' => $facturaOriginal->empresa_id,
            'sucursal_id' => $facturaOriginal->sucursal_id,
        ]);

        // Copiar detalles con montos negativos
        foreach ($facturaOriginal->detalles as $detalle) {
            $notaCredito->detalles()->create([
                'baremo_id' => $detalle->baremo_id,
                'descripcion' => $detalle->descripcion,
                'cantidad' => -$detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => -$detalle->subtotal,
            ]);
        }

        // Actualizar estado de factura original
        $facturaOriginal->update(['estado' => Pago::ESTADO_CANCELADO]);

        DB::commit();
        return $notaCredito;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Método Actualizado en Serie

```php
public function obtenerSiguienteNumero()
{
    $this->increment('correlativo_actual');
    
    // Incrementar también el número de control fiscal
    if ($this->control_fiscal_actual) {
        $controlActual = intval($this->control_fiscal_actual);
        $controlActual++;
        $this->control_fiscal_actual = str_pad(
            $controlActual, 
            $this->longitud_control_fiscal, 
            '0', 
            STR_PAD_LEFT
        );
        $this->save();
    }
    
    $this->refresh();
    
    return str_pad(
        $this->correlativo_actual, 
        $this->longitud_correlativo, 
        '0', 
        STR_PAD_LEFT
    );
}
```

## 5. Reportes y Auditoría

### Libro de Ventas

Debe incluir:
- Todas las facturas emitidas
- Todas las notas de crédito
- Todas las notas de débito
- Ordenadas por número de control fiscal

### Consulta SQL para Libro de Ventas

```sql
SELECT 
    tipo_pago,
    serie,
    numero,
    numero_control_fiscal,
    fecha,
    cliente_fiscal_id,
    base_imponible,
    iva_monto,
    total_bs,
    estado
FROM pagos
WHERE empresa_id = ?
  AND fecha BETWEEN ? AND ?
ORDER BY numero_control_fiscal ASC;
```

## 6. Validaciones Importantes

### Al Emitir Factura
- ✅ Verificar que la serie esté activa
- ✅ Generar número correlativo secuencial
- ✅ Generar número de control fiscal secuencial
- ✅ Ambos números deben incrementarse juntos

### Al Emitir Nota de Crédito
- ✅ Verificar que la factura original exista
- ✅ Verificar que la factura esté aprobada
- ✅ Verificar que no tenga nota de crédito previa
- ✅ El monto no puede exceder el saldo disponible
- ✅ Debe tener un motivo válido
- ✅ Generar nuevo número de control fiscal

### Auditoría
- ✅ No debe haber saltos en números de control fiscal
- ✅ Cada factura anulada debe tener su nota de crédito
- ✅ Los montos deben cuadrar
- ✅ Las fechas deben ser coherentes

## 7. Mejores Prácticas

1. **Nunca eliminar facturas**: Siempre usar notas de crédito
2. **Documentar motivos**: Siempre registrar el motivo de anulación
3. **Mantener trazabilidad**: Vincular nota de crédito con factura original
4. **Conservar documentos**: Mínimo 10 años según ley
5. **Auditorías periódicas**: Verificar secuencia de números de control
6. **Backup regular**: Respaldar base de datos diariamente
7. **Control de acceso**: Solo usuarios autorizados pueden emitir notas de crédito

## 8. Casos Especiales

### Anulación Parcial
```php
// Anular solo Bs. 500 de una factura de Bs. 1,000
$notaCredito = generarNotaCredito($factura, 'Descuento por reclamo', 500);
// Saldo disponible de la factura: Bs. 500
```

### Múltiples Notas de Crédito
```php
// Primera nota de crédito: Bs. 300
$nc1 = generarNotaCredito($factura, 'Descuento 1', 300);

// Segunda nota de crédito: Bs. 200
$nc2 = generarNotaCredito($factura, 'Descuento 2', 200);

// Saldo disponible: Bs. 500 (de Bs. 1,000 original)
```

## 9. Migración Requerida

```bash
php artisan make:migration add_numero_control_fiscal_to_series_table
php artisan migrate
```

## 10. Conclusión

El manejo correcto del número de control fiscal y las notas de crédito es fundamental para:
- ✅ Cumplir con la normativa del SENIAT
- ✅ Evitar sanciones fiscales
- ✅ Mantener trazabilidad completa
- ✅ Facilitar auditorías
- ✅ Garantizar integridad de datos

---

**Fecha de Documentación**: Febrero 2024
**Normativa Aplicable**: 
- Providencia Administrativa SNAT/2022/000013
- Código Orgánico Tributario
- Ley del IVA

**Desarrollado por**: TheizerDev
