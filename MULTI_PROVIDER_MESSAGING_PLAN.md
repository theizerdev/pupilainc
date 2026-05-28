# Integración de Sistema Multi-Proveedor de Mensajería

## Análisis del Estado Actual

El proyecto actualmente tiene:
- Un servicio `WhatsAppService` acoplado a una API Node.js personalizada (WhatsApp Lite)
- Configuración por empresa en la tabla `empresas` (campos: `whatsapp_api_key`, `whatsapp_status`, etc.)
- Sistema de notificaciones configurables vía `WhatsAppNotificationSetting` y `WhatsAppNotificationGate`
- Jobs para envío programado: `ProcessScheduledWhatsAppMessages`, `SendAccessWhatsAppNotificationJob`
- Múltiples puntos de uso: Citas, Enfermeros, Pedidos, Confirmaciones, etc.

**Problema**: Todo está hardcodeado al proveedor actual de WhatsApp, sin abstracción ni posibilidad de cambiar fácilmente.

**Oportunidad**: Convertir la integración actual en "WhatsApp Lite" como un provider más dentro del sistema multi-proveedor.

---

## Arquitectura Propuesta

### 1. Modelo de Base de Datos

Crear tablas para gestionar conexiones de mensajería:

#### Tabla: `messaging_providers`
Almacena los tipos de proveedores disponibles:
- `id`
- `name` (ej: "WhatsApp Lite", "WhatsApp Business API", "Twilio", "Google Firebase")
- `slug` (ej: "whatsapp_lite", "whatsapp_meta", "twilio", "google_fcm")
- `icon` (clase CSS o ruta de icono)
- `config_schema` (JSON con campos requeridos)
- `is_active` (boolean)
- `created_at`, `updated_at`

#### Tabla: `messaging_connections`
Almacena las credenciales y configuración por empresa:
- `id`
- `empresa_id` (FK a empresas)
- `provider_id` (FK a messaging_providers)
- `name` (nombre descriptivo, ej: "WhatsApp Principal")
- `credentials` (JSON encriptado con API keys, tokens, etc.)
- `configuration` (JSON con settings específicos: timeout, retry, etc.)
- `status` (enum: 'active', 'inactive', 'testing', 'error')
- `is_default_for` (JSON: qué módulos usa esta conexión como default)
- `last_test_at` (timestamp última prueba exitosa)
- `test_result` (JSON con resultado de la última prueba)
- `created_at`, `updated_at`

#### Tabla: `module_notification_channels`
Configura qué canal usar por módulo/tipo de notificación:
- `id`
- `empresa_id` (FK a empresas)
- `module_key` (ej: 'citas', 'usuarios', 'consultas', 'doctores')
- `action_key` (ej: 'creacion', 'recordatorio', 'confirmacion')
- `recipient_type` (ej: 'paciente', 'doctor', 'usuario')
- `connection_id` (FK a messaging_connections - qué conexión usar)
- `enabled` (boolean)
- `priority` (int - orden de fallback si hay múltiples canales)
- `created_at`, `updated_at`

#### Migraciones necesarias:
- Crear `messaging_providers` con seeders para WhatsApp Lite, Meta WhatsApp, Twilio, Google
- Crear `messaging_connections` 
- Crear `module_notification_channels`
- Mantener campos actuales en `empresas` para compatibilidad temporal

---

### 2. Capa de Abstracción - Interfaces y Contratos

#### Interface: `MessagingProviderInterface`
Ubicación: `app/Contracts/MessagingProviderInterface.php`

Métodos requeridos:
```php
public function send(string $to, string $message, array $options = []): bool;
public function sendWithTemplate(string $to, string $templateId, array $variables = []): bool;
public function getStatus(): array;
public function testConnection(): array;
public function getProviderName(): string;
public function getRequiredFields(): array;
```

#### Clases Provider Concretas:

**WhatsAppLiteProvider** (`app/Services/Messaging/Providers/WhatsAppLiteProvider.php`)
- **Envuelve la integración actual de WhatsApp** (API Node.js personalizada)
- Reutiliza la lógica existente de `WhatsAppService`
- Campos requeridos: `api_url`, `api_key`, `company_id` (extraídos de la configuración actual)
- Migración automática: Lee credenciales actuales de la tabla `empresas` y las convierte en una conexión
- Mantiene compatibilidad total con el sistema actual

**WhatsAppMetaProvider** (`app/Services/Messaging/Providers/WhatsAppMetaProvider.php`)
- Implementa API oficial de Meta (Graph API)
- Campos requeridos: `phone_number_id`, `access_token`, `business_account_id`, `webhook_verify_token`
- Soporte para plantillas aprobadas por Meta

**TwilioProvider** (`app/Services/Messaging/Providers/TwilioProvider.php`)
- Integra SDK de Twilio
- Campos requeridos: `account_sid`, `auth_token`, `phone_number`
- Soporte para SMS y WhatsApp via Twilio

**GoogleFCMProvider** (`app/Services/Messaging/Providers/GoogleFCMProvider.php`)
- Integra Firebase Cloud Messaging
- Campos requeridos: `project_id`, `private_key`, `client_email`, `api_key`
- Para notificaciones push y RCS

---

### 3. Factory y Registry de Proveedores

#### Clase: `MessagingProviderFactory`
Ubicación: `app/Services/Messaging/MessagingProviderFactory.php`

Responsabilidad:
- Instanciar el provider correcto según el tipo
- Cache de instancias para performance
- Método: `create(string $providerSlug, array $credentials): MessagingProviderInterface`

#### Clase: `MessagingConnectionManager`
Ubicación: `app/Services/Messaging/MessagingConnectionManager.php`

Responsabilidad:
- Obtener la conexión activa para una empresa
- Validar credenciales desde BD (desencriptar)
- Método: `getConnection(int $empresaId, ?string $moduleKey = null): MessagingConnection`
- Método: `getProviderForModule(int $empresaId, string $module, string $action, string $recipient): MessagingProviderInterface`

---

### 4. Servicio de Notificaciones Unificado

#### Clase: `UnifiedNotificationService`
Ubicación: `app/Services/Messaging/UnifiedNotificationService.php`

Reemplaza gradualmente a `CitaNotificationService`, `WhatsAppService`, etc.

Métodos principales:
```php
public function notify(int $empresaId, string $module, string $action, 
                      string $recipientType, string $phoneNumber, 
                      string $message, array $context = []): bool;

public function notifyWithFallback(int $empresaId, string $module, string $action,
                                   string $recipientType, string $phoneNumber,
                                   string $message): bool;
// Intenta con el canal principal, si falla usa el secondary
```

Características:
- Lee configuración de `module_notification_channels`
- Respeta `WhatsAppNotificationGate` para permisos
- Logging centralizado de todos los envíos
- Soporte para fallback automático

---

### 5. UI para Gestión de Conexiones

#### Livewire Component: `MessagingConnections`
Ubicación: `app/Livewire/Admin/Messaging/MessagingConnections.php`

Vista: `resources/views/livewire/admin/messaging/connections.blade.php`

Funcionalidades:
- Listar todas las conexiones de la empresa
- Agregar nueva conexión (wizard por pasos)
- Editar credenciales
- **Botón "Probar Conexión"**: Llama a `testConnection()` del provider
- Establecer como default para módulos específicos
- Ver estado y última prueba
- Eliminar conexión

Flujo de agregar conexión:
1. Seleccionar proveedor (WhatsApp Lite, WhatsApp Business API, Twilio, Google)
2. Formulario dinámico según `config_schema` del proveedor
3. Guardar credenciales encriptadas
4. Ejecutar prueba automática
5. Si pasa, permitir configurar módulos default

**Nota especial para WhatsApp Lite**: Al crear la primera conexión de este tipo, el sistema detectará automáticamente si ya existe configuración en la tabla `empresas` y ofrecerá migrarla.

---

### 6. UI para Configuración de Canales por Módulo

#### Livewire Component: `ModuleNotificationChannels`
Ubicación: `app/Livewire/Admin/Messaging/ModuleNotificationChannels.php`

Vista: `resources/views/livewire/admin/messaging/module-channels.blade.php`

Similar a `WhatsAppNotifications` actual pero más flexible:
- Tabs por sector (Citas, Usuarios, Consultas, Doctores, etc.)
- Por cada acción/destinatario, dropdown para seleccionar conexión
- Checkbox para habilitar/deshabilitar
- Indicador visual si la conexión seleccionada está activa
- Botón "Guardar configuración"

---

### 7. Adaptación del Código Existente

#### Paso 1: Wrapper de Compatibilidad
El `WhatsAppLiteProvider` mantendrá la API actual de `WhatsAppService` internamente, permitiendo que todo el código existente siga funcionando sin cambios.

#### Paso 2: Refactorizar Puntos Críticos

**Archivos a modificar:**
- `app/Services/CitaNotificationService.php`: Usar `UnifiedNotificationService`
- `app/Services/PedidoService.php`: Inyectar `UnifiedNotificationService`
- `app/Jobs/ProcessScheduledWhatsAppMessages.php`: Adaptar para usar conexiones configuradas
- `app/Jobs/SendAccessWhatsAppNotificationJob.php`: Igual
- `app/Livewire/Admin/Enfermeros/Edit.php` e `Index.php`: Usar nuevo servicio
- `app/Models/Cita.php` (método `crearPreconsultaYEnviarWhatsApp`): Adaptar

Estrategia:
- Mantener `WhatsAppService` como deprecated pero funcional
- Nuevo código usa `UnifiedNotificationService`
- Tests verifican ambos caminos durante transición

---

### 8. Encriptación de Credenciales

#### Service: `CredentialEncryptionService`
Ubicación: `app/Services/Messaging/CredentialEncryptionService.php`

Usar Laravel's encryption:
```php
use Illuminate\Support\Facades\Crypt;

public function encrypt(array $credentials): string {
    return Crypt::encryptString(json_encode($credentials));
}

public function decrypt(string $encrypted): array {
    return json_decode(Crypt::decryptString($encrypted), true);
}
```

La clave de encriptación viene de `APP_KEY` en `.env`.

---

### 9. Seeders y Datos Iniciales

#### Seeder: `MessagingProvidersSeeder`
Ubicación: `database/seeders/MessagingProvidersSeeder.php`

Insertar providers por defecto:
```php
[
    [
        'name' => 'WhatsApp Lite',
        'slug' => 'whatsapp_lite',
        'config_schema' => json_encode([
            'api_url' => ['type' => 'text', 'required' => true, 'default' => config('whatsapp.api_url')],
            'api_key' => ['type' => 'password', 'required' => true],
            'timeout' => ['type' => 'number', 'required' => false, 'default' => 30]
        ]),
        'description' => 'Integración actual con API Node.js personalizada'
    ],
    [
        'name' => 'WhatsApp Business API',
        'slug' => 'whatsapp_meta',
        'config_schema' => json_encode([
            'phone_number_id' => ['type' => 'text', 'required' => true],
            'access_token' => ['type' => 'password', 'required' => true],
            'business_account_id' => ['type' => 'text', 'required' => true],
            'api_version' => ['type' => 'select', 'options' => ['v17.0', 'v18.0'], 'default' => 'v18.0']
        ]),
        'description' => 'API oficial de Meta para WhatsApp Business'
    ],
    [
        'name' => 'Twilio',
        'slug' => 'twilio',
        'config_schema' => json_encode([
            'account_sid' => ['type' => 'text', 'required' => true],
            'auth_token' => ['type' => 'password', 'required' => true],
            'phone_number' => ['type' => 'text', 'required' => true]
        ]),
        'description' => 'Plataforma de mensajería SMS y WhatsApp'
    ],
    [
        'name' => 'Google Firebase',
        'slug' => 'google_fcm',
        'config_schema' => json_encode([
            'project_id' => ['type' => 'text', 'required' => true],
            'private_key' => ['type' => 'textarea', 'required' => true],
            'client_email' => ['type' => 'email', 'required' => true],
            'api_key' => ['type' => 'password', 'required' => true]
        ]),
        'description' => 'Firebase Cloud Messaging para notificaciones push'
    ]
]
```

---

### 10. Rutas y Navegación

Agregar a `routes/admin.php`:
```php
Route::prefix('messaging')->as('messaging.')->group(function () {
    Route::get('/connections', \App\Livewire\Admin\Messaging\MessagingConnections::class)
        ->name('connections');
    Route::get('/channels', \App\Livewire\Admin\Messaging\ModuleNotificationChannels::class)
        ->name('channels');
});
```

Actualizar menú de administración para incluir:
- "Conexiones de Mensajería"
- "Canales por Módulo" (reemplaza "Notificaciones WhatsApp" actual)

---

### 11. Testing y Validación

#### Tests Unitarios:
- `tests/Unit/Messaging/WhatsAppLiteProviderTest.php`
- `tests/Unit/Messaging/WhatsAppMetaProviderTest.php`
- `tests/Unit/Messaging/TwilioProviderTest.php`
- `tests/Unit/Messaging/MessagingConnectionManagerTest.php`
- `tests/Unit/Messaging/UnifiedNotificationServiceTest.php`

#### Tests de Integración:
- Probar flujo completo: crear conexión -> probar -> configurar módulo -> enviar notificación
- Mock de APIs externas (Meta, Twilio, Google)
- Verificar migración automática desde configuración actual

---

## Plan de Implementación por Fases

### Fase 1: Foundation (Días 1-3)
1. Crear migraciones de nuevas tablas
2. Crear models: `MessagingProvider`, `MessagingConnection`, `ModuleNotificationChannel`
3. Crear interface `MessagingProviderInterface`
4. Implementar `CredentialEncryptionService`
5. Seeder de providers iniciales (incluyendo WhatsApp Lite)

### Fase 2: Providers (Días 4-8)
1. **Implementar `WhatsAppLiteProvider`** (prioridad alta - envuelve sistema actual)
   - Adaptar `WhatsAppService` existente para implementar `MessagingProviderInterface`
   - Crear migrador automático que lea credenciales de `empresas` y cree conexión inicial
2. Implementar `WhatsAppMetaProvider`
3. Implementar `TwilioProvider`
4. Implementar `GoogleFCMProvider`
5. Crear `MessagingProviderFactory`
6. Tests unitarios de cada provider

### Fase 3: Core Services (Días 9-11)
1. Implementar `MessagingConnectionManager`
2. Implementar `UnifiedNotificationService`
3. **Crear migrador automático de WhatsApp Lite**:
   - Leer configuraciones existentes de `empresas.whatsapp_api_key`, `whatsapp_status`, etc.
   - Crear automáticamente una conexión `WhatsAppLiteProvider` para cada empresa configurada
   - Marcar como conexión default para todos los módulos
4. Integrar con `WhatsAppNotificationGate`

### Fase 4: UI (Días 12-15)
1. Crear Livewire `MessagingConnections`
2. Crear Livewire `ModuleNotificationChannels`
3. Vistas Blade con formularios dinámicos
4. Componentes de prueba de conexión
5. Indicadores visuales de estado

### Fase 5: Migration (Días 16-19)
1. Adaptar `CitaNotificationService`
2. Adaptar `ProcessScheduledWhatsAppMessages` job
3. Adaptar `SendAccessWhatsAppNotificationJob`
4. Adaptar servicios de Pedidos, Enfermeros, etc.
5. Tests de integración

### Fase 6: Cleanup & Docs (Días 20-21)
1. Deprecar `WhatsAppService` antiguo (mantener como fallback)
2. Actualizar documentación
3. Guía de migración para desarrolladores
4. Tests end-to-end

---

## Consideraciones Técnicas

### Seguridad:
- Todas las credenciales encriptadas con `Crypt::encryptString()`
- Logs nunca deben mostrar credenciales completas (masking)
- Rotación de API keys soportada

### Performance:
- Cache de conexiones activas (TTL 5 minutos)
- Lazy loading de providers (solo instanciar cuando se necesita)
- Queue jobs para envíos asíncronos

### Escalabilidad:
- Fácil agregar nuevos providers (solo implementar interface)
- Soporte para múltiples conexiones del mismo tipo (ej: 2 números de WhatsApp)
- Fallback configurable entre providers

### Backwards Compatibility:
- **WhatsApp Lite mantiene 100% compatibilidad** con el sistema actual
- Migración automática de credenciales existentes al nuevo sistema
- El código existente sigue funcionando sin cambios durante la transición
- Feature flag para activar/desactivar nuevo sistema
- Migración gradual módulo por módulo

---

## Archivos Clave a Crear/Modificar

### Nuevos Archivos (~28 archivos):
- Models: 3
- Interfaces: 1
- Providers: 4 (WhatsApp Lite + 3 nuevos)
- Services: 4
- Livewire Components: 2
- Migrations: 3
- Seeders: 1
- Tests: 10-12

### Archivos a Modificar (~15 archivos):
- `app/Services/CitaNotificationService.php`
- `app/Services/PedidoService.php`
- `app/Jobs/ProcessScheduledWhatsAppMessages.php`
- `app/Jobs/SendAccessWhatsAppNotificationJob.php`
- `app/Livewire/Admin/Enfermeros/Edit.php`
- `app/Livewire/Admin/Enfermeros/Index.php`
- `app/Models/Cita.php`
- `routes/admin.php`
- Vistas de navegación/menú
- Configs existentes

---

## Beneficios de Esta Arquitectura

1. **Compatibilidad Total**: WhatsApp Lite preserva tu integración actual sin romper nada
2. **Flexibilidad**: Agregar nuevos proveedores (Meta, Twilio, Google) sin tocar código existente
3. **Testing**: Probar conexiones antes de usar en producción
4. **Granularidad**: Diferentes canales para diferentes módulos
5. **Resiliencia**: Fallback automático si un canal falla
6. **Multi-tenancy**: Cada empresa configura sus propias conexiones
7. **Extensibilidad**: Agregar nuevos providers fácilmente
8. **Auditabilidad**: Historial de qué canal se usó para cada notificación
9. **Migración Suave**: Las empresas pueden seguir usando WhatsApp Lite mientras evalúan otros proveedores
