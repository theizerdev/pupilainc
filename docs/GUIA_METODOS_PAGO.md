# Guía para Agregar Nuevos Métodos de Pago

## Sistema Dinámico de Métodos de Pago

El sistema ahora usa un **helper centralizado** (`MetodoPagoHelper`) que permite agregar nuevos métodos de pago sin modificar código en múltiples archivos.

---

## 📍 Ubicación del Helper

```
app/Helpers/MetodoPagoHelper.php
```

---

## ✅ Cómo Agregar un Nuevo Método de Pago

### Paso 1: Abrir el archivo `MetodoPagoHelper.php`

### Paso 2: Agregar el método en las 3 constantes principales

#### Ejemplo: Agregar "Criptomoneda Bitcoin"

```php
const NOMBRES_AMIGABLES = [
    // ... métodos existentes ...
    
    // Criptomonedas
    'bitcoin' => 'Bitcoin (BTC)',  // ← AGREGAR AQUÍ
];

const ICONOS = [
    // ... iconos existentes ...
    
    // Criptomonedas
    'bitcoin' => 'ri ri-bitcoin-line text-warning',  // ← AGREGAR AQUÍ
];

const CATEGORIAS = [
    // ... categorías existentes ...
    
    // Criptomonedas
    'bitcoin' => 'CRIPTOMONEDAS',  // ← AGREGAR AQUÍ
];
```

### Paso 3: ¡Listo! El método aparecerá automáticamente en:

- ✅ Detalle de caja (resumen por método)
- ✅ Tabla de pagos
- ✅ Exportaciones Excel
- ✅ Notificaciones WhatsApp
- ✅ Cualquier vista que use los helpers

---

## 🎨 Convenciones de Iconos

Usamos **Remix Icon** (ya instalado en el proyecto). Algunos ejemplos:

| Tipo | Icono Recomendado | Clase CSS |
|------|------------------|-----------|
| Efectivo | 💵 Money | `ri ri-money-dollar-circle-line text-success` |
| Transferencia | 🏦 Bank | `ri ri-bank-line text-info` |
| Tarjeta Débito | 💳 Card | `ri ri-bank-card-line text-primary` |
| Tarjeta Crédito | 💳 Card | `ri ri-bank-card-line text-warning` |
| Móvil/Pago Digital | 📱 Phone | `ri ri-smartphone-line text-primary` |
| PayPal | 🅿️ PayPal | `ri ri-paypal-line text-info` |
| Crypto | ₿ Bitcoin | `ri ri-bitcoin-line text-warning` |
| Desconocido | ❓ Question | `ri ri-question-line text-muted` |

### Colores disponibles:
- `text-success` - Verde (efectivo)
- `text-primary` - Azul (débito, transferencias)
- `text-info` - Cyan (transferencias bancarias)
- `text-warning` - Amarillo/Naranja (crédito, crypto)
- `text-danger` - Rojo
- `text-purple` - Púrpura (Zelle, servicios especiales)
- `text-muted` - Gris (desconocidos)

---

## 📊 Categorías Sugeridas

Las categorías se usan para agrupar en reportes:

- `EFECTIVO` - Pagos en efectivo
- `TRANSFERENCIAS` - Transferencias bancarias, pagos móviles, Zelle, PayPal
- `TARJETA CRÉDITO` - Todas las tarjetas de crédito
- `TARJETA DÉBITO` - Todas las tarjetas de débito
- `CRIPTOMONEDAS` - Bitcoin, USDT, Ethereum, etc.
- `OTROS` - Métodos no categorizados (fallback automático)

---

## 🔧 Fallback Automático

Si agregas un método pero **olvidas configurarlo**, el sistema lo maneja automáticamente:

- **Nombre**: Convierte `snake_case` a `Title Case`
  - Ejemplo: `pago_especial` → `Pago Especial`
  
- **Icono**: Muestra un icono de pregunta gris
  - `ri ri-question-line text-muted`
  
- **Categoría**: Asigna `OTROS`

Esto significa que **el sistema nunca se rompe**, incluso con métodos no configurados.

---

## 🚀 Ejemplos Prácticos

### Ejemplo 1: Agregar "Nequi" (Colombia)

```php
const NOMBRES_AMIGABLES = [
    'nequi' => 'Nequi',
];

const ICONOS = [
    'nequi' => 'ri ri-smartphone-line text-primary',
];

const CATEGORIAS = [
    'nequi' => 'TRANSFERENCIAS',
];
```

### Ejemplo 2: Agregar "Mercado Pago"

```php
const NOMBRES_AMIGABLES = [
    'mercado_pago' => 'Mercado Pago',
];

const ICONOS = [
    'mercado_pago' => 'ri ri-hand-coin-line text-info',
];

const CATEGORIAS = [
    'mercado_pago' => 'TRANSFERENCIAS',
];
```

### Ejemplo 3: Agregar "Cheque"

```php
const NOMBRES_AMIGABLES = [
    'cheque' => 'Cheque',
];

const ICONOS = [
    'cheque' => 'ri ri-bill-line text-warning',
];

const CATEGORIAS = [
    'cheque' => 'OTROS',
];
```

---

## 📝 Dónde se Usa el Helper

El helper se usa automáticamente en estos archivos:

1. **Vistas Blade:**
   - `resources/views/livewire/admin/cajas/show.blade.php`
   
2. **Componentes Livewire:**
   - `app/Livewire/Admin/Cajas/Show.php`
   - `app/Livewire/Admin/Cajas/Index.php` (si aplica)
   
3. **Exportaciones Excel:**
   - Todos los exports de cajas y pagos

4. **Modelos:**
   - `app/Models/Pago.php` (cálculos de IGTF)
   - `app/Models/Caja.php` (agrupación por método)

---

## ⚠️ Importante

1. **No modifiques** los arrays hardcodeados en vistas o componentes
2. **Siempre usa** `MetodoPagoHelper::getNombreAmigable()`, `getIcono()`, `getCategoria()`
3. **Mantén consistencia** en las claves (usa `snake_case`)
4. **Prueba** después de agregar un nuevo método revisando el detalle de caja

---

## 🔄 Migración desde Código Antiguo

Si encuentras código antiguo como este:

```php
// ❌ ANTES (hardcodeado)
$nombre = match($metodo) {
    'efectivo' => 'Efectivo',
    'transferencia' => 'Transferencia',
    default => ucfirst($metodo)
};
```

Reemplázalo por:

```php
// ✅ DESPUÉS (usando helper)
$nombre = MetodoPagoHelper::getNombreAmigable($metodo);
```

---

## 💡 Beneficios de Este Enfoque

✅ **Centralizado**: Un solo lugar para configurar  
✅ **Escalable**: Agregar métodos es trivial  
✅ **Seguro**: Fallback automático evita errores  
✅ **Consistente**: Mismo nombre/icono en todo el sistema  
✅ **Mantenible**: No hay duplicación de código  

---

## 🆘 Soporte

Si tienes dudas sobre qué icono usar o cómo categorizar un método, consulta:
- Remix Icon Gallery: https://remixicon.com/
- O revisa los métodos ya configurados como referencia
