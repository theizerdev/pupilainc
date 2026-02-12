<?php

use App\Http\Controllers\Admin\PacienteCarnetMenorController;
use App\Livewire\Admin\ActiveSessions;
use App\Livewire\Admin\ConceptosPago\Create as ConceptosPagoCreate;
use App\Livewire\Admin\ConceptosPago\Edit as ConceptosPagoEdit;
use App\Livewire\Admin\ConceptosPago\Index as ConceptosPagoIndex;
use App\Livewire\Admin\Empresas\Create as EmpresasCreate;
use App\Livewire\Admin\Empresas\Edit as EmpresasEdit;
use App\Livewire\Admin\Empresas\Index as EmpresasIndex;
use App\Livewire\Admin\Permissions\Create as PermissionsCreate;
use App\Livewire\Admin\Permissions\Edit as PermissionsEdit;
use App\Livewire\Admin\Permissions\Index as PermissionsIndex;
use App\Livewire\Admin\Roles\Create as RolesCreate;
use App\Livewire\Admin\Roles\Edit as RolesEdit;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Roles\Show as RolesShow;
use App\Livewire\Admin\Sucursales\Create as SucursalesCreate;
use App\Livewire\Admin\Sucursales\Edit as SucursalesEdit;
use App\Livewire\Admin\Sucursales\Index as SucursalesIndex;
use App\Livewire\Admin\Sucursales\Show as SucursalesShow;
use App\Livewire\Admin\Users\Create as UsersCreate;
use App\Livewire\Admin\Users\Edit as UsersEdit;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

// Recepción
Route::prefix('recepcion')->name('recepcion.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Recepcion\Dashboard::class)->name('dashboard')->middleware('checkAdminPermission:access recepcion dashboard');
    Route::get('/control-consultorios', \App\Livewire\Admin\Recepcion\ControlConsultorios::class)->name('control-consultorios')->middleware('checkAdminPermission:manage consultorios');
});

// Consulta - Apertura
Route::prefix('consulta')->name('consulta.')->group(function () {
    Route::get('/apertura', \App\Livewire\Admin\Consulta\Apertura::class)->name('apertura')->middleware('checkAdminPermission:access consulta apertura');
});

// Consultorios
Route::middleware(['checkAdminPermission:access consultorios'])->group(function () {
    Route::get('/consultorios', \App\Livewire\Admin\Consultorios\Index::class)->name('consultorios.index');
    Route::get('/consultorios/crear', \App\Livewire\Admin\Consultorios\Create::class)->name('consultorios.create');
    Route::get('/consultorios/{consultorio}/editar', \App\Livewire\Admin\Consultorios\Edit::class)->name('consultorios.edit');
});

// Empresas
Route::middleware(['checkAdminPermission:access empresas'])->group(function () {
    Route::get('/empresas', EmpresasIndex::class)->name('empresas.index');
    Route::get('/empresas/crear', EmpresasCreate::class)->name('empresas.create');
    Route::get('/empresas/{empresa}/editar', EmpresasEdit::class)->name('empresas.edit');
});

// Especialidades
Route::middleware(['checkAdminPermission:access especialidades'])->group(function () {
    Route::get('/especialidades', \App\Livewire\Admin\Especialidades\Index::class)->name('especialidades.index');
    Route::get('/especialidades/crear', \App\Livewire\Admin\Especialidades\Create::class)->name('especialidades.create');
    Route::get('/especialidades/{especialidad}/editar', \App\Livewire\Admin\Especialidades\Edit::class)->name('especialidades.edit');
    Route::get('/especialidades/{especialidad}', \App\Livewire\Admin\Especialidades\Show::class)->name('especialidades.show');
});

// Tipos de Consulta
Route::middleware(['checkAdminPermission:access tipo-consultas'])->group(function () {
    Route::get('/tipo-consultas', \App\Livewire\Admin\TipoConsultas\Index::class)->name('tipo-consultas.index');
    Route::get('/tipo-consultas/crear', \App\Livewire\Admin\TipoConsultas\Create::class)->name('tipo-consultas.create');
    Route::get('/tipo-consultas/{tipoConsulta}/editar', \App\Livewire\Admin\TipoConsultas\Edit::class)->name('tipo-consultas.edit');
    Route::get('/tipo-consultas/{tipoConsulta}', \App\Livewire\Admin\TipoConsultas\Show::class)->name('tipo-consultas.show');
});

// Subespecialidades
Route::middleware(['checkAdminPermission:access subespecialidades'])->group(function () {
    Route::get('/subespecialidades', \App\Livewire\Admin\Subespecialidades\Index::class)->name('subespecialidades.index');
    Route::get('/subespecialidades/crear', \App\Livewire\Admin\Subespecialidades\Create::class)->name('subespecialidades.create');
    Route::get('/subespecialidades/{subespecialidad}/editar', \App\Livewire\Admin\Subespecialidades\Edit::class)->name('subespecialidades.edit');
    Route::get('/subespecialidades/{subespecialidad}', \App\Livewire\Admin\Subespecialidades\Show::class)->name('subespecialidades.show');
});

// Médicos
Route::middleware(['checkAdminPermission:access medicos'])->group(function () {
    Route::get('/medicos', \App\Livewire\Admin\Medicos\Index::class)->name('medicos.index');
    Route::get('/medicos/crear', \App\Livewire\Admin\Medicos\Create::class)->name('medicos.create');
    Route::get('/medicos/{medico}/editar', \App\Livewire\Admin\Medicos\Edit::class)->name('medicos.edit');
});

// Pacientes
Route::middleware(['checkAdminPermission:access pacientes'])->group(function () {
    Route::get('/pacientes', \App\Livewire\Admin\Pacientes\Index::class)->name('pacientes.index');
    Route::get('/pacientes/crear', \App\Livewire\Admin\Pacientes\Wizard::class)->name('pacientes.create');
    Route::get('/pacientes/{pacienteId}/editar', \App\Livewire\Admin\Pacientes\Wizard::class)->name('pacientes.edit');
    Route::get('/pacientes/{paciente}/carnet-menor', [PacienteCarnetMenorController::class, 'show'])->name('pacientes.carnet-menor');
    Route::get('/pacientes/{paciente}/carnet-menor.png', [PacienteCarnetMenorController::class, 'png'])->name('pacientes.carnet-menor.png');
    Route::get('/pacientes/{paciente}/carnet-menor.pdf', [PacienteCarnetMenorController::class, 'pdf'])->name('pacientes.carnet-menor.pdf');
    Route::post('/pacientes/{paciente}/carnet-menor/whatsapp', [PacienteCarnetMenorController::class, 'sendWhatsApp'])->name('pacientes.carnet-menor.whatsapp');
    Route::get('/pacientes/{paciente}/preconsulta', \App\Livewire\Admin\Pacientes\Preconsulta::class)->name('pacientes.preconsulta');
});

// Países
Route::middleware(['checkAdminPermission:access paises'])->group(function () {
    Route::get('/paises', \App\Livewire\Admin\Paises\PaisIndex::class)->name('paises.index');
    Route::get('/paises/crear', \App\Livewire\Admin\Paises\Create::class)->name('paises.create');
    Route::get('/paises/{pais}/editar', \App\Livewire\Admin\Paises\Edit::class)->name('paises.edit');
});

// Sucursales
Route::middleware(['checkAdminPermission:access sucursales'])->group(function () {
    Route::get('/sucursales', SucursalesIndex::class)->name('sucursales.index');
    Route::get('/sucursales/crear', SucursalesCreate::class)->name('sucursales.create');
    Route::get('/sucursales/{sucursal}/editar', SucursalesEdit::class)->name('sucursales.edit');
    Route::get('/sucursales/{sucursal}', SucursalesShow::class)->name('sucursales.show');
});

// Usuarios
Route::middleware(['checkAdminPermission:access users'])->group(function () {
    Route::get('/usuarios', UsersIndex::class)->name('users.index');
    Route::get('/usuarios/crear', UsersCreate::class)->name('users.create');
    Route::get('/usuarios/{user}/editar', UsersEdit::class)->name('users.edit');
});

// Perfil de usuario (Acceso para todos los autenticados, sin permiso específico requerido)
Route::prefix('profile')->group(function () {
    Route::get('/', \App\Livewire\Admin\Users\Profile\Index::class)->name('users.profile');
    Route::get('/{user_id}/password', \App\Livewire\Admin\Users\Profile\ChangePassword::class)->name('users.password');
    Route::get('/{user_id}/history', \App\Livewire\Admin\Users\Profile\HistoryUser::class)->name('users.history');
});

// Roles
Route::middleware(['checkAdminPermission:access roles'])->group(function () {
    Route::get('/roles', RolesIndex::class)->name('roles.index');
    Route::get('/roles/crear', RolesCreate::class)->name('roles.create');
    Route::get('/roles/{role}/editar', RolesEdit::class)->name('roles.edit');
    Route::get('/roles/{role}', RolesShow::class)->name('roles.show');
});

// Permisos
Route::middleware(['checkAdminPermission:access permissions'])->group(function () {
    Route::get('/permisos', PermissionsIndex::class)->name('permissions.index');
    Route::get('/permisos/crear', PermissionsCreate::class)->name('permissions.create');
    Route::get('/permisos/{permission}/editar', PermissionsEdit::class)->name('permissions.edit');
});

// Sesiones activas
Route::get('/active-sessions', ActiveSessions::class)->name('active-sessions.index')->middleware('checkAdminPermission:view active sessions');

// Monitoreo
Route::prefix('monitoreo')->as('monitoreo.')->group(function () {
    Route::get('/servidor', \App\Livewire\Admin\Monitoreo\Servidor::class)->name('servidor')->middleware('checkAdminPermission:view monitoreo servidor');
    Route::get('/base-datos', \App\Livewire\Admin\Monitoreo\BaseDatos::class)->name('base-datos')->middleware('checkAdminPermission:view monitoreo base-datos');
    Route::get('/estudiantes', \App\Livewire\Admin\Monitoreo\Estudiantes::class)->name('estudiantes')->middleware('checkAdminPermission:view monitoreo estudiantes');
    Route::get('/accesos', \App\Livewire\Admin\Monitoreo\Accesos::class)->name('accesos')->middleware('checkAdminPermission:view monitoreo accesos');
});

// Tasas de Cambio
Route::get('/tasas-cambio', \App\Livewire\Admin\ExchangeRates::class)->name('exchange-rates')->middleware('checkAdminPermission:view exchange-rates');

// Series de Documentos
Route::middleware(['checkAdminPermission:access series'])->group(function () {
    Route::get('/series', \App\Livewire\Admin\Series\Index::class)->name('series.index');
    Route::get('/series/crear', \App\Livewire\Admin\Series\Create::class)->name('series.create');
    Route::get('/series/{serie}/editar', \App\Livewire\Admin\Series\Edit::class)->name('series.edit');
});

// Pagos
Route::middleware(['checkAdminPermission:access pagos'])->group(function () {
    Route::get('/pagos', \App\Livewire\Admin\Pagos\Index::class)->name('pagos.index');
    Route::get('/pagos/crear', \App\Livewire\Admin\Pagos\Create::class)->name('pagos.create');
    Route::get('/pagos/{pago}/editar', \App\Livewire\Admin\Pagos\Edit::class)->name('pagos.edit');
    Route::get('/pagos/{pago}', \App\Livewire\Admin\Pagos\Show::class)->name('pagos.show');
    Route::get('/pagos/{pago}/print', [\App\Livewire\Admin\Pagos\Index::class, 'downloadReceipt'])->name('pagos.print');
    Route::get('/pagos/comprobante/{comprobante}', \App\Livewire\Admin\Pagos\Comprobantes::class)->name('pagos.comprobante');
});

// Registro de Actividad
Route::get('/activity-log', \App\Livewire\Admin\ActivityLog::class)->name('activity-log')->middleware('checkAdminPermission:access activity log');

// Cajas
Route::middleware(['checkAdminPermission:access cajas'])->group(function () {
    Route::get('/cajas', \App\Livewire\Admin\Cajas\Index::class)->name('cajas.index');
    Route::get('/cajas/crear', \App\Livewire\Admin\Cajas\Create::class)->name('cajas.create');
    Route::get('/cajas/{caja}', \App\Livewire\Admin\Cajas\Show::class)->name('cajas.show');
    Route::get('/cajas/{caja}/export', [\App\Http\Controllers\Admin\CajaExportController::class, 'export'])->name('cajas.export');
});

// Reglas de Morosidad
Route::get('/reglas-morosidad', \App\Livewire\Admin\LatePaymentRules\Index::class)->name('late-payment-rules.index')->middleware('checkAdminPermission:access reglas mora');

// Notificaciones
Route::get('/notifications', \App\Livewire\Admin\Notifications\Index::class)->name('notifications.index')->middleware('checkAdminPermission:access notifications');

// WhatsApp - Nuevas rutas separadas
Route::prefix('whatsapp')->as('whatsapp.')->middleware(['checkAdminPermission:access whatsapp'])->group(function () {
    // Dashboard principal
    Route::get('/dashboard', \App\Livewire\Admin\Whatsapp\WhatsAppDashboard::class)->name('dashboard');

    // Gestión de conexión
    Route::get('/connection', \App\Livewire\Admin\Whatsapp\WhatsAppConnection::class)->name('connection');

    // Enviar mensajes
    Route::get('/send-messages', \App\Livewire\Admin\Whatsapp\WhatsAppSendMessages::class)->name('send-messages');

    // Plantillas
    Route::get('/templates', \App\Livewire\Admin\Whatsapp\WhatsAppTemplates::class)->name('templates.index');

    // Historial
    Route::get('/history', \App\Livewire\Admin\Whatsapp\WhatsAppHistory::class)->name('history');

    // Mensajes programados
    Route::get('/scheduled-messages', \App\Livewire\Admin\Whatsapp\WhatsAppScheduledMessages::class)->name('scheduled-messages');

    // Mantener rutas antiguas para compatibilidad temporal
    Route::get('/', \App\Livewire\Admin\Whatsapp\Index::class)->name('index');

    // Estadísticas
    Route::get('/statistics', \App\Livewire\Admin\Whatsapp\WhatsAppStatistics::class)->name('statistics');
});

// Exportador de Base de Datos
Route::get('/exportar-base-datos', \App\Livewire\Admin\DatabaseExport::class)->name('database-export')->middleware('checkAdminPermission:access database export');

// WhatsApp (Ruta legacy fuera del grupo)
Route::get('/whatsapp', \App\Livewire\Admin\Whatsapp\Index::class)->name('whatsapp.index')->middleware('checkAdminPermission:access whatsapp');

// Conceptos de Pago
Route::middleware(['checkAdminPermission:access conceptos pago'])->group(function () {
    Route::get('/conceptos-pago', ConceptosPagoIndex::class)->name('conceptos-pago.index');
    Route::get('/conceptos-pago/crear', ConceptosPagoCreate::class)->name('conceptos-pago.create');
    Route::get('/conceptos-pago/{concepto}/editar', ConceptosPagoEdit::class)->name('conceptos-pago.edit');
});

// Citas Médicas
Route::middleware(['checkAdminPermission:access citas'])->group(function () {
    Route::get('/citas', \App\Livewire\Admin\Citas\Index::class)->name('citas.index');
    Route::get('/citas/analytics', \App\Livewire\Admin\Citas\Analytics::class)->name('citas.analytics');
    Route::get('/citas/recordatorios', \App\Livewire\Admin\Citas\Recordatorios::class)->name('citas.recordatorios');
    Route::get('/citas/reagendamiento', \App\Livewire\Admin\Citas\Reagendamiento::class)->name('citas.reagendamiento');
    // Confirmaciones de Citas
    Route::get('/citas/confirmaciones', \App\Livewire\Admin\CitaConfirmationStats::class)->name('citas.confirmaciones');
});
