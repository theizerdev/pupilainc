# Sistema de Notificaciones Selectivas para Citas

## 📋 Resumen de la Implementación

Se ha implementado un sistema inteligente de notificaciones que permite controlar qué notificaciones se envían a pacientes y doctores cuando cambian los estados de las citas, evitando la saturación de mensajes.

## 🎯 Problema Resuelto

**Antes:** Cada cambio de estado de cita (programada → confirmada → sala_espera → en_enfermeria → en_consultorio → finalizada → pagada) enviaba notificaciones automáticas tanto al paciente como al doctor, generando:
- 6-7 notificaciones por consulta
- Fatiga de notificaciones
- Posible bloqueo por spam
- Costos innecesarios de API de mensajería

**Ahora:** Solo se envían notificaciones estratégicas según configuración personalizable.

## 🗂️ Archivos Creados/Modificados

### 1. Base de Datos
- **Migración:** `database/migrations/2026_05_10_000000_create_configuracion_notificaciones_table.php`
  - Tabla `configuracion_notificaciones` con campos:
    - `empresa_id`: Multitenancy
    - `tipo_destinatario`: 'paciente' o 'doctor'
    - `estado_cita`: Estado de la cita
    - `enviar_notificacion`: Boolean para activar/desactivar

### 2. Modelo
- **Modelo:** `app/Models/ConfiguracionNotificacion.php`
  - Métodos principales:
    - `debeEnviar()`: Verifica si se debe enviar notificación
    - `obtenerConfiguracionEmpresa()`: Obtiene toda la configuración
    - `guardarConfiguracion()`: Guarda la configuración
    - `valorPorDefecto()`: Valores recomendados por defecto

### 3. Servicio Modificado
- **Servicio:** `app/Services/CitaNotificationService.php`
  - Método `notificarCambioEstado()` modificado para verificar configuración antes de enviar
  - Ahora respeta las preferencias configuradas por empresa

### 4. Seeder
- **Seeder:** `database/seeders/ConfiguracionNotificacionesSeeder.php`
  - Inicializa configuración por defecto para todas las empresas
  - Configuración optimizada basada en mejores prácticas

### 5. Componente Livewire
- **Componente:** `app/Livewire/Admin/Configuracion/ConfigurarNotificaciones.php`
  - Interfaz para configurar notificaciones
  - Funciones:
    - Aplicar configuración recomendada
    - Activar/desactivar todas
    - Personalización individual por estado

### 6. Vista
- **Vista:** `resources/views/livewire/admin/configuracion/configurar-notificaciones.blade.php`
  - UI moderna con Tailwind CSS
  - Toggle switches para cada estado
  - Secciones separadas para pacientes y doctores
  - Botones de acción rápida

### 7. Ruta
- **Ruta:** Agregada en `routes/admin.php`
  - URL: `/admin/configuracion/notificaciones`
  - Nombre: `admin.configuracion.notificaciones`
  - Permiso requerido: `access empresas`

## ⚙️ Configuración por Defecto Recomendada

### Para PACIENTES ✅

| Estado | Enviar | Razón |
|--------|--------|-------|
| confirmada | ✅ SÍ | Confirmación importante |
| sala_espera | ❌ NO | Proceso interno |
| en_enfermeria | ❌ NO | Proceso interno |
| en_consultorio | ❌ NO | Proceso interno |
| en_consultorio_optometrista | ❌ NO | Proceso interno |
| en_gotas | ❌ NO | Proceso interno |
| dilatado | ❌ NO | Proceso interno |
| en_optica | ❌ NO | Proceso interno |
| en_estudio | ❌ NO | Proceso interno |
| finalizada | ❌ NO | Ya terminó |
| pagada | ✅ SÍ | Confirmación de pago |
| cancelada | ✅ SÍ | Cancelación importante |
| no_asistio | ❌ NO | No aplica |

**Total notificaciones por consulta:** 2-3 (vs 6-7 antes)

### Para DOCTORES 👨‍⚕️

| Estado | Enviar | Razón |
|--------|--------|-------|
| confirmada | ✅ SÍ | Agenda confirmada |
| sala_espera | ⚠️ OPCIONAL | Paciente llegó |
| en_enfermeria | ❌ NO | Interno |
| en_consultorio | ❌ NO | Ya está presente |
| en_consultorio_optometrista | ❌ NO | Interno |
| en_gotas | ❌ NO | Interno |
| dilatado | ❌ NO | Interno |
| en_optica | ❌ NO | Interno |
| en_estudio | ❌ NO | Interno |
| finalizada | ❌ NO | Interno |
| pagada | ❌ NO | Administrativo |
| cancelada | ✅ SÍ | Liberar agenda |
| no_asistio | ❌ NO | No aplica |

**Total notificaciones por consulta:** 2-3 (vs 6-7 antes)

## 🚀 Cómo Usar

### 1. Ejecutar Migración
```bash
php artisan migrate
```

### 2. Ejecutar Seeder
```bash
php artisan db:seed --class=ConfiguracionNotificacionesSeeder
```

### 3. Acceder a la Configuración
Navegar a: `/admin/configuracion/notificaciones`

### 4. Personalizar
- Usar botón "⭐ Recomendado" para aplicar configuración óptima
- O personalizar manualmente cada estado
- Guardar cambios

## 💡 Beneficios

1. **Reducción del 60-70% en notificaciones**
   - Antes: 6-7 mensajes por consulta
   - Ahora: 2-3 mensajes por consulta

2. **Mejor Experiencia de Usuario**
   - Menos interrupciones
   - Solo información relevante
   - Menor fatiga de notificaciones

3. **Ahorro de Costos**
   - Menos uso de API de WhatsApp/SMS
   - Reducción proporcional en costos operativos

4. **Flexibilidad Total**
   - Configuración por empresa
   - Personalizable según necesidades
   - Fácil de modificar

5. **Seguridad Anti-Spam**
   - Evita bloqueos por exceso de mensajes
   - Mejor reputación en plataformas de mensajería

## 🔧 Personalización Avanzada

Si necesitas agregar más estados o modificar la lógica:

1. **Agregar nuevo estado:**
   ```php
   // En ConfiguracionNotificacion::valorPorDefecto()
   'nuevo_estado' => false, // o true según corresponda
   ```

2. **Modificar comportamiento programático:**
   ```php
   // Verificar antes de enviar
   if (ConfiguracionNotificacion::debeEnviar($empresaId, 'paciente', $estado)) {
       // Enviar notificación
   }
   ```

3. **Resetear configuración:**
   ```bash
   php artisan db:seed --class=ConfiguracionNotificacionesSeeder
   ```

## 📊 Impacto Esperado

- **Pacientes:** Reciben solo 2-3 mensajes relevantes vs 6-7 antes
- **Doctores:** Reciben solo notificaciones críticas
- **Administración:** Control total sobre el flujo de comunicación
- **Sistema:** Más eficiente y profesional

## 🎉 Conclusión

El sistema ahora es mucho más inteligente y respetuoso con los usuarios, enviando solo la información realmente necesaria y permitiendo personalización completa según las necesidades de cada clínica.
