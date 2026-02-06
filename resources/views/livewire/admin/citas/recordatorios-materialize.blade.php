<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">📅 Recordatorios de Citas</h1>
            <p class="text-muted">Gestión y seguimiento de recordatorios automáticos</p>
        </div>
        <div>
            <button wire:click="procesarPendientes" class="btn btn-success">
                <i class="fas fa-play"></i> Procesar Pendientes
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Recordatorios
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pendientes'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Enviados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['enviados'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Fallidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['fallidos'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Por Enviar
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['por_enviar'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Paciente o médico..." wire:model="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Estado</label>
                        <select class="form-control form-control-sm" wire:model="estado">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Tipo</label>
                        <select class="form-control form-control-sm" wire:model="tipo">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Canal</label>
                        <select class="form-control form-control-sm" wire:model="canal">
                            <option value="">Todos los canales</option>
                            @foreach($canales as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Desde</label>
                        <input type="date" class="form-control form-control-sm" wire:model="fechaDesde">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small text-muted mb-1">Hasta</label>
                        <input type="date" class="form-control form-control-sm" wire:model="fechaHasta">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Listado de Recordatorios
            </h6>
            <span class="badge badge-secondary">{{ $recordatorios->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="12%">Cita</th>
                            <th width="18%">Paciente</th>
                            <th width="15%">Médico</th>
                            <th width="12%">Especialidad</th>
                            <th width="8%">Tipo</th>
                            <th width="8%">Canal</th>
                            <th width="10%">Fecha Programada</th>
                            <th width="8%">Estado</th>
                            <th width="5%">Intentos</th>
                            <th width="12%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordatorios as $recordatorio)
                            <tr class="{{ $recordatorio->estado === 'pendiente' ? 'table-warning' : ($recordatorio->estado === 'enviado' ? 'table-success' : ($recordatorio->estado === 'fallido' ? 'table-danger' : '')) }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">#{{ $recordatorio->cita->id }}</small><br>
                                            <small class="font-weight-bold">{{ \Carbon\Carbon::parse($recordatorio->cita->fecha_inicio)->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $recordatorio->cita->paciente->nombre_completo }}</strong>
                                            <small class="text-muted">{{ $recordatorio->cita->paciente->telefono }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $recordatorio->cita->medico->nombre_completo }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light" style="background-color: {{ $recordatorio->cita->especialidad->color ?? '#6c757d' }}20; color: {{ $recordatorio->cita->especialidad->color ?? '#6c757d' }};">
                                        <i class="{{ $recordatorio->cita->especialidad->icono ?? 'fas fa-stethoscope' }}"></i>
                                        {{ $recordatorio->cita->especialidad->nombre ?? 'Sin especialidad' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-clock"></i> {{ $tipos[$recordatorio->tipo] ?? $recordatorio->tipo }}
                                    </span>
                                </td>
                                <td>
                                    @switch($recordatorio->canal)
                                        @case('whatsapp')
                                            <span class="badge badge-success">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </span>
                                            @break
                                        @case('email')
                                            <span class="badge badge-primary">
                                                <i class="fas fa-envelope"></i> Email
                                            </span>
                                            @break
                                        @case('sms')
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-sms"></i> SMS
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="text-center">
                                        <i class="fas fa-calendar-alt text-muted"></i><br>
                                        <small class="font-weight-bold">{{ \Carbon\Carbon::parse($recordatorio->fecha_envio_programado)->format('d/m/Y') }}</small><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($recordatorio->fecha_envio_programado)->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @switch($recordatorio->estado)
                                        @case('pendiente')
                                            <span class="badge badge-warning badge-pill">
                                                <i class="fas fa-clock"></i> Pendiente
                                            </span>
                                            @break
                                        @case('enviado')
                                            <span class="badge badge-success badge-pill">
                                                <i class="fas fa-check"></i> Enviado
                                            </span>
                                            @break
                                        @case('fallido')
                                            <span class="badge badge-danger badge-pill">
                                                <i class="fas fa-times"></i> Fallido
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-pill">{{ $recordatorio->intentos }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($recordatorio->estado === 'fallido')
                                            <button wire:click="reenviarRecordatorio({{ $recordatorio->id }})" 
                                                    class="btn btn-outline-warning" 
                                                    title="Reenviar">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif
                                        @if($recordatorio->estado === 'pendiente')
                                            <button wire:click="cancelarRecordatorio({{ $recordatorio->id }})" 
                                                    class="btn btn-outline-danger" 
                                                    title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @if($recordatorio->error_mensaje)
                                        <div class="mt-1">
                                            <small class="text-danger" title="{{ $recordatorio->error_mensaje }}">
                                                <i class="fas fa-exclamation-triangle"></i> {{ Str::limit($recordatorio->error_mensaje, 15) }}
                                            </small>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i><br>
                                    <h5>No se encontraron recordatorios</h5>
                                    <p class="small">Ajusta los filtros o crea nuevos recordatorios</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recordatorios->hasPages())
            <div class="card-footer py-3">
                <div class="d-flex justify-content-center">
                    {{ $recordatorios->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .card.border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .card.border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .card.border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .card.border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    .card.border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
    }
    .badge-pill {
        padding: 0.5em 1em;
        border-radius: 10rem;
    }
</style>
@endpush