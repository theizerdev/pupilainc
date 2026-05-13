# 📋 Flujo de Notificaciones cuando una Cita se Confirma

## Resumen Ejecutivo

Cuando una cita cambia al estado **"confirmada"**, el sistema realiza las siguientes acciones en orden:

---

## 🔁 Flujo Completo de Ejecución

### 1️⃣ **Envío de Pre-Consulta** (EXISTENTE - NO MODIFICADO)
**Archivo:** `app/Models/Cita.php` → Método `cambiarEstado()`  
**Líneas:** 334-342

```php
elseif ($nuevoEstado === self::ESTADO_CONFIRMADA) {
    // Solo enviar preconsulta si está pendiente y no hay respuestas completadas
    $yaTieneRespuestas = $this->respuestasPreconsulta()
        ->where('completado', true)
        ->exists();

    if ($this->estado_preconsulta === 'pendiente' && !$yaTieneRespuestas) {
        $this->crearPreconsultaYEnviarWhatsApp();  // ← ENVÍA PRE-CONSULTA
    }
    $this->crearConsultaSiNoExiste(true);
}
```

**Qué hace:**
- ✅ Verifica si la pre-consulta está pendiente
- ✅ Verifica si el paciente ya completó el cuestionario
- ✅ Si no tiene respuestas, crea y envía el formulario de pre-consulta por WhatsApp
- ✅ Crea la consulta médica asociada

**Mensaje enviado al paciente:**
```
Para agilizar su atencion, complete el cuestionario de pre-consulta antes de su cita:
https://solumed.com/preconsulta/[token]
```

---

### 2️⃣ **Notificación de Cambio de Estado** (NUEVO - AGREGADO)
**Archivo:** `app/Livewire/Admin/Calendario.php` → Línea 936  
**Servicio:** `app/Services/CitaNotificationService.php` → Método `notificarCambioEstado()`

```php
$cita->cambiarEstado($nuevoEstado);  // Paso 1: Envía pre-consulta
$notificacion = $this->notificarCambioEstado($cita, $estadoAnterior);  // Paso 2: Notifica
```

**Qué hace ahora (CON NUESTRA MODIFICACIÓN):**
- ✅ Detecta que el estado es "confirmada"
- ✅ Envía mensaje ESPECÍFICO al paciente confirmando la cita
- ✅ Envía mensaje ESPECÍFICO al doctor informando la confirmación

---

## 📱 Mensajes Enviados (Después de la Modificación)

### Al Paciente (2 mensajes posibles):

#### A) Formulario de Pre-Consulta (EXISTENTE)
```
🏥 *Recordatorio de Cita Médica*

Estimado(a) *[Nombre]*,

👤 Paciente: [Nombre]
👨‍⚕️ Médico: Dr(a). [Médico]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM

────────────────────
Para agilizar su atencion, complete el cuestionario de pre-consulta antes de su cita:

https://solumed.com/preconsulta/[token]

Es confidencial y nos ayudara a brindarle mejor atencion.
```

#### B) Confirmación de Cita (NUEVO)
```
✅ *Cita Confirmada*

Estimado(a) *[Nombre]*,

Su cita médica ha sido confirmada exitosamente:

👤 Paciente: [Nombre]
👨‍⚕️ Médico: Dr(a). [Médico]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo]

Por favor, llegue 15 minutos antes de su cita.
Si necesita cancelar o reprogramar, comuníquese con nosotros.
```

---

### Al Doctor (1 mensaje NUEVO):

```
✅ *Cita Confirmada por el Paciente*

Dr(a). *[Nombre del Doctor]*,

El paciente ha confirmado su asistencia a la siguiente cita:

👤 Paciente: *[Nombre del Paciente]*
🏥 Especialidad: [Especialidad]
📅 Fecha: DD/MM/YYYY
🕐 Hora: HH:MM AM/PM
📋 Motivo: [Motivo]

━━━━━━━━━━━━━━━━━━━━
ℹ️ Esta cita está confirmada en su agenda.
El paciente llegará 15 minutos antes de la hora programada.
```

---

## ⚙️ Configuración Requerida

### En Base de Datos:
La configuración de notificaciones debe tener activado el estado "confirmada" para ambos destinatarios:

```php
// app/Models/ConfiguracionNotificacion.php

$configPaciente = [
    'confirmada' => true,  // ✅ Debe ser TRUE
];

$configDoctor = [
    'confirmada' => true,  // ✅ Debe ser TRUE
];
```

### Cómo Verificar/Configurar:
1. Ir a: `admin/calendario/configurar-notificaciones`
2. Sección "Notificaciones a Pacientes" → Activar "Confirmada"
3. Sección "Notificaciones a Doctores" → Activar "Confirmada"
4. Guardar configuración

---

## 🎯 Resultado Final

### Antes de la Modificación:
- ❌ Paciente recibe: Solo pre-consulta (sin confirmación explícita)
- ❌ Doctor recibe: Nada cuando el paciente confirma

### Después de la Modificación:
- ✅ Paciente recibe: Pre-consulta + Confirmación de cita
- ✅ Doctor recibe: Notificación específica de confirmación del paciente

---

## 📊 Estadísticas de Mensajes por Cita Confirmada

| Destinatario | Tipo de Mensaje | Cantidad |
|--------------|----------------|----------|
| Paciente | Pre-consulta (si está pendiente) | 0-1 |
| Paciente | Confirmación de cita | 1 |
| Doctor | Notificación de confirmación | 1 |
| **Total** | | **2-3 mensajes** |

---

## 🔍 Puntos Clave

### ✅ Lo que NO cambió:
- El envío de pre-consulta sigue funcionando igual
- La lógica de verificación de respuestas completadas se mantiene
- El token de pre-consulta se genera de la misma forma
- Los recordatorios automáticos (12h, 6h, 1h) no se ven afectados

### ✅ Lo que SÍ se agregó:
- Mensaje específico de confirmación al paciente
- Mensaje específico de confirmación al doctor
- Diferenciación entre "cambio de estado genérico" y "confirmación específica"

### ✅ Beneficios:
1. **Mejor comunicación**: El doctor sabe inmediatamente cuando un paciente confirma
2. **Confirmación clara**: El paciente recibe confirmación explícita de que su cita fue registrada
3. **Información completa**: El doctor recibe todos los detalles de la cita confirmada
4. **Profesionalismo**: Mensajes específicos y bien diseñados para cada contexto

---

## 🧪 Pruebas Recomendadas

### Escenario 1: Cita sin pre-consulta previa
1. Crear cita en estado "programada"
2. Cambiar estado a "confirmada"
3. **Resultado esperado:**
   - ✅ Paciente recibe pre-consulta + confirmación
   - ✅ Doctor recibe notificación de confirmación

### Escenario 2: Cita con pre-consulta ya completada
1. Crear cita y completar pre-consulta
2. Cambiar estado a "confirmada"
3. **Resultado esperado:**
   - ✅ Paciente recibe SOLO confirmación (no pre-consulta duplicada)
   - ✅ Doctor recibe notificación de confirmación

### Escenario 3: Configuración desactivada
1. Desactivar notificación "confirmada" para doctores
2. Cambiar estado de cita a "confirmada"
3. **Resultado esperado:**
   - ✅ Paciente recibe sus mensajes
   - ❌ Doctor NO recibe notificación (por configuración)

---

## 📝 Archivos Modificados

### Modificados:
- ✅ `app/Services/CitaNotificationService.php`
  - Agregado: `construirMensajeConfirmacionPaciente()`
  - Agregado: `construirMensajeConfirmacionMedico()`
  - Modificado: `notificarCambioEstado()` para detectar estado "confirmada"

### No Modificados (funcionan como antes):
- ✅ `app/Models/Cita.php` → Método `cambiarEstado()`
- ✅ `app/Models/Cita.php` → Método `crearPreconsultaYEnviarWhatsApp()`
- ✅ `app/Livewire/Admin/Calendario.php` → Flujo de ejecución

---

## 💡 Notas Importantes

1. **La pre-consulta tiene prioridad**: Si el paciente ya completó el cuestionario, NO se envía de nuevo
2. **Los mensajes son independientes**: La confirmación se envía aunque no haya pre-consulta
3. **Configuración flexible**: Se puede activar/desactivar por empresa y por destinatario
4. **Sin efectos secundarios**: No afecta otros estados ni otros tipos de notificaciones

---

**Documento creado:** 13 de mayo de 2026  
**Versión del sistema:** Solumed v1.0  
**Modificación:** Agregado de notificación de confirmación al doctor
