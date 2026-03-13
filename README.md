# Sistema de Gestión Médica Vargas

<p align="center">
  Sistema integral de gestión médica desarrollado con Laravel
</p>

## Índice

- [Descripción](#descripción)
- [Características Principales](#características-principales)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Arquitectura](#arquitectura)
- [Documentación Adicional](#documentación-adicional)

## Descripción

El Sistema de Gestión Educativa Vargas es una plataforma integral diseñada para administrar todos los aspectos de una institución educativa con servicios médicos integrados. Desarrollado con Laravel, ofrece una solución robusta y escalable que incluye gestión de estudiantes, matrículas, pagos, control de acceso, comunicaciones, atención médica y gestión de citas médicas.

## Características Principales

### 1. Gestión de Pacientes
- ✅ Registro completo de pacientes con información médica y personal
- ✅ Gestión de datos de tutores para pacientes menores de edad
- ✅ Historial médico completo
- ✅ Cálculo automático de edad
- ✅ Gestión de fotografías y documentos médicos
- ✅ Control de estado activo/inactivo
- ✅ Información de contacto y emergencia

### 2. Gestión de Médicos
- ✅ Registro completo de médicos con especialidades y subespecialidades
- ✅ Gestión de licencias médicas y años de experiencia
- ✅ Asignación de especialidades médicas
- ✅ Control de horarios de atención
- ✅ Perfiles profesionales con tarifas de consulta
- ✅ Gestión de niveles de experiencia
- ✅ Control de estado activo/inactivo

### 3. Sistema de Citas Médicas
- ✅ Calendario integrado para gestión de citas
- ✅ Estados de citas (pendiente, confirmada, en curso, completada, cancelada, no asistió)
- ✅ Confirmaciones automáticas vía WhatsApp
- ✅ Recordatorios automáticos (24h y 2h antes)
- ✅ Sistema de botones interactivos para confirmaciones
- ✅ Reintentos automáticos de mensajes fallidos
- ✅ Gestión de tipos de consulta
- ✅ Control de conflictos de horarios

### 4. Sistema de Pagos
- ✅ Múltiples métodos de pago (efectivo, transferencia, tarjeta, pago móvil)
- ✅ Soporte para pagos mixtos
- ✅ Cronograma de pagos con control de cuotas
- ✅ Cálculo automático de recargos por morosidad
- ✅ Sistema de tasas de cambio (USD/EUR)
- ✅ Gestión de comprobantes digitales
- ✅ Control de series y numeración de documentos
- ✅ Generación de facturas, boletas y recibos

### 5. Gestión de Caja
- ✅ Apertura y cierre de caja
- ✅ Control de montos por método de pago
- ✅ Reportes detallados de operaciones
- ✅ Integración con exportación a Excel
- ✅ Notificaciones de cierre de caja por WhatsApp
- ✅ Control de usuarios responsables
- ✅ Registro de observaciones de apertura y cierre

### 6. Sistema de Control de Acceso
- ✅ Registro de entradas y salidas de pacientes
- ✅ Control por usuario autorizado
- ✅ Múltiples métodos de acceso
- ✅ Registro de observaciones
- ✅ Logs de acceso de pacientes
- ✅ Generación de reportes de asistencia

### 7. Sistema de Notificaciones
- ✅ Notificaciones en tiempo real
- ✅ Sistema de prioridades (baja, media, alta, urgente)
- ✅ Historial completo de notificaciones
- ✅ Marcado de leídos/no leídos
- ✅ Notificaciones por correo electrónico
- ✅ Notificaciones push en tiempo real

### 8. Integración con WhatsApp
- ✅ Envío de mensajes de texto
- ✅ Envío de documentos (Excel, PDF, Word)
- ✅ Programación de mensajes
- ✅ Reintento automático de mensajes fallidos
- ✅ Plantillas de mensajes personalizables
- ✅ Notificaciones de cierre de caja
- ✅ Control de estado de conexión
- ✅ Sistema de colas para envío masivo

### 9. Sistema de Mensajería Interna
- ✅ Mensajes entre usuarios del sistema
- ✅ Múltiples destinatarios
- ✅ Priorización de mensajes
- ✅ Adjuntar archivos
- ✅ Control de lectura y archivado
- ✅ Historial de conversaciones

### 10. Biblioteca Digital
- ✅ Gestión de archivos médicos por categorías
- ✅ Control de visibilidad (público, privado, restringido)
- ✅ Registro de descargas
- ✅ Etiquetas y metadatos médicos
- ✅ Usuarios autorizados por archivo
- ✅ Múltiples formatos de archivo
- ✅ Control de tamaño y tipo MIME

### 11. Gestión de Especialidades Médicas
- ✅ Especialidades médicas configurables
- ✅ Subespecialidades médicas
- ✅ Asignación de médicos a especialidades
- ✅ Tarifas de consulta por especialidad
- ✅ Niveles de experiencia requeridos
- ✅ Horarios de atención por especialidad

### 12. Sistema de Auditoría
- ✅ Registro de todas las acciones médicas
- ✅ Control de cambios en datos médicos
- ✅ Seguimiento por usuario médico
- ✅ Registro de IPs y user agents
- ✅ Tags y metadatos personalizables
- ✅ Exportación de logs de auditoría médica

### 13. Gestión de Usuarios y Roles
- ✅ Sistema de autenticación robusto
- ✅ Verificación de correo electrónico
- ✅ Autenticación de dos factores (2FA)
- ✅ Gestión de roles y permisos médicos (Spatie)
- ✅ Perfiles de usuario con avatar
- ✅ Control de sesiones activas
- ✅ Códigos de verificación temporales

### 14. Multitenancia
- ✅ Soporte multiempresa para clínicas
- ✅ Soporte multisucursal
- ✅ Aislamiento de datos por empresa/sucursal
- ✅ Configuración independiente por tenant
- ✅ API Keys por empresa

### 15. Exportación de Datos
- ✅ Exportación dinámica de cualquier tabla médica
- ✅ Múltiples formatos (Excel, CSV, PDF)
- ✅ Filtros avanzados con múltiples condiciones
- ✅ Selección de columnas específicas
- ✅ Interfaz web amigable
- ✅ Comando Artisan para automatización
- ✅ Proceso asíncrono con barra de progreso

### 16. Configuración Regional
- ✅ Formato de fechas localizado
- ✅ Formato de monedas configurable
- ✅ Configuración por empresa/sucursal
- ✅ Soporte para múltiples monedas
- ✅ Formato de números y decimales

### 17. Sistema de Tareas Programadas (Jobs)
- ✅ Procesamiento de mensajes de WhatsApp
- ✅ Reintento automático de mensajes fallidos
- ✅ Envío de notificaciones automáticas
- ✅ Procesamiento de eventos programados
- ✅ Sistema de colas eficiente

### 18. Sistema de Reportes
- ✅ Reportes de pacientes
- ✅ Reportes de citas médicas
- ✅ Reportes de pagos
- ✅ Reportes de caja
- ✅ Reportes de asistencia médica
- ✅ Exportación a múltiples formatos

## Requisitos del Sistema

- PHP >= 8.1
- Composer
- MySQL >= 5.7 o PostgreSQL >= 10
- Node.js >= 16 (para servicio de WhatsApp)
- Redis (opcional, para colas y caché)
- Extensión de PHP: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Instalación

1. **Clonar el repositorio**
```bash
git clone [url-del-repositorio]
cd vargas
```

2. **Instalar dependencias de PHP**
```bash
composer install
```

3. **Instalar dependencias de Node.js (opcional, para WhatsApp)**
```bash
cd resources/js/whatsapp
npm install
```

4. **Configurar el archivo .env**
```bash
cp .env.example .env
```

5. **Generar clave de aplicación**
```bash
php artisan key:generate
```

6. **Ejecutar migraciones**
```bash
php artisan migrate
```

7. **Iniciar el servidor de desarrollo**
```bash
php artisan serve
```

8. **Iniciar el servicio de WhatsApp (opcional)**
```bash
cd resources/js/whatsapp
npm start
```

## Configuración

### Variables de Entorno Principales

```env
# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vargas
DB_USERNAME=your_username
DB_PASSWORD=your_password

# WhatsApp
WHATSAPP_API_URL=http://82.165.213.124:8092
WHATSAPP_API_KEY=test-api-key-vargas-centro

# Correo Electrónico
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Colas
QUEUE_CONNECTION=database
```

## Arquitectura

El sistema sigue una arquitectura modular con los siguientes patrones de diseño:

- **Repository Pattern**: Para abstracción del acceso a datos
- **Service Layer**: Para lógica de negocio
- **Domain-Driven Design**: Para organización del dominio
- **Event-Driven Architecture**: Para manejo de eventos y notificaciones

### Estructura del Proyecto

```
app/
├── Application/      # Lógica de aplicación
│   ├── DTOs/        # Data Transfer Objects
│   ├── Events/      # Eventos del dominio
│   └── Services/    # Servicios de aplicación
├── Domain/          # Dominio del negocio
│   ├── Contracts/   # Contratos e interfaces
│   ├── Entities/    # Entidades del dominio
│   └── ValueObjects/# Objetos de valor
├── Infrastructure/   # Infraestructura
│   └── Repositories/# Implementaciones de repositorios
├── Http/            # Capa HTTP
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/          # Modelos Eloquent
├── Services/        # Servicios del sistema
└── Traits/          # Traits reutilizables
```

## Documentación Adicional

- [Documentación de Exportación de Base de Datos](EXPORTADOR_BASE_DATOS.md)
- [Documentación de Integración WhatsApp](WHATSAPP_DOCUMENT_IMPLEMENTATION.md)
- [Documentación del Sistema de Confirmación de Citas](docs/CITA_CONFIRMATION_SYSTEM.md)
- [Registro de Cambios](CHANGELOG.md)

## Seguridad

El sistema implementa múltiples capas de seguridad:

- ✅ Autenticación de dos factores (2FA)
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Registro exhaustivo de auditoría
- ✅ Validación de datos en servidor y cliente
- ✅ Protección contra CSRF
- ✅ Sanitización de entradas
- ✅ Rate limiting en APIs
- ✅ Encriptación de datos sensibles

## Soporte

Para soporte técnico, consulte la documentación oficial de Laravel o contacte al equipo de desarrollo.

## Licencia

Este proyecto es propiedad de TheizerDev. Todos los derechos reservados.

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
