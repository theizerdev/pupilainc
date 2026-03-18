# Mejoras en la Vista Diaria y Semanal del Calendario

## Problema Identificado
En la vista diaria (`timeGridDay`) y semanal (`timeGridWeek`) del calendario, solo se visualizaba la hora actual, ocultando las horas anteriores. Además, el formato de hora no era claro para todos los usuarios.

## Solución Implementada

### 1. Rango Horario Completo Visible (8 AM - 11 PM)
- **Antes**: El sistema ajustaba dinámicamente `slotMinTime` y `slotMaxTime` para mostrar solo un rango cercano a la hora actual
- **Ahora**: Mantiene siempre visible el rango completo de **08:00 AM a 11:00 PM**

### 2. Formato de Hora AM/PM
- Las horas ahora se muestran en formato **12 horas con meridiano** (8 AM, 9 AM, 10 AM, etc.)
- Más intuitivo y fácil de leer para los usuarios
- Consistente con la convención de tiempo utilizada en Venezuela

### 3. Scroll Automático Inteligente
- Se mantiene el scroll automático a la hora actual al cargar la vista
- Permite hacer scroll hacia arriba para ver las horas anteriores (8 AM - hora actual)
- La franja roja del indicador de hora actual permanece visible

### 4. Botón "Ir a Hora Actual"
Se agregó un botón personalizado `⏰ Ahora` en el toolbar del calendario que permite:
- Volver rápidamente a la hora actual desde cualquier posición de scroll
- Funciona tanto en vista diaria como semanal
- Muestra una notificación toast de confirmación

### 5. Mejoras en la Visibilidad
- Indicador de hora actual con color rojo más intenso (#dc3545)
- Grosor aumentado de la línea indicadora (2px)
- Ejes de tiempo actuales resaltados en rojo y negrita

## Cambios Técnicos

### Archivo: `public/js/app-calendario-general.js`

#### 1. Configuración Inicial del Calendario
```javascript
initialView: 'timeGridDay',
plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
slotMinTime: '08:00:00',      // 8 AM
slotMaxTime: '23:00:00',      // 11 PM
slotLabelFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short',
    hour12: true
},
```

#### 2. Modificación del `datesSet`
```javascript
datesSet: function(arg){
    var today = new Date();
    var viewDate = new Date(arg.start);
    var currentHour = today.getHours();

    if (arg.view.type === 'timeGridDay') {
        // Mantener siempre el mismo rango horario completo (08:00 - 23:00)
        calendar.setOption('slotMinTime', '08:00:00');
        calendar.setOption('slotMaxTime', '23:00:00');
        
        // Solo hacer scroll a la hora actual si es el día de hoy
        if (viewDate.toDateString() === today.toDateString()) {
            var scrollHour = currentHour < 8 ? 8 : currentHour;
            calendar.setOption('scrollTime', (scrollHour < 10 ? '0' + scrollHour : scrollHour) + ':00:00');
            calendar.setOption('nowIndicator', true);
        } else {
            calendar.setOption('scrollTime', '09:00:00');
            calendar.setOption('nowIndicator', false);
        }
    } else if (arg.view.type === 'timeGridWeek') {
        // Para vista semanal, también usar formato AM/PM y rango 08:00 - 23:00
        calendar.setOption('slotMinTime', '08:00:00');
        calendar.setOption('slotMaxTime', '23:00:00');
        calendar.setOption('scrollTime', '09:00:00');
        calendar.setOption('nowIndicator', true);
    } else {
        calendar.setOption('slotMinTime', '08:00:00');
        calendar.setOption('slotMaxTime', '23:00:00');
        calendar.setOption('scrollTime', '09:00:00');
        calendar.setOption('nowIndicator', false);
    }
}
```

#### 3. Botón Personalizado "Ir a Hora Actual"
```javascript
customButtons: { 
    sidebarToggle: { text: 'Menú' },
    goToNow: { 
        text: '⏰ Ahora', 
        click: function() {
            var today = new Date();
            var view = calendar.getView();
            
            if (view.type === 'timeGridDay') {
                var currentHour = today.getHours();
                var scrollHour = currentHour < 8 ? 8 : currentHour;
                var scrollTime = (scrollHour < 10 ? '0' + scrollHour : scrollHour) + ':00:00';
                
                calendar.setOption('scrollTime', scrollTime);
                calendar.changeView('timeGridDay', calendar.getDate());
                
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Mostrando hora actual',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } else {
                calendar.changeView('timeGridDay', today);
                setTimeout(function() {
                    var hour = today.getHours();
                    var h = hour < 8 ? 8 : hour;
                    calendar.setOption('scrollTime', (h < 10 ? '0' + h : h) + ':00:00');
                    calendar.changeView('timeGridDay', today);
                }, 100);
            }
        }
    }
}
```

#### 4. Scroll Inicial al Cargar
```javascript
setTimeout(function() {
    var today = new Date();
    var view = calendar.getView();
    
    if (view.type === 'timeGridDay' || view.type === 'timeGridWeek') {
        var currentHour = today.getHours();
        var scrollHour = currentHour < 8 ? 8 : currentHour;
        var scrollTime = (scrollHour < 10 ? '0' + scrollHour : scrollHour) + ':00:00';
        
        calendar.setOption('scrollTime', scrollTime);
        calendar.changeView(view.type, calendar.getDate());
    }
}, 500);
```

### Archivo: `resources/views/livewire/admin/calendario.blade.php`

#### Estilos CSS Mejorados
```css
.fc .fc-timegrid-now-indicator-line {
    border-color: #dc3545 !important;
    border-width: 2px 0 0 !important;
    z-index: 999 !important;
}

.fc .fc-timegrid-now-indicator-arrow {
    border-top-color: #dc3545 !important;
    z-index: 999 !important;
}

.fc .fc-timegrid-now-indicator-container {
    z-index: 999 !important;
}

/* Mejorar visibilidad del indicador de hora actual */
.fc .fc-timegrid-axis.fc-timegrid-now {
    color: #dc3545 !important;
    font-weight: 700 !important;
}
```

## Beneficios para el Usuario

1. ✅ **Visibilidad Completa**: Ahora puede ver todo el día desde 8 AM hasta 11 PM sin perder las horas anteriores
2. ✅ **Formato AM/PM Claro**: Las horas se muestran en formato familiar (8 AM, 9 AM, 10 AM, etc.)
3. ✅ **Indicador Claro**: La franja roja de la hora actual es más visible y destacada
4. ✅ **Navegación Fácil**: Botón dedicado para volver rápidamente a la hora actual
5. ✅ **Contexto Temporal**: Mejor comprensión de la distribución de citas/consultas a lo largo del día
6. ✅ **UX Mejorada**: Scroll suave y animaciones fluidas

## Configuración Resultante

- **slotMinTime**: '08:00:00' (8 AM - hora mínima mostrada)
- **slotMaxTime**: '23:00:00' (11 PM - hora máxima mostrada)
- **slotLabelFormat**: Formato 12 horas con AM/PM
  - Ejemplo: 8 AM, 9 AM, 10 AM, 11 AM, 12 PM, 1 PM, ..., 11 PM
- **scrollTime**: Dinámico según la hora actual (días de hoy) o '09:00:00' (otros días)
- **nowIndicator**: true (solo para el día actual y vista semanal)

## Visualización de Horas

El eje vertical ahora muestra las horas en el siguiente formato:
```
8 AM
9 AM
10 AM
11 AM
12 PM
1 PM
2 PM
3 PM
4 PM
5 PM
6 PM
7 PM
8 PM
9 PM
10 PM
11 PM
```

## Pruebas Recomendadas

1. Abrir el calendario en vista diaria durante diferentes horas del día
2. Verificar que se muestren todas las horas desde las 8 AM hasta las 11 PM
3. Confirmar que el formato de hora sea AM/PM (ej: 10 AM, 3 PM, etc.)
4. Comprobar que el scroll inicial posiciona la vista en la hora actual
5. Hacer scroll hacia arriba para confirmar que se ven las horas anteriores
6. Usar el botón "⏰ Ahora" para verificar que retorna a la hora actual
7. Probar en vista semanal para asegurar que también usa el formato AM/PM
8. Verificar que la franja roja de la hora actual sea claramente visible
