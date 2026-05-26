<p align="center">
  <img src="public/logo/auth.png" alt="Pupila Inc. - Oftalmología Integral" width="260">
</p>

<h1 align="center">Pupila Inc.</h1>

<p align="center">
  Plataforma integral para gestión clínica oftalmológica, agenda médica, consultas, pagos, caja, notificaciones y comunicación por WhatsApp.
</p>

<p align="center">
  <strong>Laravel 12</strong> · <strong>Livewire 3</strong> · <strong>MySQL 8</strong> · <strong>Docker</strong> · <strong>WhatsApp Service</strong>
</p>

---

## Visión General

Pupila Inc. es un sistema de gestión médica orientado a clínicas oftalmológicas. Centraliza la operación diaria de recepción, agenda, atención médica, administración, caja, pagos, reportes y comunicación con pacientes en una sola plataforma.

El proyecto está construido sobre Laravel y Livewire, con una arquitectura preparada para operar por empresa y sucursal, integrarse con servicios externos y mantener trazabilidad de las acciones importantes del sistema.

## Módulos Principales

| Área | Capacidades |
| --- | --- |
| Pacientes | Expediente, datos personales, contactos, tutores, fotografías, documentos e historial clínico. |
| Médicos | Especialidades, subespecialidades, horarios, tarifas, perfiles profesionales y disponibilidad. |
| Agenda | Calendario general, control de citas, reagendamiento, estados, conflictos de horario y vistas operativas. |
| Consultas | Flujo de atención, signos vitales, notas médicas, cuestionarios, diagnósticos y seguimiento. |
| Caja y pagos | Apertura/cierre de caja, pagos mixtos, comprobantes, cuotas, saldos, tasas de cambio y reportes. |
| WhatsApp | Confirmaciones, recordatorios, mensajes programados, reintentos, webhooks y estado de conexión. |
| Notificaciones | Alertas internas, prioridades, lectura, eventos de sistema y comunicación en tiempo real. |
| Usuarios y roles | Autenticación, permisos con Spatie, 2FA, perfiles, sesiones y auditoría. |
| Reportes | Exportaciones a Excel, CSV y PDF con filtros, columnas dinámicas y procesos asíncronos. |
| Multitenancia | Soporte multiempresa y multisucursal con configuración aislada por tenant. |

## Stack Técnico

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| UI dinámica | Livewire 3, Blade, Materialize Admin |
| Frontend build | Vite, Tailwind CSS |
| Base de datos | MySQL 8 |
| Autorización | Spatie Laravel Permission |
| Auditoría | Spatie Activity Log |
| Exportaciones | Laravel Excel, PhpSpreadsheet, DomPDF |
| Autenticación API | JWT |
| 2FA | Google2FA |
| Contenedores | Docker Compose |
| Mensajería externa | Servicio Node.js para WhatsApp |

## Requisitos

### Con Docker

- Docker
- Docker Compose
- Puertos disponibles: `8097`, `8098`, `8099`, `3318`

### Instalación manual

- PHP `8.2` o superior
- Composer
- MySQL `8.0` o compatible
- Node.js `18` o superior
- NPM
- Extensiones PHP comunes de Laravel: `pdo_mysql`, `mbstring`, `openssl`, `xml`, `ctype`, `json`, `bcmath`, `gd`, `zip`

## Puesta En Marcha Con Docker

Desde la raíz del entorno, un nivel arriba de este directorio:

```bash
cd /root/pupila-dev
docker compose up -d --build
```

Servicios disponibles:

| Servicio | URL local |
| --- | --- |
| Aplicación Laravel | `http://127.0.0.1:8097` |
| WhatsApp Service | `http://127.0.0.1:8098` |
| phpMyAdmin | `http://127.0.0.1:8099` |
| MySQL | `127.0.0.1:3318` |

Comandos útiles:

```bash
docker compose logs -f pupiladev_app
docker compose logs -f pupiladev_whatsapp
docker compose ps
docker compose exec pupiladev_app php artisan migrate
docker compose exec pupiladev_app php artisan optimize:clear
```

## Instalación Manual

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Para desarrollo con Vite:

```bash
npm run dev
```

Para ejecutar el servicio de WhatsApp:

```bash
cd resources/js/whatsapp
npm install
npm start
```

## Variables De Entorno

```env
APP_NAME="Nombre de la aplicación"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=database_user
DB_PASSWORD=database_password

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

WHATSAPP_API_URL=http://localhost:3001
WHATSAPP_API_KEY=change_me
WHATSAPP_WEBHOOK_URL=http://localhost/api/whatsapp/webhook
```

Usa `.env.example` como plantilla y configura los valores reales solo en tu entorno local.

## Estructura Del Proyecto

```text
app/
  Console/              Comandos Artisan y tareas operativas
  Domain/               Entidades, contratos y objetos de valor
  Http/                 Controladores, middleware, requests y recursos
  Livewire/             Componentes interactivos de la aplicación
  Models/               Modelos Eloquent
  Services/             Servicios de negocio e integraciones
  Traits/               Comportamientos reutilizables
database/
  migrations/           Estructura de base de datos
  seeders/              Datos iniciales y catálogos
public/
  js/                   Scripts publicados del sistema
  logo/                 Identidad visual de Pupila Inc.
resources/
  views/                Plantillas Blade
  js/whatsapp/          Servicio Node.js de WhatsApp
docs/                   Guías técnicas y documentación funcional
```

## Flujo De Desarrollo

```bash
php artisan optimize:clear
php artisan migrate
php artisan test
npm run build
```

Validaciones recomendadas antes de publicar cambios:

- Revisar logs de Laravel y del contenedor web.
- Validar que las migraciones corran en un entorno limpio.
- Ejecutar pruebas de backend cuando aplique.
- Compilar assets con Vite.
- Probar los flujos críticos: login, calendario, citas, pagos, caja y WhatsApp.

## Seguridad Y Operación

- Control de acceso basado en roles y permisos.
- Autenticación de dos factores.
- Protección CSRF en formularios web.
- Auditoría de acciones relevantes.
- Validación de datos en servidor.
- Separación por empresa y sucursal.
- API keys para integraciones externas.
- Manejo de colas para procesos asíncronos.

## Documentación Relacionada

- [Sistema de confirmación de citas](docs/CITA_CONFIRMATION_SYSTEM.md)
- [Sistema de gestión de caja](docs/SISTEMA_GESTION_CAJA.md)
- [Sistema de reintentos de WhatsApp](docs/WHATSAPP_RETRY_SYSTEM.md)
- [Configuración regional](docs/REGIONAL_CONFIGURATION_GUIDE.md)
- [Guía de métodos de pago](docs/GUIA_METODOS_PAGO.md)
- [Proceso de consulta](docs/PROCESO_CONSULTA_SYSTEM.md)
- [Signos vitales y enfermería](docs/SIGNOS_VITALES_ENFERMERIA.md)
- [Inventario de mensajes de WhatsApp](WHATSAPP_MESSAGE_INVENTORY.md)
- [Registro de cambios](CHANGELOG.md)

## Mantenimiento

Limpieza de cachés:

```bash
php artisan optimize:clear
```

Reconstrucción de assets:

```bash
npm run build
```

Ejecución de colas:

```bash
php artisan queue:work
```

Revisión de logs:

```bash
tail -f storage/logs/laravel.log
```

## Licencia Y Propiedad

Este proyecto es propiedad de TheizerDev. Todos los derechos reservados.

Laravel es software open source distribuido bajo licencia MIT.
