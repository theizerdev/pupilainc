# Mejoras en el Sistema de Facturación - Forma Libre Venezuela

## Requisitos Fiscales Implementados

### 1. Datos Obligatorios del Emisor (Empresa)
✅ **Razón Social** - Nombre completo de la empresa
✅ **RIF** - Registro de Información Fiscal
✅ **Dirección Fiscal** - Dirección completa registrada ante SENIAT
✅ **Teléfono** - Número de contacto
✅ **Email** - Correo electrónico de contacto

### 2. Datos del Documento
✅ **Tipo de Documento** - FACTURA / BOLETA / RECIBO
✅ **Número de Factura** - Serie y correlativo (Ej: F001-00000123)
✅ **Número de Control Fiscal** - Para facturas fiscales (opcional en forma libre)
✅ **Fecha de Emisión** - Fecha en formato dd/mm/yyyy
✅ **Condición de Pago** - CONTADO / CRÉDITO

### 3. Datos del Cliente
✅ **Razón Social / Nombre Completo** - Identificación del cliente
✅ **RIF / Cédula** - Documento de identidad fiscal
✅ **Dirección** - Dirección del cliente (cuando aplique)
✅ **Teléfono** - Número de contacto
✅ **Email** - Correo electrónico (opcional)

### 4. Detalle de Servicios/Productos
✅ **Número de Item** - Numeración secuencial
✅ **Descripción** - Descripción clara del servicio/producto
✅ **Cantidad** - Cantidad de unidades
✅ **Precio Unitario** - Precio por unidad en Bs
✅ **Total por Item** - Subtotal de cada línea

### 5. Cálculos Fiscales
✅ **Subtotal** - Suma de todos los items
✅ **Descuento** - Descuentos aplicados (si aplica)
✅ **Base Imponible** - Monto sobre el cual se calcula el IVA
✅ **Monto Exento** - Servicios/productos exentos de IVA
✅ **IVA** - Impuesto al Valor Agregado (16%)
✅ **IGTF** - Impuesto a las Grandes Transacciones Financieras (3%)
✅ **Total a Pagar** - Monto total en Bolívares

### 6. Información Adicional
✅ **Método de Pago** - Efectivo, Transferencia, Pago Móvil, etc.
✅ **Referencia** - Número de referencia bancaria (cuando aplica)
✅ **Tasa de Cambio BCV** - Tasa del día para conversión USD/Bs
✅ **Equivalente en USD** - Monto total en dólares
✅ **Firma Autorizada** - Espacio para firma y sello

### 7. Leyenda Legal
✅ Texto indicando cumplimiento con Providencia Administrativa SNAT

## Formatos Disponibles

### Formato A4 (210mm x 297mm)
- Una factura completa por página
- Mayor espacio para detalles
- Ideal para facturas con muchos items
- Presentación más formal y profesional

### Formato Media Carta (Letter dividido)
- Dos facturas por página (Original y Copia)
- Optimización de papel
- Formato compacto
- Ideal para facturas simples

## Características Técnicas

### Diseño Visual
- Encabezado destacado con datos de la empresa
- Secciones claramente delimitadas con bordes
- Uso de negritas para resaltar información importante
- Tablas con bordes para mejor legibilidad
- Espaciado adecuado entre secciones

### Cumplimiento Legal
- Todos los campos requeridos por SENIAT para forma libre
- Cálculo correcto de impuestos (IVA e IGTF)
- Separación de base imponible y montos exentos
- Trazabilidad completa del documento
- Numeración secuencial controlada

### Conversión de Moneda
- Tasa BCV del día de emisión
- Montos en Bolívares (moneda principal)
- Equivalente en USD para referencia
- Aplicación correcta de IGTF para pagos en divisas

## Uso del Sistema

### Generar Factura A4
```php
$this->downloadReceipt($pagoId, 'a4');
```

### Generar Factura Media Carta
```php
$this->downloadReceipt($pagoId, 'letter');
```

## Validaciones Implementadas

1. **Datos de Empresa**: Verifica que existan RIF, razón social y dirección fiscal
2. **Datos de Cliente**: Valida documento de identidad y nombre/razón social
3. **Cálculos**: Validación automática de totales y subtotales
4. **Impuestos**: Cálculo correcto según tipo de servicio y método de pago
5. **Numeración**: Control de series y correlativos únicos

## Mejoras Futuras Sugeridas

- [ ] Código QR con validación de autenticidad
- [ ] Integración con SENIAT para facturas electrónicas
- [ ] Firma digital electrónica
- [ ] Envío automático por email al cliente
- [ ] Almacenamiento en la nube
- [ ] Generación de reportes fiscales mensuales
- [ ] Libro de ventas automatizado

## Notas Importantes

⚠️ **Forma Libre**: Este formato cumple con los requisitos de forma libre establecidos por SENIAT. No requiere autorización previa pero debe mantener todos los datos fiscales obligatorios.

⚠️ **Conservación**: Las facturas deben conservarse por un período mínimo de 10 años según la legislación venezolana.

⚠️ **Numeración**: La numeración debe ser secuencial y sin saltos. El sistema controla esto automáticamente.

⚠️ **IVA**: El porcentaje de IVA puede variar según disposiciones del SENIAT. Actualmente está configurado en 16%.

⚠️ **IGTF**: Se aplica automáticamente a transacciones en divisas (USD, EUR) o métodos de pago electrónicos internacionales.

## Soporte Legal

Este sistema está diseñado para cumplir con:
- Código Orgánico Tributario
- Ley del IVA
- Providencia Administrativa SNAT/2022/000013
- Resoluciones del SENIAT vigentes

---

**Fecha de Implementación**: 2024
**Versión**: 1.0
**Desarrollado por**: TheizerDev
