# Sistema de Gestión Educativa Vargas

<p align="center">
  Sistema integral de gestión educativa desarrollado con Laravel
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

### 1. Gestión de Estudiantes
- ✅ Registro completo de estudiantes con información personal y académica
- ✅ Gestión de datos de representantes
- ✅ Control de asistencia y acceso al centro educativo
- ✅ Historial académico completo
- ✅ Generación de códigos QR para identificación
- ✅ Cálculo automático de edad
- ✅ Gestión de fotografías y documentos
- ✅ Control de estado activo/inactivo

### 2. Gestión de Matrículas
- ✅ Proceso de matriculación por períodos escolares
- ✅ Asignación a niveles educativos y programas
- ✅ Gestión de turnos (mañana, tarde, noche)
- ✅ Control de estados de matrícula
- ✅ Registro de observaciones y notas
- ✅ Cálculo automático de costos
- ✅ Generación de cronogramas de pagos

### 3. Sistema de Pagos
- ✅ Múltiples métodos de pago (efectivo, transferencia, tarjeta, pago móvil)
- ✅ Soporte para pagos mixtos
- ✅ Cronograma de pagos con control de cuotas
- ✅ Cálculo automático de recargos por morosidad
- ✅ Sistema de tasas de cambio (USD/EUR)
- ✅ Gestión de comprobantes digitales
- ✅ Control de series y numeración de documentos
- ✅ Generación de facturas, boletas y recibos

### 4. Gestión de Caja
- ✅ Apertura y cierre de caja
- ✅ Control de montos por método de pago
- ✅ Reportes detallados de operaciones
- ✅ Integración con exportación a Excel
- ✅ Notificaciones de cierre de caja por WhatsApp
- ✅ Control de usuarios responsables
- ✅ Registro de observaciones de apertura y cierre

### 5. Sistema de Control de Acceso
- ✅ Registro de entradas y salidas
- ✅ Control por usuario autorizado
- ✅ Múltiples métodos de acceso
- ✅ Registro de observaciones
- ✅ Logs de acceso de estudiantes
- ✅ Generación de reportes de asistencia

### 6. Sistema de Notificaciones
- ✅ Notificaciones en tiempo real
- ✅ Sistema de prioridades (baja, media, alta, urgente)
- ✅ Historial completo de notificaciones
- ✅ Marcado de leídos/no leídos
- ✅ Notificaciones por correo electrónico
- ✅ Notificaciones push en tiempo real

### 7. Integración con WhatsApp
- ✅ Envío de mensajes de texto
- ✅ Envío de documentos (Excel, PDF, Word)
- ✅ Programación de mensajes
- ✅ Reintento automático de mensajes fallidos
- ✅ Plantillas de mensajes personalizables
- ✅ Notificaciones de cierre de caja
- ✅ Control de estado de conexión
- ✅ Sistema de colas para envío masivo

### 8. Sistema de Mensajería Interna
- ✅ Mensajes entre usuarios del sistema
- ✅ Múltiples destinatarios
- ✅ Priorización de mensajes
- ✅ Adjuntar archivos
- ✅ Control de lectura y archivado
- ✅ Historial de conversaciones

### 9. Biblioteca Digital
- ✅ Gestión de archivos por categorías
- ✅ Control de visibilidad (público, privado, restringido)
- ✅ Registro de descargas
- ✅ Etiquetas y metadatos
- ✅ Usuarios autorizados por archivo
- ✅ Múltiples formatos de archivo
- ✅ Control de tamaño y tipo MIME

### 10. Gestión Académica
- ✅ Niveles educativos configurables
- ✅ Programas académicos
- ✅ Asignaturas/Materias
- ✅ Gestión de docentes
- ✅ Horarios de clases
- ✅ Sistema de evaluaciones
- ✅ Asignación de docentes a materias

### 11. Sistema de Auditoría
- ✅ Registro de todas las acciones
- ✅ Control de cambios en datos
- ✅ Seguimiento por usuario
- ✅ Registro de IPs y user agents
- ✅ Tags y metadatos personalizables
- ✅ Exportación de logs de auditoría

### 12. Gestión de Usuarios y Roles
- ✅ Sistema de autenticación robusto
- ✅ Verificación de correo electrónico
- ✅ Autenticación de dos factores (2FA)
- ✅ Gestión de roles y permisos (Spatie)
- ✅ Perfiles de usuario con avatar
- ✅ Control de sesiones activas
- ✅ Códigos de verificación temporales

### 13. Multitenancia
- ✅ Soporte multiempresa
- ✅ Soporte multisucursal
- ✅ Aislamiento de datos por empresa/sucursal
- ✅ Configuración independiente por tenant
- ✅ API Keys por empresa

### 14. Exportación de Datos
- ✅ Exportación dinámica de cualquier tabla
- ✅ Múltiples formatos (Excel, CSV, PDF)
- ✅ Filtros avanzados con múltiples condiciones
- ✅ Selección de columnas específicas
- ✅ Interfaz web amigable
- ✅ Comando Artisan para automatización
- ✅ Proceso asíncrono con barra de progreso

### 15. Configuración Regional
- ✅ Formato de fechas localizado
- ✅ Formato de monedas configurable
- ✅ Configuración por empresa/sucursal
- ✅ Soporte para múltiples monedas
- ✅ Formato de números y decimales

### 16. Sistema de Tareas Programadas (Jobs)
- ✅ Procesamiento de mensajes de WhatsApp
- ✅ Reintento automático de mensajes fallidos
- ✅ Envío de notificaciones automáticas
- ✅ Procesamiento de eventos programados
- ✅ Sistema de colas eficiente

### 17. Sistema de Reportes
- ✅ Reportes de estudiantes
- ✅ Reportes de matrículas
- ✅ Reportes de pagos
- ✅ Reportes de caja
- ✅ Reportes de asistencia
- ✅ Exportación a múltiples formatos

### 18. Gestión Médica y Citas
- ✅ Gestión completa de médicos con especialidades y subespecialidades
- ✅ Registro de pacientes con información médica y personal
- ✅ Sistema de citas médicas con calendario integrado
- ✅ Horarios de atención médica configurables
- ✅ Confirmaciones automáticas de citas vía WhatsApp
- ✅ Recordatorios automáticos (24h y 2h antes de la cita)
- ✅ Sistema de botones interactivos para confirmaciones
- ✅ Reintentos automáticos de mensajes fallidos
- ✅ Gestión de estados de citas (pendiente, confirmada, en curso, completada, cancelada, no asistió)
- ✅ Notificaciones médicas por WhatsApp y email
- ✅ Integración con servicios de mensajería médica
- ✅ Biblioteca digital para archivos médicos
- ✅ Auditoría completa de acciones médicas
- ✅ Reportes de citas y estadísticas médicas
- ✅ Control de acceso médico por roles y permisos
- ✅ Soporte multi-empresa para clínicas y consultorios
- ✅ Configuración regional para formatos médicos
- ✅ Exportación de datos médicos a múltiples formatos

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
WHATSAPP_API_URL=http://localhost:3001
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

Este proyecto es propiedad de Vargas Centro Educativo. Todos los derechos reservados.

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
