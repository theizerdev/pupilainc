<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-calendar-alt text-primary"></i> Re-agendamiento de Citas
            </h1>
            <p class="text-muted mb-0">Gestión de citas canceladas y no asistidas para re-agendar</p>
        </div>
        <div>
            <button onclick="return confirm('¿Desea procesar el re-agendamiento masivo de todas las citas en el rango de fechas seleccionado?')"
                    wire:click="procesarReagendamientosMasivos"
                    wire:loading.attr="disabled"
                    wire:target="procesarReagendamientosMasivos"
                    class="btn btn-primary shadow-sm">
                <span wire:loading.remove wire:target="procesarReagendamientosMasivos">
                    <i class="fas fa-magic me-1"></i> Re-agendar Masivamente
                </span>
                <span wire:loading wire:target="procesarReagendamientosMasivos">
                    <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                </span>
            </button>
        </div>
    </div>

    <!-- Stats Cards (clickable) -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'cancelada' ? 'ring-active ring-danger' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('cancelada')"
                 title="Filtrar canceladas">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Canceladas</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['canceladas'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-secondary border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === 'no_asistio' ? 'ring-active ring-secondary' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado('no_asistio')"
                 title="Filtrar no asistidas">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">No Asistidas</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['no_asistidas'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100 py-2 stats-card-clickable {{ $estado === '' ? 'ring-active ring-warning' : '' }}"
                 role="button"
                 wire:click="filtrarPorEstado(null)"
                 title="Ver todas las reagendables">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Reagendables</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['reagendables'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-redo-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ya Re-agendadas</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['reagendadas'] ?? 0 }}</div>
                        </div>
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-double"></i>
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
                        @foreach($estados as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-user-md me-1"></i> Médico
                    </label>
                    <select class="form-control form-control-sm" wire:model.live="medicoId">
                        <option value="">Todos los médicos</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}">{{ $medico->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-calendar me-1"></i> Desde
                    </label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="fechaDesde">
                </div>
                <div class="col-lg-2 col-md-3">
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
                    <i class="fas fa-list me-1"></i> Citas para Re-agendar
                </h6>
                <span class="badge bg-secondary">{{ $citas->total() }} registros</span>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Loading overlay -->
            <div wire:loading.delay wire:target="search, estado, medicoId, fechaDesde, fechaHasta, filtrarPorEstado, limpiarFiltros"
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
                            <th class="ps-3" width="6%">ID</th>
                            <th width="20%">Paciente</th>
                            <th width="18%">Médico / Especialidad</th>
                            <th width="14%">Fecha Original</th>
                            <th width="10%">Estado</th>
                            <th class="d-none d-lg-table-cell" width="17%">Motivo</th>
                            <th class="text-center pe-3" width="15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citas as $cita)
                            <tr class="cita-row {{ $cita->estado === 'cancelada' ? 'table-danger-soft' : ($cita->estado === 'no_asistio' ? 'table-secondary-soft' : '') }}">
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark fw-semibold">#{{ $cita->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle icon-circle-info me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block">{{ $cita->paciente->nombre_completo }}{{ $cita->paciente->nickname ? " ($cita->paciente->nickname)" : "" }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>{{ $cita->paciente->telefono ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle icon-circle-success me-2">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block">{{ $cita->medico->nombre_completo ?? '-' }}</span>
                                            @if($cita->especialidad)
                                                <span class="badge rounded-pill small"
                                                      style="background-color: {{ $cita->especialidad->color ?? '#6c757d' }}15; color: {{ $cita->especialidad->color ?? '#6c757d' }}; border: 1px solid {{ $cita->especialidad->color ?? '#6c757d' }}30; font-size: 0.7rem;">
                                                    <i class="{{ $cita->especialidad->icono ?? 'fas fa-stethoscope' }} me-1"></i>{{ $cita->especialidad->nombre }}
                                                </span>
                                            @else
                                                <small class="text-muted">Sin especialidad</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-semibold small">{{ $cita->fecha_inicio?->format('d/m/Y') ?? '-' }}</span><br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $cita->fecha_inicio?->format('H:i') ?? '-' }}
                                        </small>
                                        <br>
                                        <small class="text-muted fst-italic">{{ $cita->fecha_inicio?->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    @switch($cita->estado)
                                        @case('cancelada')
                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                <i class="fas fa-times me-1"></i> Cancelada
                                            </span>
                                            @break
                                        @case('no_asistio')
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                <i class="fas fa-user-times me-1"></i> No Asistió
                                            </span>
                                            @break
                                        @case('pendiente')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                <i class="fas fa-clock me-1"></i> Pendiente
                                            </span>
                                            @break
                                        @case('confirmada')
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                <i class="fas fa-check me-1"></i> Confirmada
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark rounded-pill px-3 py-2">{{ ucfirst($cita->estado) }}</span>
                                    @endswitch
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($cita->motivo)
                                        <span class="small" title="{{ $cita->motivo }}">
                                            {{ Str::limit($cita->motivo, 45) }}
                                        </span>
                                        @if(str_contains($cita->motivo, '[Re-agendada]'))
                                            <br>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill small mt-1">
                                                <i class="fas fa-redo me-1"></i> Ya re-agendada
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">Sin motivo</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    @if(in_array($cita->estado, ['cancelada', 'no_asistio']))
                                        <div class="d-flex justify-content-center gap-1">
                                            <button wire:click="buscarHorariosDisponibles({{ $cita->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="buscarHorariosDisponibles({{ $cita->id }})"
                                                    class="btn btn-outline-primary btn-sm action-btn-rect"
                                                    title="Buscar horarios disponibles">
                                                <span wire:loading.remove wire:target="buscarHorariosDisponibles({{ $cita->id }})">
                                                    <i class="fas fa-search me-1"></i> Horarios
                                                </span>
                                                <span wire:loading wire:target="buscarHorariosDisponibles({{ $cita->id }})">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                            <button onclick="return confirm('¿Re-agendar automáticamente al primer horario disponible?')"
                                                    wire:click="reagendarAutomaticamente({{ $cita->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="reagendarAutomaticamente({{ $cita->id }})"
                                                    class="btn btn-outline-success btn-sm action-btn-rect"
                                                    title="Re-agendar al primer horario disponible">
                                                <span wire:loading.remove wire:target="reagendarAutomaticamente({{ $cita->id }})">
                                                    <i class="fas fa-magic me-1"></i> Auto
                                                </span>
                                                <span wire:loading wire:target="reagendarAutomaticamente({{ $cita->id }})">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                        </div>
                                    @elseif(str_contains($cita->motivo ?? '', '[Re-agendada]'))
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                            <i class="fas fa-check me-1"></i> Re-agendada
                                        </span>
                                    @else
                                        <span class="text-muted small">No aplica</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <h5 class="text-muted">No se encontraron citas para re-agendar</h5>
                                        @if($this->hayFiltrosActivos)
                                            <p class="text-muted small mb-3">No hay resultados con los filtros aplicados</p>
                                            <button class="btn btn-primary btn-sm" wire:click="limpiarFiltros">
                                                <i class="fas fa-eraser me-1"></i> Limpiar filtros
                                            </button>
                                        @else
                                            <p class="text-muted small mb-0">No hay citas canceladas o no asistidas en el rango de fechas seleccionado</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($citas->hasPages())
            <div class="card-footer py-3 bg-white">
                <div class="d-flex justify-content-center">
                    {{ $citas->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal: Re-agendamiento -->
    @if($mostrarModalReagendamiento && $citaSeleccionada)
        <div class="modal-backdrop-custom" wire:click="cerrarModal"></div>
        <div class="modal d-block" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Re-agendar Cita #{{ $citaSeleccionada->id }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="cerrarModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Info de la cita original -->
                        <div class="bg-light rounded p-3 mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="fas fa-info-circle me-1"></i> Cita Original
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Paciente</small>
                                    <span class="fw-semibold">{{ $citaSeleccionada->paciente->nombre_completo }}{{ $citaSeleccionada->paciente->nickname ? " ($citaSeleccionada->paciente->nickname)" : "" }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Médico</small>
                                    <span class="fw-semibold">{{ $citaSeleccionada->medico->nombre_completo ?? '-' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Fecha Original</small>
                                    <span class="fw-semibold">{{ $citaSeleccionada->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}</span>
                                    <span class="badge bg-{{ $citaSeleccionada->estado === 'cancelada' ? 'danger' : 'secondary' }} ms-1 small">
                                        {{ ucfirst(str_replace('_', ' ', $citaSeleccionada->estado)) }}
                                    </span>
                                </div>
                            </div>
                            @if($citaSeleccionada->especialidad)
                                <div class="mt-2">
                                    <small class="text-muted d-block">Especialidad</small>
                                    <span class="badge rounded-pill"
                                          style="background-color: {{ $citaSeleccionada->especialidad->color ?? '#6c757d' }}15; color: {{ $citaSeleccionada->especialidad->color ?? '#6c757d' }}; border: 1px solid {{ $citaSeleccionada->especialidad->color ?? '#6c757d' }}30;">
                                        <i class="{{ $citaSeleccionada->especialidad->icono ?? 'fas fa-stethoscope' }} me-1"></i>{{ $citaSeleccionada->especialidad->nombre }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Horarios disponibles -->
                        @if(!empty($horariosDisponibles))
                            <h6 class="mb-3">
                                <i class="fas fa-clock text-primary me-1"></i>
                                Horarios Disponibles
                                <span class="badge bg-primary rounded-pill ms-1">{{ count($horariosDisponibles) }}</span>
                            </h6>
                            <div class="row g-2">
                                @foreach($horariosDisponibles as $index => $horario)
                                    @php
                                        $fecha = \Carbon\Carbon::parse($horario['fecha_hora']);
                                        $esHoy = $fecha->isToday();
                                        $esManana = $fecha->isTomorrow();
                                    @endphp
                                    <div class="col-lg-4 col-md-6">
                                        <button wire:click="reagendarManualmente('{{ $horario['fecha_hora'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="reagendarManualmente('{{ $horario['fecha_hora'] }}')"
                                                class="btn btn-outline-primary w-100 text-start horario-btn p-3 {{ $index === 0 ? 'border-primary border-2' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="horario-icon">
                                                        <i class="fas fa-calendar-day"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="d-block fw-semibold">
                                                        {{ $fecha->format('d/m/Y') }}
                                                        @if($esHoy)
                                                            <span class="badge bg-success ms-1 small">Hoy</span>
                                                        @elseif($esManana)
                                                            <span class="badge bg-info ms-1 small">Mañana</span>
                                                        @endif
                                                    </span>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-clock me-1"></i>{{ $fecha->format('H:i') }}
                                                        - {{ $fecha->copy()->addMinutes($horario['duracion'] ?? 30)->format('H:i') }}
                                                    </span>
                                                    <span class="d-block text-muted small fst-italic">{{ $fecha->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            @if($index === 0)
                                                <span class="badge bg-primary mt-2 small">Recomendado</span>
                                            @endif
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($horariosDisponibles) >= 10)
                                <div class="text-center mt-3">
                                    <button wire:click="ampliarBusqueda"
                                            wire:loading.attr="disabled"
                                            wire:target="ampliarBusqueda"
                                            class="btn btn-outline-secondary btn-sm">
                                        <span wire:loading.remove wire:target="ampliarBusqueda">
                                            <i class="fas fa-search-plus me-1"></i> Buscar más horarios ({{ $diasBusqueda + 7 }} días)
                                        </span>
                                        <span wire:loading wire:target="ampliarBusqueda">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Buscando...
                                        </span>
                                    </button>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div class="empty-state-icon-sm mb-3">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h6 class="text-muted">No se encontraron horarios disponibles</h6>
                                <p class="text-muted small mb-3">
                                    No hay horarios en los próximos {{ $diasBusqueda }} días para este médico
                                </p>
                                <button wire:click="ampliarBusqueda"
                                        wire:loading.attr="disabled"
                                        wire:target="ampliarBusqueda"
                                        class="btn btn-outline-primary btn-sm">
                                    <span wire:loading.remove wire:target="ampliarBusqueda">
                                        <i class="fas fa-search-plus me-1"></i> Ampliar búsqueda a {{ $diasBusqueda + 7 }} días
                                    </span>
                                    <span wire:loading wire:target="ampliarBusqueda">
                                        <i class="fas fa-spinner fa-spin me-1"></i> Buscando...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                            <i class="fas fa-times me-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
    .ring-danger { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #e74a3b !important; }
    .ring-secondary { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #858796 !important; }
    .ring-warning { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #f6c23e !important; }

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
    .icon-circle-info { background-color: #36b9cc; }
    .icon-circle-success { background-color: #1cc88a; }

    .table-danger-soft { background-color: rgba(231, 74, 59, 0.05); }
    .table-secondary-soft { background-color: rgba(133, 135, 150, 0.05); }

    .cita-row {
        transition: background-color 0.15s ease;
    }

    .action-btn-rect {
        font-size: 0.78rem;
        padding: 0.35rem 0.65rem;
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
    .empty-state-icon-sm {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f8f9fc, #e2e6ea);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #b7b9cc;
        margin: 0 auto;
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

    .horario-btn {
        border-radius: 0.5rem;
        transition: all 0.15s ease;
    }
    .horario-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
    }

    .horario-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        background: rgba(78, 115, 223, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4e73df;
        font-size: 1rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    @media (max-width: 768px) {
        .stats-icon { width: 2.5rem; height: 2.5rem; font-size: 1rem; }
        .card-header .d-flex { flex-direction: column; gap: 0.5rem; }
        .action-btn-rect { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
    }
</style>
@endpush
