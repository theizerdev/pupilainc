<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Recordatorios de Citas</h1>
            <p class="text-muted">Gestión y seguimiento de recordatorios automáticos</p>
        </div>
        <div>
            <button wire:click="procesarPendientes"
                    wire:loading.attr="disabled"
                    wire:target="procesarPendientes"
                    class="btn btn-success">
                <span wire:loading.remove wire:target="procesarPendientes">
                    <i class="fas fa-play"></i> Procesar Pendientes
                </span>
                <span wire:loading wire:target="procesarPendientes">
                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                </span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 {{ $estado === '' ? 'ring-active ring-primary' : '' }}"
                 role="button" wire:click="filtrarPorEstado(null)" title="Ver todos" style="cursor:pointer;">
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

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 {{ $estado === 'pendiente' ? 'ring-active ring-warning' : '' }}"
                 role="button" wire:click="filtrarPorEstado('pendiente')" title="Filtrar pendientes" style="cursor:pointer;">
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

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 {{ $estado === 'enviado' ? 'ring-active ring-success' : '' }}"
                 role="button" wire:click="filtrarPorEstado('enviado')" title="Filtrar enviados" style="cursor:pointer;">
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


    </div>

    <!-- Row with extra stats -->
    <div class="row mb-4">
          <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 {{ $estado === 'fallido' ? 'ring-active ring-danger' : '' }}"
                 role="button" wire:click="filtrarPorEstado('fallido')" title="Filtrar fallidos" style="cursor:pointer;">
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
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 {{ $estado === 'cancelado' ? 'ring-active ring-secondary' : '' }}"
                 role="button" wire:click="filtrarPorEstado('cancelado')" title="Filtrar cancelados" style="cursor:pointer;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Cancelados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelados'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Listos para Enviar
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

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Búsqueda:</label>
                        <input type="text" class="form-control" id="search"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Paciente, médico o cédula...">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select class="form-control" id="estado" wire:model.live="estado">
                            <option value="">Todos</option>
                            @foreach($estados as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" wire:model.live="tipo">
                            <option value="">Todos</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="canal">Canal:</label>
                        <select class="form-control" id="canal" wire:model.live="canal">
                            <option value="">Todos</option>
                            @foreach($canales as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="fechaDesde">Desde:</label>
                        <input type="date" class="form-control" id="fechaDesde" wire:model.live="fechaDesde">
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-secondary btn-block" wire:click="limpiarFiltros" title="Limpiar filtros">
                            <i class="fas fa-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Recordatorios</h6>
            <div class="d-flex gap-2">
                @if(($stats['fallidos'] ?? 0) > 0)
                    <button onclick="return confirm('¿Desea reenviar TODOS los recordatorios fallidos?')"
                            wire:click="reenviarTodosFallidos"
                            wire:loading.attr="disabled"
                            wire:target="reenviarTodosFallidos"
                            class="btn btn-outline-warning btn-sm">
                        <span wire:loading.remove wire:target="reenviarTodosFallidos">
                            <i class="fas fa-redo me-1"></i> Reenviar fallidos ({{ $stats['fallidos'] }})
                        </span>
                        <span wire:loading wire:target="reenviarTodosFallidos">
                            <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                        </span>
                    </button>
                @endif
                @if(($stats['pendientes'] ?? 0) > 0)
                    <button onclick="return confirm('¿Desea cancelar TODOS los recordatorios pendientes?')"
                            wire:click="cancelarTodosPendientes"
                            wire:loading.attr="disabled"
                            wire:target="cancelarTodosPendientes"
                            class="btn btn-outline-danger btn-sm">
                        <span wire:loading.remove wire:target="cancelarTodosPendientes">
                            <i class="fas fa-ban me-1"></i> Cancelar pendientes ({{ $stats['pendientes'] }})
                        </span>
                        <span wire:loading wire:target="cancelarTodosPendientes">
                            <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                        </span>
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Cita</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Tipo</th>
                            <th>Canal</th>
                            <th>Programado</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordatorios as $recordatorio)
                            <tr>
                                <td>
                                    <small class="text-muted">#{{ $recordatorio->cita->id }}</small><br>
                                    <span class="fw-semibold">{{ $recordatorio->cita->fecha_inicio ? \Carbon\Carbon::parse($recordatorio->cita->fecha_inicio)->format('d/m/Y H:i') : '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $recordatorio->cita->paciente->nombre_completo }}{{ $recordatorio->cita->paciente->nickname ? " ({$recordatorio->cita->paciente->nickname})" : "" }}</div>
                                    <small class="text-muted">{{ $recordatorio->cita->paciente->telefono ?? '-' }}</small>
                                </td>
                                <td>{{ $recordatorio->cita->medico->nombre_completo ?? '-' }}</td>
                                <td>{{ $recordatorio->cita->especialidad->nombre ?? 'Sin especialidad' }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $tipos[$recordatorio->tipo] ?? $recordatorio->tipo }}
                                    </span>
                                </td>
                                <td>
                                    @switch($recordatorio->canal)
                                        @case('whatsapp')
                                            <span class="badge bg-success">
                                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                            </span>
                                            @break
                                        @case('email')
                                            <span class="badge bg-primary">
                                                <i class="fas fa-envelope me-1"></i> Email
                                            </span>
                                            @break
                                        @case('sms')
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-sms me-1"></i> SMS
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $recordatorio->canal }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div>{{ $recordatorio->fecha_envio_programado?->format('d/m/Y H:i') }}</div>
                                    <small class="text-muted">{{ $recordatorio->fecha_envio_programado?->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @switch($recordatorio->estado)
                                        @case('pendiente')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i> Pendiente
                                            </span>
                                            @break
                                        @case('enviado')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i> Enviado
                                            </span>
                                            @break
                                        @case('fallido')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i> Fallido
                                            </span>
                                            @break
                                        @case('cancelado')
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-ban me-1"></i> Cancelado
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $recordatorio->estado }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $recordatorio->intentos ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @if($recordatorio->mensaje)
                                                <button class="dropdown-item" wire:click="verMensaje({{ $recordatorio->id }})">
                                                    <i class="fas fa-eye me-1"></i> Ver mensaje
                                                </button>
                                            @endif
                                            @if($recordatorio->estado === 'fallido')
                                                <button class="dropdown-item"
                                                        onclick="return confirm('¿Reenviar este recordatorio?')"
                                                        wire:click="reenviarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-redo me-1"></i> Reenviar
                                                </button>
                                            @endif
                                            @if($recordatorio->estado === 'pendiente')
                                                <button class="dropdown-item text-danger"
                                                        onclick="return confirm('¿Cancelar este recordatorio?')"
                                                        wire:click="cancelarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-ban me-1"></i> Cancelar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @if($recordatorio->error_mensaje)
                                        <small class="text-danger d-block mt-1" title="{{ $recordatorio->error_mensaje }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ Str::limit($recordatorio->error_mensaje, 20) }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    No se encontraron recordatorios
                                    @if($this->hayFiltrosActivos)
                                        <br><small class="text-muted">No hay resultados con los filtros aplicados</small>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $recordatorios->links('livewire.pagination') }}
            </div>
        </div>
    </div>

    <!-- Modal: Ver Mensaje -->
    @if($viendoId)
        @php
            $viendoRecordatorio = \App\Models\CitaRecordatorio::with(['cita.paciente'])->find($viendoId);
        @endphp
        @if($viendoRecordatorio)
            <div class="modal-backdrop-custom" wire:click="cerrarMensaje"></div>
            <div class="modal d-block" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title">
                                <i class="fas fa-envelope-open-text text-primary me-2"></i>
                                Mensaje del Recordatorio #{{ $viendoRecordatorio->id }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="cerrarMensaje"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted fw-bold text-uppercase">Paciente</small>
                                    <span class="badge bg-{{ $viendoRecordatorio->estado === 'enviado' ? 'success' : ($viendoRecordatorio->estado === 'fallido' ? 'danger' : ($viendoRecordatorio->estado === 'cancelado' ? 'secondary' : 'warning')) }}">
                                        {{ ucfirst($viendoRecordatorio->estado) }}
                                    </span>
                                </div>
                                <p class="mb-1 fw-semibold">{{ $viendoRecordatorio->cita->paciente->nombre_completo }}{{ $viendoRecordatorio->cita->paciente->nickname ? " ({$viendoRecordatorio->cita->paciente->nickname})" : "" }}</p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted fw-bold text-uppercase">Canal</small>
                                <p class="mb-1">
                                    @switch($viendoRecordatorio->canal)
                                        @case('whatsapp') <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp @break
                                        @case('email') <i class="fas fa-envelope text-primary me-1"></i> Email @break
                                        @case('sms') <i class="fas fa-sms text-secondary me-1"></i> SMS @break
                                    @endswitch
                                </p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted fw-bold text-uppercase">Contenido del Mensaje</small>
                                <div class="bg-light rounded p-3 mt-1" style="white-space: pre-wrap; font-size: 0.9rem; max-height: 300px; overflow-y: auto; overflow-x: hidden; word-break: break-word;">
                                    @php
                                        $mensaje = $viendoRecordatorio->mensaje ?? 'Sin contenido de mensaje registrado.';
                                        if (is_string($mensaje)) {
                                            $decoded = json_decode($mensaje, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $mensaje = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                            }
                                        }
                                    @endphp
                                    <pre style="margin: 0; white-space: pre-wrap; font-family: monospace;">{{ $mensaje }}</pre>
                                </div>
                            </div>

                            @if($viendoRecordatorio->fecha_envio_real)
                                <div class="mb-3">
                                    <small class="text-muted fw-bold text-uppercase">Enviado</small>
                                    <p class="mb-0">
                                        {{ $viendoRecordatorio->fecha_envio_real->format('d/m/Y H:i') }}
                                        <small class="text-muted">({{ $viendoRecordatorio->fecha_envio_real->diffForHumans() }})</small>
                                    </p>
                                </div>
                            @endif

                            @if($viendoRecordatorio->error_mensaje)
                                <div class="alert alert-danger mb-0 d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>
                                        <strong>Error:</strong><br>
                                        {{ $viendoRecordatorio->error_mensaje }}
                                    </div>
                                </div>
                            @endif

                            @if($viendoRecordatorio->confirmacion_respuesta !== null)
                                <div class="alert {{ $viendoRecordatorio->confirmacion_respuesta ? 'alert-success' : 'alert-warning' }} mb-0 mt-3">
                                    <i class="fas {{ $viendoRecordatorio->confirmacion_respuesta ? 'fa-check-circle' : 'fa-question-circle' }} me-1"></i>
                                    <strong>Confirmación:</strong>
                                    {{ $viendoRecordatorio->confirmacion_respuesta ? 'Cita confirmada por el paciente' : 'El paciente no confirmó la cita' }}
                                    @if($viendoRecordatorio->fecha_respuesta)
                                        <br><small>{{ $viendoRecordatorio->fecha_respuesta->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary" wire:click="cerrarMensaje">
                                <i class="fas fa-times me-1"></i> Cerrar
                            </button>
                            @if($viendoRecordatorio->estado === 'fallido')
                                <button onclick="return confirm('¿Reenviar este recordatorio?')"
                                        wire:click="reenviarRecordatorio({{ $viendoRecordatorio->id }})"
                                        class="btn btn-warning">
                                    <i class="fas fa-redo me-1"></i> Reenviar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@push('styles')
<style>
    .ring-active.ring-primary { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #4e73df !important; }
    .ring-active.ring-warning { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #f6c23e !important; }
    .ring-active.ring-success { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1cc88a !important; }
    .ring-active.ring-danger { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #e74a3b !important; }
    .ring-active.ring-secondary { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #858796 !important; }

    .text-xs {
        font-size: 0.7rem;
    }

    .modal-backdrop-custom {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }
    .modal.d-block {
        z-index: 1050;
    }
</style>
@endpush
