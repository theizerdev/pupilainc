# Modelo Híbrido de Estados de Consulta

## 📋 Resumen del Cambio

Se implementó un **modelo híbrido** para la gestión de estados de consulta que separa los estados en dos categorías:

1. **Estados BASE** (universales, automáticos)
2. **Estados ESPECIALES** (configurables por especialidad)

---

## 🎯 Problema Resuelto

### Antes del cambio:
- Todos los estados eran configurables por especialidad
- Cada especialidad debía configurar manualmente estados universales como "Sala de Espera", "En Consultorio", etc.
- Complejidad innecesaria y duplicación de configuración
- Riesgo de inconsistencias entre especialidades

### Después del cambio:
- Los estados base son **automáticos e invisibles** en la configuración
- Solo los estados especiales requieren configuración
- Simplificación drástica de la UI
- Consistencia garantizada en el flujo básico

---

## 🏗️ Arquitectura

### Estados BASE (Automáticos)
Definidos en `EspecialidadPlantilla::ESTADOS_BASE`:

```php
const ESTADOS_BASE = [
    'por_llegar'      => 'Por Llegar',
    'sala_espera'     => 'Sala de Espera',
    'en_enfermeria'   => 'En Enfermería',
    'en_consultorio'  => 'En Consultorio',
    'finalizada'      => 'Finalizada',
];
```

**Características:**
- ✅ Siempre activos para TODAS las especialidades
- ✅ No se pueden desactivar, eliminar o reordenar
- ✅ No aparecen en la UI de configuración (solo visualización informativa)
- ✅ Orden fijo y predefinido

### Estados ESPECIALES (Configurables)
Definidos en `EspecialidadPlantilla::ESTADOS_ESPECIALES`:

```php
const ESTADOS_ESPECIALES = [
    'en_consultorio_optometrista'   => 'En Consultorio Optometrista',
    'en_gotas'                      => 'En Gotas',
    'dilatado'                      => 'Dilatado',
    'en_optica'                     => 'En Óptica',
    'en_estudio'                    => 'En Estudio',
    'pagada'                        => 'Pagada',
];
```

**Características:**
- ⚙️ Configurables por especialidad
- ✅ Se pueden activar/desactivar
- ✅ Se pueden reordenar
- ✅ Se pueden eliminar
- ✅ Específicos para flujos especializados

---

## 💻 Implementación Técnica

### 1. Modelo `EspecialidadPlantilla`

#### Nuevas constantes:
```php
const ESTADOS_BASE = [...];          // Estados universales
const ESTADOS_ESPECIALES = [...];    // Estados configurables
const ESTADOS_DISPONIBLES = [...];   // Todos (base + especiales)
```

#### Método `getEstadosEfectivos()` actualizado:
```php
public function getEstadosEfectivos(): array
{
    // 1. Agregar estados BASE automáticamente
    foreach (self::ESTADOS_BASE as $key => $nombre) {
        $config[] = [
            'key'    => $key,
            'nombre' => $nombre,
            'color'  => Consulta::ESTADO_COLORES[$key],
            'activo' => true,
            'tipo'   => 'base',
        ];
    }

    // 2. Agregar estados ESPECIALES configurados
    if (!empty($this->estados_config)) {
        // Usar config personalizada (excluyendo base)
        $especiales = collect($this->estados_config)
            ->where('tipo', '!=', 'base')
            ->sortBy('orden');
        
        foreach ($especiales as $estado) {
            $config[] = $estado;
        }
    }

    return $config;
}
```

### 2. Componente `PlantillaConsulta`

#### UI simplificada:
- Muestra estados base en sección informativa (sin controles)
- Muestra estados especiales con controles completos
- Botón "Agregar estado especial" solo afecta estados configurables

#### Protecciones agregadas:
```php
// toggleEstado() - No permite desactivar estados base
if (($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
    $this->dispatch('notify', ['type' => 'error', ...]);
    return;
}

// eliminarEstado() - No permite eliminar estados base
if (($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
    $this->dispatch('notify', ['type' => 'error', ...]);
    return;
}

// moverEstado() - No permite reordenar estados base
if (($this->estadosFlujo[$index]['tipo'] ?? 'especial') === 'base') {
    $this->dispatch('notify', ['type' => 'error', ...]);
    return;
}
```

### 3. Vista `plantilla-consulta.blade.php`

#### Nueva estructura visual:

```blade
{{-- Estados BASE (solo visualización) --}}
<div class="mb-4">
    <h6><i class="ri ri-lock-line"></i>Estados Base (Automáticos)</h6>
    @foreach(ESTADOS_BASE as $key => $nombre)
        <div class="campo-row" style="opacity: 0.7;">
            <span>{{ $nombre }}</span>
            <span class="badge bg-success">Siempre activo</span>
        </div>
    @endforeach
</div>

{{-- Estados ESPECIALES (configurables) --}}
<div>
    <h6><i class="ri ri-settings-3-line"></i>Estados Especiales</h6>
    <button wire:click="abrirModalEstado()">Agregar estado especial</button>
    
    @foreach($estadosEspeciales as $estado)
        <div class="campo-row">
            {{ $estado['nombre'] }}
            <!-- Controles: mover, editar, toggle, eliminar -->
        </div>
    @endforeach
</div>
```

---

## 🔄 Migración de Datos

### Plantillas existentes:
El sistema migra automáticamente plantillas antiguas:

```php
// Si una plantilla tiene estados_flujo legacy:
$flujo = $this->estados_flujo ?? [];

foreach ($flujo as $key) {
    // Solo agrega si es un estado ESPECIAL (no base)
    if (isset(self::ESTADOS_ESPECIALES[$key])) {
        $config[] = [
            'key'    => $key,
            'tipo'   => 'especial',
            ...
        ];
    }
}
```

**Resultado:**
- Estados base antiguos (`sala_espera`, `en_consultorio`, etc.) → Se ignoran (ahora son automáticos)
- Estados especiales antiguos (`en_gotas`, `dilatado`, etc.) → Se mantienen como configurables

---

## 📊 Ejemplos de Uso

### Ejemplo 1: Medicina General
**Configuración:** Sin estados especiales

**Flujo resultante:**
```
Por Llegar → Sala Espera → Enfermería → Consultorio → Finalizada
```
*(Solo estados base)*

### Ejemplo 2: Oftalmología
**Configuración:** Agrega estados especiales:
- En Gotas
- Dilatado
- En Óptica

**Flujo resultante:**
```
Por Llegar → Sala Espera → Enfermería → Consultorio 
→ En Gotas → Dilatado → En Óptica → Finalizada
```
*(Base + especiales)*

### Ejemplo 3: Optometría
**Configuración:** Agrega estado especial:
- En Consultorio Optometrista

**Flujo resultante:**
```
Por Llegar → Sala Espera → En Consultorio Optometrista → Finalizada
```
*(Base modificado + especial)*

---

## ✅ Beneficios

### Para usuarios:
1. **Simplicidad**: Menos clicks para configurar
2. **Claridad**: Separación visual entre estados universales y especiales
3. **Consistencia**: Todas las especialidades tienen el flujo base
4. **Flexibilidad**: Pueden agregar estados específicos cuando lo necesiten

### Para desarrolladores:
1. **Mantenimiento**: Menos código repetitivo
2. **Escalabilidad**: Fácil agregar nuevos estados base o especiales
3. **Validación**: Protecciones contra errores de configuración
4. **Retrocompatibilidad**: Migración automática de datos existentes

---

## 🔍 Verificación

Para verificar que el modelo híbrido funciona correctamente:

```bash
# 1. Ir a admin/especialidades/{id}/plantilla
# 2. Pestaña "Configuración general"
# 3. Verificar sección "Estados del Flujo"

# Deberías ver:
# - Sección "Estados Base" con 5 estados (sin controles)
# - Sección "Estados Especiales" vacía o con estados configurados
```

---

## 📝 Notas Importantes

1. **Los estados base NO se almacenan en la BD**: Se generan dinámicamente en `getEstadosEfectivos()`

2. **Solo los estados especiales se guardan**: En el campo `estados_config` de la plantilla

3. **El orden de los estados base es fijo**: No se puede modificar

4. **Los estados especiales se ordenan después de los base**: El orden comienza donde terminan los base

5. **Compatibilidad total**: Las vistas Kanban y listas funcionan sin cambios

---

## 🚀 Próximos Pasos (Opcional)

1. **Dashboard de estadísticas**: Mostrar cuántas especialidades usan cada estado especial

2. **Recomendaciones inteligentes**: Sugerir estados especiales basados en la especialidad

3. **Plantillas predefinidas**: Crear presets para especialidades comunes (Oftalmología, Optometría, etc.)

4. **Validación de flujo**: Detectar estados huérfanos o flujos incompletos

---

**Fecha de implementación:** Mayo 2026  
**Archivos modificados:**
- `app/Models/EspecialidadPlantilla.php`
- `app/Livewire/Admin/Especialidades/PlantillaConsulta.php`
- `resources/views/livewire/admin/especialidades/plantilla-consulta.blade.php`
