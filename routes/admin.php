<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Admin\Empresas\Index as EmpresasIndex;
use App\Livewire\Admin\Empresas\Create as EmpresasCreate;
use App\Livewire\Admin\Empresas\Edit as EmpresasEdit;
use App\Livewire\Admin\Sucursales\Index as SucursalesIndex;
use App\Livewire\Admin\Sucursales\Create as SucursalesCreate;
use App\Livewire\Admin\Sucursales\Edit as SucursalesEdit;
use App\Livewire\Admin\Sucursales\Show as SucursalesShow;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Users\Create as UsersCreate;
use App\Livewire\Admin\Users\Edit as UsersEdit;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Roles\Create as RolesCreate;
use App\Livewire\Admin\Roles\Edit as RolesEdit;
use App\Livewire\Admin\Roles\Show as RolesShow;
use App\Livewire\Admin\Permissions\Index as PermissionsIndex;
use App\Livewire\Admin\Permissions\Create as PermissionsCreate;
use App\Livewire\Admin\Permissions\Edit as PermissionsEdit;
use App\Livewire\Admin\ActiveSessions;
use App\Livewire\Admin\ConceptosPago\Index as ConceptosPagoIndex;
use App\Livewire\Admin\ConceptosPago\Create as ConceptosPagoCreate;
use App\Livewire\Admin\ConceptosPago\Edit as ConceptosPagoEdit;



// Empresas
Route::get('/empresas', EmpresasIndex::class)->name('empresas.index');
Route::get('/empresas/crear', EmpresasCreate::class)->name('empresas.create');
Route::get('/empresas/{empresa}/editar', EmpresasEdit::class)->name('empresas.edit');

// Especialidades
Route::get('/especialidades', \App\Livewire\Admin\Especialidades\Index::class)->name('especialidades.index');
Route::get('/especialidades/crear', \App\Livewire\Admin\Especialidades\Create::class)->name('especialidades.create');
Route::get('/especialidades/{especialidad}/editar', \App\Livewire\Admin\Especialidades\Edit::class)->name('especialidades.edit');
Route::get('/especialidades/{especialidad}', \App\Livewire\Admin\Especialidades\Show::class)->name('especialidades.show');

// Subespecialidades
Route::get('/subespecialidades', \App\Livewire\Admin\Subespecialidades\Index::class)->name('subespecialidades.index');
Route::get('/subespecialidades/crear', \App\Livewire\Admin\Subespecialidades\Create::class)->name('subespecialidades.create');
Route::get('/subespecialidades/{subespecialidad}/editar', \App\Livewire\Admin\Subespecialidades\Edit::class)->name('subespecialidades.edit');
Route::get('/subespecialidades/{subespecialidad}', \App\Livewire\Admin\Subespecialidades\Show::class)->name('subespecialidades.show');

// Países
Route::get('/paises', \App\Livewire\Admin\Paises\PaisIndex::class)->name('paises.index');
Route::get('/paises/crear', \App\Livewire\Admin\Paises\Create::class)->name('paises.create');
Route::get('/paises/{pais}/editar', \App\Livewire\Admin\Paises\Edit::class)->name('paises.edit');

// Sucursales
Route::get('/sucursales', SucursalesIndex::class)->name('sucursales.index');
Route::get('/sucursales/crear', SucursalesCreate::class)->name('sucursales.create');
Route::get('/sucursales/{sucursal}/editar', SucursalesEdit::class)->name('sucursales.edit');
Route::get('/sucursales/{sucursal}', SucursalesShow::class)->name('sucursales.show');

// Usuarios
Route::get('/usuarios', UsersIndex::class)->name('users.index');
Route::get('/usuarios/crear', UsersCreate::class)->name('users.create');
Route::get('/usuarios/{user}/editar', UsersEdit::class)->name('users.edit');
 // Perfil de usuario
Route::prefix('profile')->group(function () {
    Route::get('/', \App\Livewire\Admin\Users\Profile\Index::class)->name('users.profile');
    Route::get('/{user_id}/password', \App\Livewire\Admin\Users\Profile\ChangePassword::class)->name('users.password');
    Route::get('/{user_id}/history', \App\Livewire\Admin\Users\Profile\HistoryUser::class)->name('users.history');
});

// Roles
Route::get('/roles', RolesIndex::class)->name('roles.index');
Route::get('/roles/crear', RolesCreate::class)->name('roles.create');
Route::get('/roles/{role}/editar', RolesEdit::class)->name('roles.edit');
Route::get('/roles/{role}', RolesShow::class)->name('roles.show');

// Permisos
Route::get('/permisos', PermissionsIndex::class)->name('permissions.index');
Route::get('/permisos/crear', PermissionsCreate::class)->name('permissions.create');
Route::get('/permisos/{permission}/editar', PermissionsEdit::class)->name('permissions.edit');


// Sesiones activas
Route::get('/active-sessions', ActiveSessions::class)->name('active-sessions.index');

// Monitoreo
Route::prefix('monitoreo')->as('monitoreo.')->group(function () {
    Route::get('/servidor', \App\Livewire\Admin\Monitoreo\Servidor::class)->name('servidor');
    Route::get('/base-datos', \App\Livewire\Admin\Monitoreo\BaseDatos::class)->name('base-datos');
    Route::get('/estudiantes', \App\Livewire\Admin\Monitoreo\Estudiantes::class)->name('estudiantes');
    Route::get('/accesos', \App\Livewire\Admin\Monitoreo\Accesos::class)->name('accesos');
});

// Tasas de Cambio
Route::get('/tasas-cambio', \App\Livewire\Admin\ExchangeRates::class)->name('exchange-rates');



// Series de Documentos
Route::get('/series', \App\Livewire\Admin\Series\Index::class)->name('series.index');
Route::get('/series/crear', \App\Livewire\Admin\Series\Create::class)->name('series.create');
Route::get('/series/{serie}/editar', \App\Livewire\Admin\Series\Edit::class)->name('series.edit');


// Pagos
Route::get('/pagos', \App\Livewire\Admin\Pagos\Index::class)->name('pagos.index');
Route::get('/pagos/crear', \App\Livewire\Admin\Pagos\Create::class)->name('pagos.create');
Route::get('/pagos/{pago}/editar', \App\Livewire\Admin\Pagos\Edit::class)->name('pagos.edit');
Route::get('/pagos/{pago}', \App\Livewire\Admin\Pagos\Show::class)->name('pagos.show');
Route::get('/pagos/{pago}/print', [\App\Livewire\Admin\Pagos\Index::class, 'downloadReceipt'])->name('pagos.print');
Route::get('/pagos/comprobante/{comprobante}', \App\Livewire\Admin\Pagos\Comprobantes::class)->name('pagos.comprobante');



// Registro de Actividad
Route::get('/activity-log', \App\Livewire\Admin\ActivityLog::class)->name('activity-log');


// Cajas
Route::get('/cajas', \App\Livewire\Admin\Cajas\Index::class)->name('cajas.index');
Route::get('/cajas/crear', \App\Livewire\Admin\Cajas\Create::class)->name('cajas.create');
Route::get('/cajas/{caja}', \App\Livewire\Admin\Cajas\Show::class)->name('cajas.show');
Route::get('/cajas/{caja}/export', [\App\Http\Controllers\Admin\CajaExportController::class, 'export'])->name('cajas.export');

// Reglas de Morosidad
Route::get('/reglas-morosidad', \App\Livewire\Admin\LatePaymentRules\Index::class)->name('late-payment-rules.index');

// Notificaciones
Route::get('/notifications', \App\Livewire\Admin\Notifications\Index::class)->name('notifications.index');

// WhatsApp - Nuevas rutas separadas
Route::prefix('whatsapp')->as('whatsapp.')->group(function () {
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
Route::get('/exportar-base-datos', \App\Livewire\Admin\DatabaseExport::class)->name('database-export');

// WhatsApp
Route::get('/whatsapp', \App\Livewire\Admin\Whatsapp\Index::class)->name('whatsapp.index');

// Conceptos de Pago
Route::get('/conceptos-pago', ConceptosPagoIndex::class)->name('conceptos-pago.index');
Route::get('/conceptos-pago/crear', ConceptosPagoCreate::class)->name('conceptos-pago.create');
Route::get('/conceptos-pago/{concepto}/editar', ConceptosPagoEdit::class)->name('conceptos-pago.edit');

// Estadísticas