<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-bell text-primary"></i> Recordatorios de Citas
            </h1>
            <p class="text-muted mb-0">Gestión y seguimiento de recordatorios automáticos</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="procesarPendientes"
                    wire:loading.attr="disabled"
                    wire:target="procesarPendientes"
                    class="btn btn-success shadow-sm">
                <span wire:loading.remove wire:target="procesarPendientes">
                    <i class="fas fa-play me-1"></i> Procesar Pendientes
                </span>
                <span wire:loading wire:target="procesarPendientes">
                    <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                </span>
            </button>
        </div>
    </div>

    <!-- Stats Cards (clickable) -->
    <div class="row mb-4">
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === '' ? 'ring-active ring-primary' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado(null)"
                 title="Ver todos">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'pendiente' ? 'ring-active ring-warning' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('pendiente')"
                 title="Filtrar pendientes">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['pendientes'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-success border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'enviado' ? 'ring-active ring-success' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('enviado')"
                 title="Filtrar enviados">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Enviados</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['enviados'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'fallido' ? 'ring-active ring-danger' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('fallido')"
                 title="Filtrar fallidos">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Fallidos</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['fallidos'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-secondary border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'cancelado' ? 'ring-active ring-secondary' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('cancelado')"
                 title="Filtrar cancelados">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Cancelados</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelados'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Listos para enviar</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['por_enviar'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filtros de Búsqueda
            </h6>
            @if($this->hayFiltrosActivos)
                <button class="btn btn-outline-secondary btn-sm"
                        wire:click="limpiarFiltros"
                        wire:loading.attr="disabled"
                        wire:target="limpiarFiltros">
                    <i class="fas fa-eraser me-1"></i> Limpiar filtros
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-search me-1"></i> Buscar
                    </label>
                    <input type="text"
                           class="form-control form-control-sm"
                           placeholder="Paciente, médico o cédula..."
                           wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-flag me-1"></i> Estado
                    </label>
                    <select class="form-control form-control-sm" wire:model.live="estado">
                        <option value="">Todos</option>
                        @foreach($estados as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-tag me-1"></i> Tipo
                    </label>
                    <select class="form-control form-control-sm" wire:model.live="tipo">
                        <option value="">Todos</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-broadcast-tower me-1"></i> Canal
                    </label>
                    <select class="form-control form-control-sm" wire:model.live="canal">
                        <option value="">Todos</option>
                        @foreach($canales as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-calendar me-1"></i> Desde
                    </label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="fechaDesde">
                </div>
                <div class="col-lg-1 col-md-4">
                    <label class="form-label small text-muted mb-1">Hasta</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="fechaHasta">
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-1"></i> Listado de Recordatorios
                </h6>
                <span class="badge bg-secondary">{{ $recordatorios->total() }} registros</span>
            </div>
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
        <div class="card-body p-0">
            <!-- Loading overlay -->
            <div wire:loading.delay wire:target="search, estado, tipo, canal, fechaDesde, fechaHasta, filtrarPorEstado, limpiarFiltros"
                 class="loading-overlay">
                <div class="d-flex align-items-center justify-content-center py-4">
                    <div class="spinner-border text-primary me-2" role="status"></div>
                    <span class="text-muted">Actualizando resultados...</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" width="13%">Cita</th>
                            <th width="18%">Paciente</th>
                            <th class="d-none d-lg-table-cell" width="14%">Médico</th>
                            <th class="d-none d-xl-table-cell" width="11%">Especialidad</th>
                            <th width="8%">Tipo</th>
                            <th width="8%">Canal</th>
                            <th width="12%">Programado</th>
                            <th width="8%">Estado</th>
                            <th class="text-center" width="5%">Intentos</th>
                            <th class="text-center pe-3" width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordatorios as $recordatorio)
                            <tr class="recordatorio-row {{ $recordatorio->estado === 'fallido' ? 'table-danger-soft' : ($recordatorio->estado === 'cancelado' ? 'table-secondary-soft' : '') }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle icon-circle-primary me-2">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small">#{{ $recordatorio->cita->id }}</span><br>
                                            <span class="fw-semibold small">{{ $recordatorio->cita->fecha_inicio ? \Carbon\Carbon::parse($recordatorio->cita->fecha_inicio)->format('d/m/Y H:i') : '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle icon-circle-info me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block">{{ $recordatorio->cita->paciente->nombre_completo }}{{ $recordatorio->cita->paciente->nickname ? " ($recordatorio->cita->paciente->nickname)" : "" }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>{{ $recordatorio->cita->paciente->telefono ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle icon-circle-success me-2">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <span class="fw-semibold">{{ $recordatorio->cita->medico->nombre_completo ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    @if($recordatorio->cita->especialidad)
                                        <span class="badge rounded-pill"
                                              style="background-color: {{ $recordatorio->cita->especialidad->color ?? '#6c757d' }}15; color: {{ $recordatorio->cita->especialidad->color ?? '#6c757d' }}; border: 1px solid {{ $recordatorio->cita->especialidad->color ?? '#6c757d' }}30;">
                                            <i class="{{ $recordatorio->cita->especialidad->icono ?? 'fas fa-stethoscope' }} me-1"></i>
                                            {{ $recordatorio->cita->especialidad->nombre }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Sin especialidad</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill">
                                        <i class="fas fa-clock me-1"></i>{{ $tipos[$recordatorio->tipo] ?? $recordatorio->tipo }}
                                    </span>
                                </td>
                                <td>
                                    @switch($recordatorio->canal)
                                        @case('whatsapp')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                            </span>
                                            @break
                                        @case('email')
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                                <i class="fas fa-envelope me-1"></i> Email
                                            </span>
                                            @break
                                        @case('sms')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">
                                                <i class="fas fa-sms me-1"></i> SMS
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark rounded-pill">{{ $recordatorio->canal }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-semibold small">{{ $recordatorio->fecha_envio_programado?->format('d/m/Y') }}</span><br>
                                        <small class="text-muted">{{ $recordatorio->fecha_envio_programado?->format('H:i') }}</small>
                                        <br>
                                        <small class="text-muted fst-italic">{{ $recordatorio->fecha_envio_programado?->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @switch($recordatorio->estado)
                                        @case('pendiente')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                <i class="fas fa-clock me-1"></i> Pendiente
                                            </span>
                                            @break
                                        @case('enviado')
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="fas fa-check me-1"></i> Enviado
                                            </span>
                                            @break
                                        @case('fallido')
                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                <i class="fas fa-times me-1"></i> Fallido
                                            </span>
                                            @break
                                        @case('cancelado')
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                <i class="fas fa-ban me-1"></i> Cancelado
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark rounded-pill px-3 py-2">{{ $recordatorio->estado }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2">{{ $recordatorio->intentos ?? 0 }}</span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Ver mensaje -->
                                        @if($recordatorio->mensaje)
                                            <button wire:click="verMensaje({{ $recordatorio->id }})"
                                                    class="btn btn-outline-info btn-sm rounded-circle action-btn"
                                                    title="Ver mensaje">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif

                                        <!-- Reenviar (solo fallidos) -->
                                        @if($recordatorio->estado === 'fallido')
                                            <button onclick="return confirm('¿Reenviar este recordatorio?')"
                                                    wire:click="reenviarRecordatorio({{ $recordatorio->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="reenviarRecordatorio({{ $recordatorio->id }})"
                                                    class="btn btn-outline-warning btn-sm rounded-circle action-btn"
                                                    title="Reenviar">
                                                <span wire:loading.remove wire:target="reenviarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-redo"></i>
                                                </span>
                                                <span wire:loading wire:target="reenviarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                        @endif

                                        <!-- Cancelar (solo pendientes) -->
                                        @if($recordatorio->estado === 'pendiente')
                                            <button onclick="return confirm('¿Cancelar este recordatorio?')"
                                                    wire:click="cancelarRecordatorio({{ $recordatorio->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="cancelarRecordatorio({{ $recordatorio->id }})"
                                                    class="btn btn-outline-danger btn-sm rounded-circle action-btn"
                                                    title="Cancelar">
                                                <span wire:loading.remove wire:target="cancelarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-ban"></i>
                                                </span>
                                                <span wire:loading wire:target="cancelarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                    @if($recordatorio->error_mensaje)
                                        <div class="mt-1">
                                            <small class="text-danger" title="{{ $recordatorio->error_mensaje }}" data-bs-toggle="tooltip">
                                                <i class="fas fa-exclamation-triangle me-1"></i>{{ Str::limit($recordatorio->error_mensaje, 20) }}
                                            </small>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-bell-slash"></i>
                                        </div>
                                        <h5 class="text-muted">No se encontraron recordatorios</h5>
                                        @if($this->hayFiltrosActivos)
                                            <p class="text-muted small mb-3">No hay resultados con los filtros aplicados</p>
                                            <button class="btn btn-primary btn-sm" wire:click="limpiarFiltros">
                                                <i class="fas fa-eraser me-1"></i> Limpiar filtros
                                            </button>
                                        @else
                                            <p class="text-muted small mb-0">Los recordatorios se crean automáticamente al agendar citas</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recordatorios->hasPages())
            <div class="card-footer py-3 bg-white">
                <div class="d-flex justify-content-center">
                    {{ $recordatorios->links('livewire.pagination') }}
                </div>
            </div>
        @endif
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
                                    <span class="badge bg-{{ $viendoRecordatorio->estado === 'enviado' ? 'success' : ($viendoRecordatorio->estado === 'fallido' ? 'danger' : ($viendoRecordatorio->estado === 'cancelado' ? 'secondary' : 'warning')) }} rounded-pill">
                                        {{ ucfirst($viendoRecordatorio->estado) }}
                                    </span>
                                </div>
                                <p class="mb-1 fw-semibold">{{ $viendoRecordatorio->cita->paciente->nombre_completo }}{{ $viendoRecordatorio->cita->paciente->nickname ? " ($viendoRecordatorio->cita->paciente->nickname)" : "" }}</p>
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
                                <div class="bg-light rounded p-3 mt-1" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $viendoRecordatorio->mensaje ?? 'Sin contenido de mensaje registrado.' }}</div>
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
    .stats-card-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .stats-card-clickable:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.12) !important;
    }
    .ring-active {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px currentColor !important;
    }
    .ring-primary { --ring-color: #4e73df; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #4e73df !important; }
    .ring-warning { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #f6c23e !important; }
    .ring-success { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1cc88a !important; }
    .ring-danger { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #e74a3b !important; }
    .ring-secondary { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #858796 !important; }

    .stats-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .icon-circle {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #fff;
    }
    .icon-circle-primary { background-color: #4e73df; }
    .icon-circle-info { background-color: #36b9cc; }
    .icon-circle-success { background-color: #1cc88a; }

    .table-danger-soft { background-color: rgba(231, 74, 59, 0.05); }
    .table-secondary-soft { background-color: rgba(133, 135, 150, 0.05); }

    .recordatorio-row {
        transition: background-color 0.15s ease;
    }

    .action-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    .empty-state {
        padding: 2rem;
    }
    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f8f9fc, #e2e6ea);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #b7b9cc;
    }

    .loading-overlay {
        background: rgba(255,255,255,0.7);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10;
    }

    .card-body {
        position: relative;
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

    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
    }

    .badge-pill,
    .rounded-pill {
        padding: 0.4em 0.8em;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    @media (max-width: 768px) {
        .stats-icon { width: 2.5rem; height: 2.5rem; font-size: 1rem; }
        .card-header .d-flex { flex-direction: column; gap: 0.5rem; }
        .action-btn { width: 28px; height: 28px; }
    }
</style>
@endpush
