# Guía de Migración: Sistema Multi-Proveedor de Mensajería

## Resumen

El sistema de mensajería de Pupila Inc. ha evolucionado de una integración hardcodeada con WhatsApp Lite a un **sistema multi-proveedor** que permite utilizar múltiples canales de comunicación (WhatsApp Lite, Meta WhatsApp, Twilio, Google FCM) desde una interfaz unificada.

## ¿Por qué migrar?

| Antes | Ahora |
|-------|-------|
| Un solo proveedor (WhatsApp Lite) | Múltiples proveedores configurables |
| Credenciales en tabla `empresas` | Credenciales encriptadas por conexión |
| Configuración global por empresa | Configuración por módulo/acción/destinatario |
| Sin testing de conexiones | Pruebas de conexión integradas |
| Sin fallback | Fallback automático entre canales |

## Arquitectura Nueva

```
┌─────────────────────────────────────────────────────────────┐
│                    Tu Aplicación                             │
├─────────────────────────────────────────────────────────────┤
│  UnifiedNotificationService (Punto de entrada único)        │
├─────────────────────────────────────────────────────────────┤
│  MessagingConnectionManager (Gestor de conexiones)          │
├─────────────────────────────────────────────────────────────┤
│  ModuleNotificationChannel (Configuración por módulo)       │
├───────────────┬─────────────────┬───────────────────────────┤
│ WhatsAppLite  │ WhatsAppMeta    │ Twilio    │ GoogleFCM    │
│ Provider      │ Provider        │ Provider  │ Provider     │
└───────────────┴─────────────────┴───────────┴──────────────┘
```

## Guía de Migración para Desarrolladores

### 1. Cambios en el Envío de Notificaciones

#### Antes (Legacy)
```php
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService($empresa);
$whatsapp->sendMessage($telefono, $mensaje);
```

#### Ahora (Nuevo Sistema)
```php
use App\Services\Messaging\UnifiedNotificationService;

$service = app(UnifiedNotificationService::class);
$service->notify(
    empresaId: $empresa->id,
    module: 'citas',
    action: 'recordatorio',
    recipientType: 'paciente',
    phoneNumber: $telefono,
    message: $mensaje,
    context: ['cita_id' => $cita->id]
);
```

### 2. Adaptar Servicios Existentes

#### CitaNotificationService
El servicio ya está adaptado para funcionar con ambos sistemas. Por defecto usa el método legacy, pero puedes habilitar el sistema unificado:

```php
// Habilitar sistema unificado
$service = new CitaNotificationService();
$service->useUnifiedService();

// O vía configuración (.env)
MESSAGING_USE_UNIFIED_SERVICE=true
```

### 3. Uso del MessagingConnectionManager

Para obtener una conexión específica:

```php
use App\Services\Messaging\MessagingConnectionManager;

$manager = app(MessagingConnectionManager::class);

// Obtener conexión para un módulo específico
$connection = $manager->getConnectionForModule(
    empresaId: 1,
    moduleKey: 'citas',
    actionKey: 'recordatorio',
    recipientType: 'paciente'
);

// Obtener el provider instanciado
$provider = $manager->getProviderForConnection($connection);
$provider->send($telefono, $mensaje);
```

### 4. Configurar Canales por Módulo

Desde la UI: **Admin → Comunicaciones → Canales**

O programáticamente:

```php
use App\Models\ModuleNotificationChannel;

ModuleNotificationChannel::updateOrCreate(
    [
        'empresa_id' => 1,
        'module_key' => 'citas',
        'action_key' => 'recordatorio',
        'recipient_type' => 'paciente',
    ],
    [
        'connection_id' => $connection->id,
        'enabled' => true,
        'priority' => 1,
    ]
);
```

### 5. Crear Nuevas Conexiones

```php
use App\Models\MessagingProvider;
use App\Models\MessagingConnection;
use App\Services\Messaging\CredentialEncryptionService;

$encryption = app(CredentialEncryptionService::class);

$provider = MessagingProvider::where('slug', 'whatsapp_lite')->first();

$connection = MessagingConnection::create([
    'empresa_id' => 1,
    'provider_id' => $provider->id,
    'name' => 'WhatsApp Principal',
    'credentials' => $encryption->encrypt([
        'api_url' => 'http://localhost:8092',
        'api_key' => 'mi-api-key',
    ]),
    'configuration' => json_encode(['timeout' => 30]),
    'status' => 'active',
]);
```

### 6. Probar Conexiones

```php
use App\Services\Messaging\MessagingConnectionManager;

$manager = app(MessagingConnectionManager::class);
$connection = MessagingConnection::find(1);

$result = $manager->testConnection($connection);

// Resultado:
// [
//     'success' => true,
//     'message' => 'Conexión exitosa',
//     'provider' => 'WhatsApp Lite',
//     'tested_at' => '2024-01-15 10:30:00'
// ]
```

## Feature Flags

Controla el comportamiento del sistema desde `.env`:

| Variable | Default | Descripción |
|----------|---------|-------------|
| `MESSAGING_USE_UNIFIED_SERVICE` | `false` | Usar el nuevo sistema unificado |
| `MESSAGING_DEFAULT_PROVIDER` | `whatsapp_lite` | Proveedor por defecto |
| `MESSAGING_CACHE_TTL` | `300` | TTL del cache en segundos |

## Backwards Compatibility

El sistema mantiene compatibilidad completa con código existente:

1. **WhatsAppService** sigue funcionando pero emite warnings de deprecated
2. Las credenciales en `empresas` se mantienen hasta migración manual
3. El migrador automático crea conexiones basadas en configuración existente

## Migrar Credenciales Existentes

El sistema incluye un comando artisan para migrar:

```bash
php artisan messaging:migrate-from-companies
```

Este comando:
1. Lee `whatsapp_api_key`, `whatsapp_status` de cada empresa
2. Crea una conexión `WhatsAppLite` por empresa
3. Configura como default para todos los módulos
4. Genera un log de la migración

## Testing

### Test Unitario de Provider
```php
use App\Services\Messaging\Providers\WhatsAppLiteProvider;

$provider = new WhatsAppLiteProvider([
    'api_url' => 'http://localhost:8092',
    'api_key' => 'test-key',
]);

$result = $provider->testConnection();
$this->assertTrue($result['success']);
```

### Test de Notificación
```php
$service = app(UnifiedNotificationService::class);

$result = $service->notify(
    empresaId: 1,
    module: 'citas',
    action: 'creacion',
    recipientType: 'paciente',
    phoneNumber: '+1234567890',
    message: 'Su cita ha sido creada'
);

$this->assertTrue($result);
```

## Errores Comunes

### "No se encontró conexión para el módulo"
**Causa:** No hay `ModuleNotificationChannel` configurado para esa combinación.
**Solución:** Configurar el canal en UI o crear el registro manualmente.

### "Provider no encontrado"
**Causa:** El `provider_id` en la conexión no existe o está inactivo.
**Solución:** Verificar que el provider exista y esté activo en `messaging_providers`.

### "Credenciales inválidas"
**Causa:** Las credenciales encriptadas no pueden ser descifradas.
**Solución:** Verificar que `APP_KEY` sea la misma que se usó para encriptar.

## Lista de Verificación para Migración

- [ ] Actualizar variables de entorno (`MESSAGING_USE_UNIFIED_SERVICE=true`)
- [ ] Ejecutar migrador: `php artisan messaging:migrate-from-companies`
- [ ] Verificar conexiones creadas en UI
- [ ] Probar cada canal configurado
- [ ] Actualizar servicios que usan WhatsAppService directamente
- [ ] Ejecutar tests de integración
- [ ] Monitorear logs durante transición

## Recursos Adicionales

- [Documentación técnica](./MULTI_PROVIDER_MESSAGING_PLAN.md)
- [Configuración de canales por módulo](./SISTEMA_NOTIFICACIONES_SELECTIVAS.md)
- [API Reference](./API_MESSAGING.md) (próximamente)

## Soporte

Para dudas o problemas durante la migración, contactar al equipo de desarrollo con:
- Logs de error
- Pasos para reproducir
- Versión de Laravel y PHP