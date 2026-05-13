# 📱 Inventario de Mensajes de WhatsApp - Sistema Solumed

**Fecha de generación:** 13 de mayo de 2026  
**Sistema:** Solumed - Sistema Médico Integral  
**Versión:** 1.0

---

## 📋 Índice

1. [Resumen General](#resumen-general)
2. [Notificaciones de Citas Médicas](#notificaciones-de-citas-médicas)
3. [Notificaciones de Acceso Estudiantil](#notificaciones-de-acceso-estudiantil)
4. [Recordatorios de Citas](#recordatorios-de-citas)
5. [Cancelaciones de Citas](#cancelaciones-de-citas)
6. [Cambios de Estado de Citas](#cambios-de-estado-de-citas)
7. [Confirmación de Citas (NUEVO)](#confirmación-de-citas-nuevo)
8. [Mensajes con Confirmación Integrada](#mensajes-con-confirmación-integrada)
9. [Plantillas Configurables](#plantillas-configurables)
10. [Servicios y APIs Utilizados](#servicios-y-apis-utilizados)

---

## 📊 Resumen General

El sistema Solumed utiliza WhatsApp como canal principal de comunicación para notificaciones automatizadas en dos áreas principales:

### Áreas de Notificación:
1. **Citas Médicas** - Gestión completa de citas (creación, recordatorios, cancelaciones, cambios de estado, **confirmaciones**)
2. **Control de Acceso Estudiantil** - Notificaciones de entrada/salida de estudiantes menores de edad

### Características del Sistema:
- ✅ Envío automático de notificaciones
- ✅ Recordatorios programados (12h, 6h, 1h antes de la cita)
- ✅ Confirmación integrada de citas
- ✅ **Notificación específica al doctor cuando el paciente confirma** (NUEVO)
- ✅ Soporte multi-empresa
- ✅ Formateo automático de números telefónicos
- ✅ Reintentos automáticos en caso de fallo
- ✅ Mensajes interactivos con botones

---

## 🏥 Notificaciones de Citas Médicas

**Ubicación:** `app/Services/CitaNotificationService.php`

### 1. Nueva Cita Agendada (Paciente)

**Método:** `construirMensajeNuevaCita()`  
**Destinatario:** Paciente o representante (si es menor)  
**Trigger:** Al crear una nueva cita médica

```
🏥 *Nueva Cita Médica Agendada*

Estimado(a) *[Nombre del Paciente]*,

Se ha agendado la siguiente cita:

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

Por favor, llegue 15 minutos antes de su cita.
Si necesita cancelar o reprogramar, comuníquese con nosotros con anticipación.
```

**Variables utilizadas:**
- `{nombre_completo}` - Nombre completo del paciente
- `{medico.nombre_completo}` - Nombre del médico asignado
- `{fecha_inicio}` - Fecha formateada (d/m/Y)
- `{hora}` - Hora formateada (h:i A)
- `{motivo}` - Motivo de la cita

---

### 2. Nueva Cita Agendada (Médico)

**Método:** `construirMensajeNuevaCitaMedico()`  
**Destinatario:** Médico  
**Trigger:** Al crear una nueva cita médica

```
🏥 *Nueva Cita Agendada*

Dr(a). *[Nombre del Médico]*, se le informa que tiene una nueva cita programada:

👤 Paciente: [Nombre Completo del Paciente]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

Esta notificación queda como constancia de aviso.
```

**Variables utilizadas:**
- `{medico.nombre_completo}` - Nombre del médico
- `{paciente.nombre_completo}` - Nombre del paciente
- `{fecha_inicio}` - Fecha formateada
- `{hora}` - Hora formateada
- `{motivo}` - Motivo de la cita

---

## ⏰ Recordatorios de Citas

### 3. Recordatorio Genérico

**Método:** `construirMensajeRecordatorio()`  
**Destinatario:** Paciente o representante  
**Triggers Automáticos:** 
- 12 horas antes (`cita_recordatorio_12h`)
- 6 horas antes (`cita_recordatorio_6h`)
- 1 hora antes (`cita_recordatorio_1h`)
- Manual desde el panel administrativo

```
🏥 *Recordatorio de Cita Médica*

Estimado(a) *[Nombre del Paciente]*,

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

⏰ Su cita es en aproximadamente *[tiempo restante]*.
Por favor, llegue 15 minutos antes.
```

**Variables utilizadas:**
- `{tiempoRestante}` - Texto descriptivo ("12 horas", "6 horas", "1 hora")
- Variables estándar de paciente, médico, fecha, hora y motivo

---

### 4. Recordatorio con Pre-Consulta (12 horas)

**Método:** `construirMensajeRecordatorioConPreconsulta()`  
**Destinatario:** Paciente  
**Trigger:** 12 horas antes si la pre-consulta está pendiente

```
[Contenido base del recordatorio genérico]

────────────────────
Para agilizar su atencion, complete el cuestionario de pre-consulta antes de su cita:

https://solumed.com/preconsulta/[token]

Es confidencial y nos ayudara a brindarle mejor atencion.
```

**Características especiales:**
- Incluye enlace al formulario de pre-consulta
- Solo se envía si `estado_preconsulta === 'pendiente'`
- El token expira según configuración del sistema

---

## ❌ Cancelaciones de Citas

### 5. Cancelación de Cita (Paciente)

**Método:** `construirMensajeCancelacion()`  
**Destinatario:** Paciente o representante  
**Trigger:** Cuando se cancela una cita

```
🏥 *Cita Médica Cancelada*

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
📅 Fecha: DD/MM/YYYY a las HH:MM AM/PM

❌ Su cita ha sido cancelada.
Si desea reprogramar, por favor comuníquese con nosotros.
```

**Acciones automáticas:**
- Cancela todos los recordatorios pendientes asociados a esta cita
- Libera el horario en la agenda del médico

---

### 6. Cancelación de Cita (Médico)

**Método:** `construirMensajeCancelacionMedico()`  
**Destinatario:** Médico  
**Trigger:** Cuando se cancela una cita

```
🏥 *Cita Cancelada*

Dr(a). *[Nombre del Médico]*,
Se ha cancelado la siguiente cita:

👤 Paciente: [Nombre Completo del Paciente]
📅 Fecha: DD/MM/YYYY a las HH:MM AM/PM

El horario ha quedado disponible nuevamente.
```

---

## 🔄 Cambios de Estado de Citas

### 7. Cambio de Estado (Paciente)

**Método:** `construirMensajeCambioEstado()`  
**Destinatario:** Paciente o representante  
**Trigger:** Cuando cambia el estado de la cita (confirmada, en progreso, completada, etc.)

```
🏥 *Actualización de Cita Médica*

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
📅 Fecha: DD/MM/YYYY a las HH:MM AM/PM

📌 Estado anterior: [Estado Anterior]
✅ Nuevo estado: [Estado Actual]
```

**Estados posibles:**
- `programada` → Programada
- `confirmada` → Confirmada
- `en_progreso` → En Progreso
- `completada` → Completada
- `cancelada` → Cancelada
- `no_asistio` → No Asistió

---

### 7.1 Confirmación de Cita (Paciente) - NUEVO

**Método:** `construirMensajeConfirmacionPaciente()`  
**Destinatario:** Paciente o representante  
**Trigger:** Cuando el paciente confirma su asistencia (estado cambia a 'confirmada')

```
✅ *Cita Confirmada*

Estimado(a) *[Nombre del Paciente]*,

Su cita médica ha sido confirmada exitosamente:

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

Por favor, llegue 15 minutos antes de su cita.
Si necesita cancelar o reprogramar, comuníquese con nosotros.
```

**Variables utilizadas:**
- `{nombre_completo}` - Nombre completo del paciente
- `{medico.nombre_completo}` - Nombre del médico asignado
- `{fecha_inicio}` - Fecha formateada (d/m/Y)
- `{hora}` - Hora formateada (h:i A)
- `{motivo}` - Motivo de la cita

**Características especiales:**
- Mensaje específico para confirmación (diferente al cambio de estado genérico)
- Incluye recordatorio de llegar 15 minutos antes
- Opción de contactar para cancelaciones/reprogramaciones

---

### 8. Cambio de Estado (Médico)

**Método:** `construirMensajeCambioEstadoMedico()`  
**Destinatario:** Médico  
**Trigger:** Cuando cambia el estado de la cita

```
🏥 *Actualización de Cita*

Dr(a). *[Nombre del Médico]*,
La cita del paciente *[Nombre del Paciente]* ha cambiado de estado.

📅 Fecha: DD/MM/YYYY a las HH:MM AM/PM
📌 Estado anterior: [Estado Anterior]
✅ Nuevo estado: [Estado Actual]
```

---

### 8.1 Confirmación de Cita (Médico) - NUEVO

**Método:** `construirMensajeConfirmacionMedico()`  
**Destinatario:** Médico  
**Trigger:** Cuando el paciente confirma su asistencia (estado cambia a 'confirmada')

```
✅ *Cita Confirmada por el Paciente*

Dr(a). *[Nombre del Médico]*,

El paciente ha confirmado su asistencia a la siguiente cita:

👤 Paciente: *[Nombre Completo del Paciente]*
🏥 Especialidad: [Nombre de la Especialidad]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

━━━━━━━━━━━━━━━━━━━━
ℹ️ Esta cita está confirmada en su agenda.
El paciente llegará 15 minutos antes de la hora programada.
```

**Variables utilizadas:**
- `{medico.nombre_completo}` - Nombre del médico
- `{paciente.nombre_completo}` - Nombre completo del paciente
- `{especialidad.nombre}` - Nombre de la especialidad médica
- `{fecha_inicio}` - Fecha formateada (d/m/Y)
- `{hora}` - Hora formateada (h:i A)
- `{motivo}` - Motivo de la cita

**Características especiales:**
- ✅ **Mensaje específico para confirmación** (no es genérico como cambio de estado)
- Incluye información completa de la cita
- Muestra la especialidad médica
- Indica que la cita está confirmada en la agenda
- Informa al doctor que el paciente llegará 15 minutos antes
- Diseño con separador visual para destacar la confirmación

---

## ✅ Confirmación de Citas (NUEVO)

**Ubicación:** `app/Services/CitaNotificationService.php`  
**Configuración:** `app/Models/ConfiguracionNotificacion.php`

### Sistema de Notificación de Confirmación

Cuando un paciente confirma su asistencia a una cita (estado cambia a 'confirmada'), el sistema envía notificaciones **específicas y diferenciadas** tanto al paciente como al doctor.

#### Configuración por Defecto

En `ConfiguracionNotificacion.php`, la configuración predeterminada para estado 'confirmada' es:

```php
// Para pacientes
$configPaciente = [
    'confirmada' => true,  // ✅ Enviar confirmación al paciente
    // ... otros estados
];

// Para doctores
$configDoctor = [
    'confirmada' => true,  // ✅ Enviar confirmación al doctor
    // ... otros estados
];
```

#### Flujo de Notificación

1. **Paciente confirma cita** → Estado cambia a 'confirmada'
2. **Sistema detecta cambio** → Verifica configuración de notificaciones
3. **Envía mensaje al paciente** → Confirmación de que su cita fue registrada
4. **Envía mensaje al doctor** → Notificación de que el paciente confirmó

#### Mensajes Enviados

##### Al Paciente:
- Método: `construirMensajeConfirmacionPaciente()`
- Contenido: Confirmación con detalles completos de la cita
- Incluye: Recordatorio de llegar 15 minutos antes

##### Al Doctor:
- Método: `construirMensajeConfirmacionMedico()`
- Contenido: Notificación específica de confirmación del paciente
- Incluye: Especialidad médica, información completa de la cita
- Destaca: Que la cita está confirmada en la agenda

---

## ✅ Mensajes con Confirmación Integrada

### 9. Nueva Cita con Confirmación

**Método:** `construirMensajeNuevaCitaConConfirmacion()`  
**Destinatario:** Paciente o representante  
**Trigger:** Al crear cita (si la confirmación está habilitada)

```
🏥 *Nueva Cita Médica Agendada*

Estimado(a) *[Nombre del Paciente]*,

Se ha agendado la siguiente cita:

👤 Paciente: [Nombre Completo]
👨‍⚕️ Médico: Dr(a). [Nombre del Médico]
🏥 Especialidad: [Nombre de la Especialidad]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo de la consulta]

Por favor, llegue 15 minutos antes de su cita.

━━━━━━━━━━━━━━━━━━━━
📋 *¿Confirma su asistencia?*

📱 Responda por este chat:
*SI* - para confirmar
*NO* - para cancelar

⏰ Tiene 24 horas para responder.
```

**Características especiales:**
- Genera un token único de confirmación
- Crea registro en tabla `cita_confirmaciones`
- URLs firmadas temporalmente para confirmar/cancelar
- Expira en 24 horas
- Permite respuesta directa por chat (SI/NO)

**URLs generadas:**
- Confirmar: `route('citas.confirmar', ['token' => $token])`
- Cancelar: `route('citas.cancelar', ['token' => $token])`

---

## 🎓 Notificaciones de Acceso Estudiantil

**Ubicación:** `app/Jobs/SendAccessWhatsAppNotificationJob.php`

### 10. Registro de Entrada/Salida

**Método:** `buildWhatsAppMessage()`  
**Destinatario:** Representante del estudiante (menor de edad)  
**Trigger:** Cuando un estudiante escanea su código de acceso

#### Mensaje de Entrada:
```
¡Hola! 👋

📥 **Ingreso registrada** 📚

**Estudiante:** [Nombres Apellidos]
**Código:** [Código del Estudiante]
**Fecha:** DD/MM/YYYY
**Hora:** HH:MM
**Notas:** [Notas adicionales si existen]

🏫 U.E JOSE MARIA VARGAS
💡 Este es un mensaje automático
```

#### Mensaje de Salida:
```
¡Hola! 👋

📤 **Salida registrada** 📚

**Estudiante:** [Nombres Apellidos]
**Código:** [Código del Estudiante]
**Fecha:** DD/MM/YYYY
**Hora:** HH:MM
**Notas:** [Notas adicionales si existen]

⏱️ **Tiempo en el instituto:** X horas y Y minutos

🏫 U.E JOSE MARIA VARGAS
💡 Este es un mensaje automático
```

**Variables utilizadas:**
- `{student.nombres}` - Nombres del estudiante
- `{student.apellidos}` - Apellidos del estudiante
- `{student.codigo}` - Código único del estudiante
- `{access_time}` - Fecha y hora del acceso
- `{notes}` - Notas opcionales registradas
- `{timeInSchool}` - Tiempo total en el instituto (solo salida)

**Características especiales:**
- Solo se envía para estudiantes menores de edad
- Calcula automáticamente el tiempo permanecido en caso de salida
- Envía a todos los teléfonos del representante
- Formatea números según código de país configurado

---

## 📝 Plantillas Configurables

### Estructura de Plantillas

**Modelo:** `App\Models\WhatsAppTemplate`  
**Tabla:** `whatsapp_templates`

Las plantillas permiten personalizar mensajes sin modificar código. Actualmente no hay plantillas creadas en la base de datos, pero el sistema soporta:

**Campos disponibles:**
- `name` - Nombre de la plantilla
- `description` - Descripción del uso
- `content` - Contenido del mensaje con variables
- `variables` - Array JSON de variables disponibles
- `category` - Categoría (notification, reminder, marketing, alert)
- `is_active` - Estado activo/inactivo
- `usage_count` - Contador de usos
- `last_used_at` - Última vez utilizada

**Ejemplo de variable en plantilla:**
```
Estimado {{nombre_paciente}}, su cita es el {{fecha}} a las {{hora}}.
```

**Uso en código:**
```php
$template->getProcessedContent([
    'nombre_paciente' => $paciente->nombre,
    'fecha' => $cita->fecha_inicio->format('d/m/Y'),
    'hora' => $cita->fecha_inicio->format('h:i A')
]);
```

---

## 🔧 Servicios y APIs Utilizados

### 1. WhatsAppService

**Ubicación:** `app/Services/WhatsAppService.php`

**Configuración:**
- **API URL:** `http://82.165.213.124:8092` (configurable en `.env`)
- **Timeout:** 30 segundos por defecto
- **Autenticación:** API Key por empresa + Company ID

**Métodos disponibles:**
- `sendMessage($to, $message)` - Enviar mensaje de texto
- `sendDocument($to, $filePath, $caption)` - Enviar documento PDF/Excel/Word
- `sendImage($to, $filePath, $caption)` - Enviar imagen
- `sendInteractiveMessage($to, $interactiveMessage)` - Mensaje con botones
- `getStatus()` - Verificar estado de conexión
- `getQRCode()` - Obtener código QR para conectar
- `connect()` / `disconnect()` / `reconnect()` - Control de conexión
- `removeSession()` - Eliminar sesión completamente

**Headers requeridos:**
```php
[
    'X-API-Key' => $apiKey,
    'X-Company-Id' => $companyId,
    'Content-Type' => 'application/json'
]
```

---

### 2. CitaNotificationService

**Ubicación:** `app/Services/CitaNotificationService.php`

**Responsabilidades:**
- Gestionar todas las notificaciones relacionadas con citas médicas
- Programar recordatorios automáticos
- Manejar confirmaciones integradas
- Formatear números telefónicos según país
- Resolver destinatarios (paciente/tutor)

**Métodos públicos:**
- `notificarNuevaCita(Cita $cita)` - Notificar creación de cita
- `programarRecordatorios(Cita $cita)` - Programar recordatorios automáticos
- `cancelarRecordatoriosPendientes(Cita $cita)` - Cancelar recordatorios
- `reprogramarRecordatorios(Cita $cita)` - Reprogramar tras cambio de fecha
- `notificarCambioEstado(Cita $cita, string $estadoAnterior)` - Notificar cambio de estado
- `notificarCancelacion(Cita $cita)` - Notificar cancelación
- `enviarRecordatorio(Cita $cita)` - Enviar recordatorio manual

**Configuración de país:**
- Obtiene código telefónico del país configurado en la empresa
- Por defecto: Venezuela (+58)
- Formatea automáticamente números locales a formato internacional

---

### 3. SendAccessWhatsAppNotificationJob

**Ubicación:** `app/Jobs/SendAccessWhatsAppNotificationJob.php`

**Tipo:** Job en cola (ShouldQueue)

**Responsabilidades:**
- Enviar notificaciones de acceso estudiantil
- Calcular tiempo permanecido en el instituto
- Formatear números según país
- Enviar a múltiples teléfonos del representante

**Lógica de tiempo en el instituto:**
```php
// Calcula diferencia entre entrada y salida
$entryTime = Carbon::parse($entrada->access_time);
$exitTime = Carbon::parse($salida->access_time);
$diff = $entryTime->diff($exitTime);

// Formato: "2 horas y 30 minutos" o "45 minutos"
```

**Manejo de errores:**
- Registra logs detallados de éxito/fallo
- Intenta enviar a todos los teléfonos del representante
- Continúa incluso si algunos teléfonos fallan

---

## 📊 Estadísticas del Sistema

### Mensajes Programados en BD

**Tabla:** `whatsapp_scheduled_messages`

**Tipos de notificación registrados:**
- `cita_recordatorio_12h` - Recordatorio 12 horas antes
- `cita_recordatorio_6h` - Recordatorio 6 horas antes
- `cita_recordatorio_1h` - Recordatorio 1 hora antes
- `manual` - Recordatorio enviado manualmente
- `nueva_cita` - Notificación de nueva cita

**Estados posibles:**
- `pending` - Pendiente de envío
- `sent` - Enviado exitosamente
- `failed` - Falló el envío
- `cancelled` - Cancelado (cita cancelada/reprogramada)

**Reintentos:**
- Máximo 3 intentos por mensaje
- Registro de error en cada intento fallido
- Backoff exponencial entre reintentos

---

## 🔐 Configuración de Seguridad

### Autenticación Multi-Empresa

Cada empresa tiene su propia:
- **API Key** única almacenada en `empresas.whatsapp_api_key`
- **Company ID** para identificar la empresa en requests
- **País configurado** para formateo de teléfonos

### Tokens de Confirmación

- Generados con `bin2hex(random_bytes(20))` (40 caracteres hexadecimales)
- URLs firmadas temporalmente con `URL::temporarySignedRoute()`
- Expiración configurable (por defecto 24 horas)
- Validación de firma en el endpoint de confirmación

---

## 🛠️ Comandos Artisan Relacionados

### Gestión de WhatsApp

```bash
# Verificar configuración
php artisan whatsapp:check-config

# Depurar conexiones
php artisan whatsapp:debug

# Procesar mensajes programados
php artisan whatsapp:process-scheduled

# Reintentar mensajes fallidos
php artisan whatsapp:retry-failed

# Sincronizar empresas con API
php artisan whatsapp:sync-companies

# Generar token de API
php artisan whatsapp:generate-token

# Iniciar API de WhatsApp
php artisan whatsapp:start-api
```

---

## 📁 Archivos Clave del Sistema

### Services
- `app/Services/CitaNotificationService.php` - Servicio principal de notificaciones de citas
- `app/Services/WhatsAppService.php` - Servicio de integración con API de WhatsApp
- `app/Services/WhatsAppNotificationService.php` - Servicio alternativo de notificaciones

### Jobs
- `app/Jobs/SendAccessWhatsAppNotificationJob.php` - Job de notificaciones de acceso
- `app/Jobs/ProcessScheduledWhatsAppMessages.php` - Procesamiento de mensajes programados
- `app/Jobs/RetryFailedWhatsAppMessages.php` - Reintento de mensajes fallidos

### Models
- `app/Models/WhatsAppTemplate.php` - Modelo de plantillas
- `app/Models/WhatsAppScheduledMessage.php` - Modelo de mensajes programados
- `app/Models/CitaConfirmacion.php` - Modelo de confirmaciones de citas

### Controllers
- `app/Http/Controllers/Admin/WhatsAppController.php` - Controller administrativo
- `app/Http/Controllers/WhatsAppController.php` - Controller público
- `app/Http/Controllers/WhatsAppProxyController.php` - Proxy para API externa

### Livewire Components
- `app/Livewire/Admin/Whatsapp/WhatsAppSendMessages.php` - Componente de envío manual
- `app/Livewire/Admin/Whatsapp/WhatsAppTemplates.php` - Gestión de plantillas
- `app/Livewire/Admin/Whatsapp/WhatsAppScheduledMessages.php` - Mensajes programados
- `app/Livewire/Admin/Whatsapp/WhatsAppConnection.php` - Gestión de conexión

### Console Commands
- `app/Console/Commands/ProcessWhatsAppScheduledMessages.php`
- `app/Console/Commands/RetryFailedWhatsAppMessages.php`
- `app/Console/Commands/SyncWhatsAppCompanies.php`
- `app/Console/Commands/TestStudentWhatsAppNotification.php`

---

## 🎯 Mejores Prácticas Implementadas

### 1. Formateo de Teléfonos
- Limpieza automática de caracteres no numéricos
- Detección inteligente de código de país
- Soporte para números locales e internacionales
- Eliminación de ceros iniciales

### 2. Manejo de Menores de Edad
- Detección automática de pacientes menores
- Envío a tutor/representante cuando aplica
- Priorización de teléfono del tutor sobre el del paciente

### 3. Mensajes Específicos por Contexto (NUEVO)
- **Confirmación de cita**: Mensaje diferenciado para pacientes y doctores
- **Cambio de estado genérico**: Para otros estados (en progreso, finalizada, etc.)
- Cada mensaje adaptado al destinatario y contexto específico

### 4. Logging y Monitoreo
- Logs detallados de cada envío
- Registro de éxitos y fallos
- Información contextual completa (IDs, teléfonos, tipos)

### 5. Resiliencia
- Timeouts configurables para evitar bloqueos
- Reintentos automáticos con backoff
- Fallback graceful cuando falla el servicio

### 6. Personalización
- Saludos diferenciados para menores/adultos
- Inclusión condicional de información (pre-consulta, tiempo en instituto)
- Soporte para múltiples idiomas (estructura preparada)
- Información específica según rol (paciente vs doctor)

---

## 📈 Métricas de Uso

### Volumen Estimado de Mensajes

Basado en la estructura del sistema:

**Por cada cita médica:**
- 1 mensaje inicial (paciente + médico) = **2 mensajes**
- 3 recordatorios automáticos (12h, 6h, 1h) = **3 mensajes**
- 1 mensaje de confirmación al paciente (opcional) = **1 mensaje**
- 1 mensaje de confirmación al doctor (NUEVO) = **1 mensaje**
- 1 mensaje de cancelación (si aplica) = **1 mensaje**
- 1-2 mensajes de cambio de estado = **1-2 mensajes**

**Total estimado por cita:** 8-10 mensajes (anteriormente 6-8)

**Por cada acceso estudiantil:**
- 1 mensaje de entrada
- 1 mensaje de salida (con tiempo calculado)

**Total estimado por día escolar:** 2 mensajes × número de estudiantes

---

## 🔮 Futuras Mejoras Sugeridas

1. **Plantillas Predefinidas:** Crear seeders con plantillas estándar
2. **Análisis de Entrega:** Tracking de mensajes leídos/entregados
3. **Respuestas Automáticas:** Chatbot básico para confirmaciones
4. **Estadísticas Avanzadas:** Dashboard de métricas de envío
5. **Opt-in/Opt-out:** Gestión de preferencias de notificación
6. **Horarios Inteligentes:** No enviar mensajes fuera de horario laboral
7. **Personalización Avanzada:** Variables dinámicas según contexto
8. **Multicanal:** Fallback a SMS si WhatsApp falla

---

## 📞 Soporte Técnico

Para modificaciones de mensajes o configuración:

1. **Modificar textos:** Editar métodos en `CitaNotificationService.php`
   - Confirmación paciente: `construirMensajeConfirmacionPaciente()`
   - Confirmación doctor: `construirMensajeConfirmacionMedico()`
   - Cambio estado genérico: `construirMensajeCambioEstado()` / `construirMensajeCambioEstadoMedico()`
2. **Agregar nuevos tipos:** Crear nuevo método `construirMensaje*()`
3. **Cambiar horarios:** Modificar array `$recordatorios` en `programarRecordatorios()`
4. **Personalizar por empresa:** Usar condicionales basados en `$this->empresaId`
5. **Configurar notificaciones:** Usar panel admin/calendario/configurar-notificaciones
   - Activar/desactivar notificaciones por estado
   - Configurar por destinatario (paciente/doctor)
   - Aplicar recomendaciones predefinidas

---

**Documento generado automáticamente el 13 de mayo de 2026**  
**Última actualización del sistema:** Mayo 2026  
**Contacto:** Equipo de Desarrollo Solumed
