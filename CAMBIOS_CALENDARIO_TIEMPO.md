# Mejoras en el Calendario - Validación de Tiempo e Intervalos

## Fecha de Implementación
11 de mayo, 2026

## Problema Identificado
Al hacer clic cerca de la línea roja (indicador de tiempo actual) en el calendario, aparecía el error:
```
No se pueden crear citas en el pasado. (Seleccionado: 21:45, Ahora: 21:47)
```

Aunque la diferencia era mínima (2 minutos), el sistema bloqueaba la creación de la cita.

## Causas del Problema

1. **Validación demasiado estricta**: La función `isPastDateTime` comparaba minuto exacto sin margen de tolerancia
2. **Intervalo de slot grande**: El `slotDuration` estaba configurado en 10 minutos, pero el `snapDuration` en 5 minutos
3. **Comparación por strings**: Se usaba comparación lexicográfica de strings en lugar de objetos Date reales

## Cambios Realizados

### 1. Mejora en la Validación de Tiempo (`isPastDateTime`)
**Archivo**: `public/js/app-calendario-general.js`  
**Líneas**: 1209-1249

#### Antes:
- Comparación estricta de strings hasta el minuto
- Si `selectedMin < nowMin`, bloqueaba inmediatamente
- Sin margen de tolerancia

#### Después:
- Uso de objetos `Date` para cálculo preciso
- Margen de tolerancia de **5 minutos** configurable
- Permite crear citas hasta 5 minutos en el pasado
- Cálculo basado en diferencia real de milisegundos

```javascript
// MARGEN DE TOLERANCIA: Permitir citas hasta 5 minutos en el pasado
var toleranceMinutes = -5;

if (diffMinutes < toleranceMinutes) {
    showPastAlert(...);
    return true;
}
```

### 2. Reducción del Intervalo de Slot
**Archivo**: `public/js/app-calendario-general.js`  
**Línea**: 1771

#### Antes:
```javascript
slotDuration: '00:10:00',  // 10 minutos
snapDuration: '00:05:00',  // 5 minutos (inconsistente)
```

#### Después:
```javascript
slotDuration: '00:05:00',  // 5 minutos
snapDuration: '00:05:00',  // 5 minutos (consistente)
```

### 3. Compactación Visual de Slots (CSS)
**Archivo**: `public/css/calendar-custom.css`  
**Líneas**: 6-20

#### Antes:
- Altura de slot: ~40px (default de FullCalendar)
- Mucho espacio vertical entre horas
- Demasiado scroll necesario

#### Después:
```css
.fc .fc-timegrid-slot {
    height: 24px !important; /* Reducido de ~40px a 24px */
}

.fc .fc-timegrid-slot-label-cushion {
    padding-top: 2px !important;
    padding-bottom: 2px !important;
}

.fc .fc-timegrid-event {
    min-height: 20px !important;
}
```

**Resultado**: La vista de día/semana ahora es mucho más compacta, mostrando más horas en pantalla sin tanto scroll.

## Beneficios

1. **Mejor experiencia de usuario**: No se bloquean citas válidas por diferencias mínimas de tiempo
2. **Mayor precisión**: Slots de 5 minutos permiten selección más precisa al hacer clic
3. **Consistencia**: `slotDuration` y `snapDuration` ahora coinciden
4. **Flexibilidad**: El margen de tolerancia es fácilmente configurable

## Configuración Adicional (Opcional)

Si deseas hacer el margen de tolerancia configurable desde Laravel:

### En el componente Livewire (Calendario.php):
```php
public $toleranciaMinutos = 5; // Margen en minutos
```

### En la vista (calendario.blade.php):
```php
initCalendarioGeneral(
    @json($eventos),
    @json($citaEstadoColores),
    @json($citaEstadoLabels),
    @json($timezone),
    @json($toleranciaMinutos ?? 5)
);
```

### En el JavaScript (app-calendario-general.js):
```javascript
function initCalendarioGeneral(eventos, colores, labels, timezone, toleranciaMinutos = 5) {
    
    function isPastDateTime(date, info) {
        var toleranceMinutes = -(toleranciaMinutos || 5);
        // ... rest of the code ...
    }
}
```

## Pruebas Recomendadas

1. ✅ Hacer clic en un slot que acaba de pasar (ej: 2-3 minutos atrás)
2. ✅ Hacer clic en un slot futuro inmediato
3. ✅ Intentar crear una cita con 10+ minutos en el pasado (debe bloquearse)
4. ✅ Verificar que los slots de 5 minutos se muestran correctamente
5. ✅ Probar arrastrar y soltar eventos cerca de la línea roja

## Notas Técnicas

- El margen de -5 minutos es un valor razonable que considera:
  - Tiempo de reacción del usuario
  - Pequeños delays en la interfaz
  - Diferencias de sincronización entre cliente/servidor
  
- Si necesitas un margen diferente, modifica la variable `toleranceMinutes` en la línea 1234

- La reducción a slots de 5 minutos mejora la precisión pero aumenta ligeramente la densidad visual del calendario
