# Sistema de Confirmación de Citas Médicas

## 📋 Resumen

Sistema completo para gestionar confirmaciones de citas médicas vía WhatsApp, Email y SMS con soporte multi-empresa, reintentos automáticos y panel de administración.

## 🏗️ Arquitectura

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────┐
│                    SISTEMA DE CONFIRMACIONES                  │
├─────────────────────────────────────────────────────────────┤
│  Modelos          │  Servicios           │  Jobs             │
│  ─────────────    │  ─────────────────   │  ─────────────    │
│  • Cita           │  • CitaConfirmation  │  • SendInitial    │
│  • CitaConfirmaci-│    Service           │    Confirmation   │
│    on             │  • WhatsAppService   │  • SendBulk       │
│                   │                      │    Confirmations  │
│                   │                      │  • RetryFailed    │
│                   │                      │    Confirmation   │
├─────────────────────────────────────────────────────────────┤
│  Controladores    │  Comandos            │  Frontend         │
│  ─────────────    │  ─────────────────   │  ─────────────    │
│  • CitaConfirmat- │  • confirmations:    │  • Livewire:      │
│    ionController  │    process           │    CitaConfirmat- │
│                   │                      │    ionStats       │
│                   │                      │  • Blade Views    │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Flujo de Trabajo

### 1. Creación de Cita
```php
// Cuando se crea una cita futura (>24h)
$cita->solicitarConfirmacion(); // o automático vía observer
```

### 2. Envío de Confirmación
```
CitaConfirmationService::iniciarConfirmacion()
  ├── Cancela confirmaciones anteriores
  ├── Crea nueva confirmación (token único)
  ├── Envía mensaje WhatsApp con botones interactivos
  └── Fallback a mensaje de texto si falla
```

### 3. Respuesta del Paciente
```
Paciente recibe mensaje → Responde SI/NO o usa botones
  ↓
Webhook/WhatsApp API → CitaConfirmationController
  ↓
procesarRespuesta() → Actualiza estado de cita
```

### 4. Reintentos Automáticos
```
Job RetryFailedConfirmation (cada hora)
  ├── Busca confirmaciones pendientes con <3 intentos
  ├── Reenvía mensaje
  └── Marca como "sin_respuesta" si agota intentos
```

## 📊 Estados de Confirmación

| Estado | Descripción | Color |
|--------|-------------|-------|
| `pendiente` | Esperando respuesta del paciente | 🟡 Amarillo |
| `confirmado` | Paciente confirmó asistencia | 🟢 Verde |
| `rechazado` | Paciente canceló/rechazó | 🔴 Rojo |
| `sin_respuesta` | Agotó intentos sin respuesta | ⚪ Gris |
| `expirado` | Pasó el tiempo límite (24h) | ⚫ Negro |

## 🛠️ Instalación y Configuración

### 1. Migraciones
```bash
php artisan migrate
```

### 2. Configurar Queue Worker
```bash
# Terminal 1: Procesar jobs
php artisan queue:work

# O usar supervisor para producción
```

### 3. Configurar Cron (Programador de Tareas)
```bash
# Añadir al crontab
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Configurar WhatsApp API
```env
WHATSAPP_API_URL=http://82.165.213.124:8092
WHATSAPP_API_KEY=tu-api-key
```

## 🚀 Uso

### Panel de Administración
```
URL: /admin/citas/confirmaciones
```

Funcionalidades:
- 📊 Estadísticas en tiempo real
- 📋 Listado de confirmaciones con filtros
- 🔄 Reintentar confirmaciones manualmente
- 📈 Tasas de confirmación y respuesta

### Comandos Artisan

```bash
# Procesar todas las confirmaciones
php artisan confirmations:process

# Procesar solo pendientes
php artisan confirmations:process --type=pending

# Procesar expiradas
php artisan confirmations:process --type=expired

# Reintentar fallidas
php artisan confirmations:process --type=retry

# Con límite
php artisan confirmations:process --type=all --limit=50
```

### Uso Programático

```php
use App\Services\CitaConfirmationService;
use App\Models\Cita;

// Iniciar confirmación manual
$service = new CitaConfirmationService();
$confirmacion = $service->iniciarConfirmacion($cita);

// Procesar respuesta
$service->procesarRespuesta($token, 'SI'); // o 'NO'

// Reintentar
$service->reintentarConfirmacion($confirmacion);

// Estadísticas
$stats = $service->obtenerEstadisticas(30); // últimos 30 días
```

## 🔗 Endpoints API

### Webhook WhatsApp
```http
POST /webhook/whatsapp/confirmations
Content-Type: application/json

{
  "phone": "584123456789",
  "message": "SI",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Confirmar/Cancelar vía URL
```http
GET /citas/confirmar?token={token}
GET /citas/cancelar?token={token}
```

### Estadísticas
```http
GET /api/confirmations/stats?dias=30
```

## 📱 Formato de Mensajes

### WhatsApp con Botones Interactivos
```
🏥 Confirmación de Cita Médica

Estimado(a) [Nombre],

¿Desea confirmar su cita médica?

📅 Fecha: 15/01/2024
🕐 Hora: 10:00 AM
👨‍⚕️ Médico: Dr. [Nombre]
🏥 Especialidad: [Especialidad]

[✅ Confirmar] [❌ Cancelar]

Tiene 24 horas para responder.
```

### Mensaje de Texto (Fallback)
```
Hola [Nombre], tiene una cita médica:

Fecha: 15/01/2024 a las 10:00 AM
Dr. [Nombre] - [Especialidad]

Para confirmar o cancelar, responda:
SI - Confirmar
NO - Cancelar

También puede usar:
Confirmar: [URL]
Cancelar: [URL]
```

## ⚙️ Configuración Avanzada

### En `config/whatsapp.php`
```php
return [
    'api_url' => env('WHATSAPP_API_URL', 'http://82.165.213.124:8092'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'timeout' => 30,
    'max_retries' => 3,
    'expiration_hours' => 24,
];
```

### Personalizar Mensajes
Editar métodos en `CitaConfirmationService`:
- `construirMensajeConBotonesInteractivos()`
- `construirMensajeConEnlaces()`
- `generarMensajeConfirmacion()`

## 🔒 Seguridad

- Tokens únicos de 40 caracteres (hex)
- URLs firmadas temporalmente (24h)
- Filtros por empresa/sucursal (multi-tenant)
- Rate limiting en webhooks
- Validación de respuestas

## 🧪 Testing

```bash
# Ejecutar pruebas del sistema
php tests/test_confirmation_system.php

# Probar comando
php artisan confirmations:process --type=all --limit=10

# Ver logs
tail -f storage/logs/confirmations.log
tail -f storage/logs/laravel.log
```

## 🐛 Troubleshooting

### Problema: No se envían mensajes
```bash
# Verificar configuración
php artisan tinker --execute="config('whatsapp.api_url');"

# Verificar queue
php artisan queue:status

# Probar WhatsApp API
curl http://82.165.213.124:8092/api/whatsapp/status
```

### Problema: Jobs no se ejecutan
```bash
# Reiniciar queue worker
php artisan queue:restart

# Verificar failed jobs
php artisan queue:failed

# Reintentar failed jobs
php artisan queue:retry all
```

### Problema: Webhook no responde
```bash
# Verificar ruta
php artisan route:list | grep webhook

# Probar endpoint
curl -X POST http://localhost/webhook/whatsapp/confirmations \
  -H "Content-Type: application/json" \
  -d '{"phone":"584123456789","message":"SI"}'
```

## 📈 Métricas y Monitoreo

El sistema registra automáticamente:
- ✅ Confirmaciones enviadas/exitosas/fallidas
- ⏱️ Tiempo de respuesta promedio
- 📊 Tasa de confirmación por médico/especialidad
- 🔄 Intentos de reenvío
- ❌ Errores y excepciones

## 📝 Changelog

### v1.0.0 (2024-01-15)
- ✅ Implementación inicial completa
- ✅ Soporte WhatsApp Business API
- ✅ Panel de administración Livewire
- ✅ Jobs de procesamiento masivo
- ✅ Sistema de reintentos automáticos
- ✅ Webhook para respuestas
- ✅ Estadísticas y reportes

---

## 🤝 Soporte

Para reportar issues o solicitar mejoras:
1. Revisar logs en `storage/logs/`
2. Ejecutar `php tests/test_confirmation_system.php`
3. Verificar configuración WhatsApp API
4. Contactar al equipo de desarrollo

**Documentación técnica completa disponible en:** `/docs/CITA_CONFIRMATION_SYSTEM.md`
