# Funcionalidad de Signos Vitales en Enfermería

## Resumen

El sistema cuenta con la funcionalidad completa para registrar signos vitales en el estado de enfermería, y estos valores se precargan automáticamente cuando el paciente pasa al consultorio con el doctor.

**ACTUALIZACIÓN IMPORTANTE**: Se ha integrado la visualización completa de signos vitales en el proceso de consulta del doctor, mostrando:
- Tarjetas visuales con todos los valores actuales
- Formulario editable para que el doctor pueda modificar los valores
- Gráficos de historial con la evolución del paciente
- Cálculo automático de IMC con clasificación

## Características Implementadas

### 1. Registro en Enfermería (INTEGRADO EN FORMULARIO DE ESTADO)

**ACTUALIZACIÓN IMPORTANTE**: Los signos vitales ahora están **integrados directamente en el formulario del estado "En Enfermería"**. 

- **Componente**: Sección automática agregada al formulario del estado
- **Vista**: Aparece como una sección más dentro del formulario cuando la consulta está en estado "en_enfermeria"
- **Permisos requeridos**: `access consultas en enfermeria`

**Campos disponibles:**
- Presión Arterial (Sistólica/Diastólica)
- Frecuencia Cardíaca
- Frecuencia Respiratoria
- Temperatura
- Saturación de Oxígeno
- Peso y Talla (con cálculo automático de IMC)
- Observaciones

**Ventajas de esta integración:**
- Flujo natural: enfermería llena todos los datos del estado en un solo formulario
- Los signos vitales se guardan automáticamente junto con los demás datos del estado
- No requiere componentes separados ni modales adicionales
- Mantiene consistencia con el sistema de plantillas dinámicas

### 2. Visualización en Consultorio (ACTUALIZADO)
Cuando el doctor accede al proceso de consulta (`ProcesoConsulta`), el sistema muestra:

**Tarjetas Visuales:**
- Presión Arterial (Sistólica/Diastólica)
- Frecuencia Cardíaca
- Temperatura
- Saturación de Oxígeno
- Frecuencia Respiratoria
- Peso y Talla
- IMC con clasificación automática (bajo peso, normal, sobrepeso, obesidad)

**Formulario Editable:**
- Todos los campos son editables por el doctor
- Botón "Recalcular IMC" para actualizar el cálculo
- Botón "Guardar Cambios" para persistir modificaciones

**Historial Gráfico:**
- Gráfico de temperatura
- Gráfico de presión arterial (sistólica y diastólica)
- Gráfico de IMC
- Muestra los últimos 10 registros del paciente

### 3. Base de Datos
- **Modelo**: `App\Models\SignosVitales`
- **Tabla**: `signos_vitales`
- **Relación**: Una consulta puede tener múltiples registros de signos vitales (historial)

## Pasos para Activar la Funcionalidad

### Paso 1: Ejecutar el Seeder de Permisos

Después de la modificación realizada en `SectorRolesAndPermissionsSeeder.php`, debes ejecutar:

```bash
php artisan db:seed --class=SectorRolesAndPermissionsSeeder
```

Esto asignará el permiso `'registrar signos vitales'` al rol de enfermeros.

### Paso 2: Verificar Permisos del Usuario de Enfermería

Asegúrate de que los usuarios de enfermería tengan asignado el rol correspondiente:

1. Ve al panel de administración de usuarios
2. Verifica que el usuario tenga el rol de "Enfermería"
3. Confirma que el permiso "registrar signos vitales" esté activo

### Paso 3: Probar la Funcionalidad

1. **Como enfermero/a:**
   - Accede a "Gestión Consultas" → "En Enfermería"
   - Selecciona una consulta en espera
   - Haz clic en el menú de opciones y selecciona "Signos Vitales"
   - Registra los valores y guarda

2. **Como doctor:**
   - Accede a "Gestión Consultas" → "En Consultorio"
   - Selecciona la misma consulta
   - Haz clic en "Procesar Consulta"
   - Verifica que los signos vitales estén precargados
   - Modifica si es necesario y guarda

## Flujo de Trabajo

```
Paciente llega → Sala de Espera
    ↓
Enfermería registra signos vitales
    ↓
Paciente pasa a Consultorio
    ↓
Doctor ve signos vitales precargados
    ↓
Doctor puede modificar o confirmar valores
    ↓
Consulta continúa normalmente
```

## Notas Importantes

1. **Integración con Estados**: Los signos vitales están integrados en el formulario del estado "en_enfermeria". Cuando la consulta cambia a este estado, automáticamente aparece la sección de signos vitales.

2. **Sincronización Bidireccional**: El sistema sincroniza automáticamente los datos entre:
   - Las propiedades individuales del componente (`$presion_sistolica`, etc.)
   - El array `datos_estado_actual` (usado por el formulario)
   - La tabla `signos_vitales` (persistencia en base de datos)

3. **Cálculo automático de IMC**: Cuando se ingresan peso y talla, el IMC se calcula automáticamente y se muestra con clasificación (bajo peso, normal, sobrepeso, obesidad).

4. **Historial visual**: En el consultorio, el doctor puede ver gráficos con la evolución de los signos vitales del paciente.

5. **Flexibilidad**: Aunque enfermería registre los signos vitales, el doctor siempre tiene la opción de modificarlos o registrar nuevos valores durante la consulta.

## Archivos Relacionados

### Backend (Lógica)
- **Componente Principal**: `app/Livewire/Admin/Consulta/ProcesoConsulta.php`
  - Método `agregarSeccionSignosVitalesAlEstado()`: Agrega dinámicamente la sección al formulario
  - Método `sincronizarSignosVitalesDesdeEstado()`: Sincroniza datos del formulario hacia las propiedades
  - Método `sincronizarSignosVitalesHaciaEstado()`: Sincroniza propiedades hacia el formulario
  - Método `guardarSignosVitalesDesdeEstado()`: Guarda en la tabla `signos_vitales`
  
- **Modelo**: `app/Models/SignosVitales.php`
- **Seeder de Permisos**: `database/seeders/SectorRolesAndPermissionsSeeder.php`

### Frontend (Vistas)
- **Vista Principal**: `resources/views/livewire/admin/consulta/proceso-consulta.blade.php`
  - Muestra el formulario del estado actual (incluyendo signos vitales cuando corresponde)
  - Tarjetas visuales con valores actuales
  - Gráficos de historial con ApexCharts

### Base de Datos
- **Migración**: `database/migrations/2026_02_17_195011_create_signos_vitales_table.php`
- **Migración**: `database/migrations/2026_02_20_142737_make_enfermero_id_nullable_in_signos_vitales_table.php`

## Implementación Técnica

### Cómo Funciona la Integración

1. **Detección del Estado**:
   ```php
   if ($estado === Consulta::ESTADO_EN_ENFERMERIA) {
       $this->agregarSeccionSignosVitalesAlEstado();
   }
   ```

2. **Creación Dinámica de la Sección**:
   - Se crea un array con la estructura de sección (campos, etiquetas, validaciones)
   - Se agrega al array `$formulariosPorEstado['en_enfermeria']['secciones']`
   - La vista itera sobre estas secciones y las renderiza automáticamente

3. **Sincronización de Datos**:
   - Cuando el usuario escribe en un campo, Livewire dispara eventos `updated{Propiedad}`
   - Estos eventos sincronizan los datos entre el formulario y las propiedades
   - Al guardar, se persiste tanto en `consulta_estado_datos` como en `signos_vitales`

4. **Precarga en Consultorio**:
   - El método `cargarSignosVitales()` busca el último registro
   - Rellena las propiedades del componente
   - Estas propiedades se muestran en las tarjetas visuales y el formulario editable

## Solución de Problemas

### El botón "Signos Vitales" no aparece en enfermería
- Verifica que el usuario tenga el permiso `registrar signos vitales`
- Ejecuta: `php artisan permission:cache-reset`
- Refresca la página

### Los signos vitales no se precargan en el consultorio
- Verifica que los signos vitales se hayan guardado correctamente en enfermería
- Revisa la base de datos en la tabla `signos_vitales`
- Confirma que el `consulta_id` sea correcto

### Error de permisos
- Ejecuta: `php artisan db:seed --class=SectorRolesAndPermissionsSeeder`
- Luego: `php artisan permission:cache-reset`
