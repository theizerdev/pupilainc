<div>
    @push('styles')
    <style>
        /* Hero Section */
        .recordatorios-hero {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .recordatorios-hero h2 {
            color: #fff;
            margin: 0;
        }
        .recordatorios-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards - Compact Style */
        .stat-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
            cursor: pointer;
        }
        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .stat-card.active {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px currentColor;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: .15rem;
        }
        .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.2;
        }

        /* Table Styles */
        .recordatorio-table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            text-transform: uppercase;
            font-size: .75rem;
            letter-spacing: .5px;
            color: #64748b;
            padding: .75rem 1rem;
            white-space: nowrap;
        }
        .recordatorio-table tbody tr {
            transition: background-color .15s;
        }
        .recordatorio-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .recordatorio-table td {
            vertical-align: middle;
            padding: .85rem 1rem;
        }

        /* Filter Section */
        .filter-section {
            background: #fff;
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* Badge Styling */
        .badge-estado {
            padding: .35rem .7rem;
            border-radius: .5rem;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-pendiente {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-enviado {
            background: #dcfce7;
            color: #166534;
        }
        .badge-fallido {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-cancelado {
            background: #f1f5f9;
            color: #475569;
        }
        .badge-canal {
            padding: .35rem .7rem;
            border-radius: .5rem;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-whatsapp {
            background: #dcfce7;
            color: #166534;
        }
        .badge-email {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-sms {
            background: #f1f5f9;
            color: #475569;
        }

        /* Action Dropdown */
        .btn-action-dropdown {
            border-radius: .5rem;
            padding: .4rem .6rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: all .2s;
        }
        .btn-action-dropdown:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
        }

        /* Modal Backdrop */
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

    <!-- Hero Section -->
    <div class="recordatorios-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-notification-3-line me-2"></i>Recordatorios de Citas</h2>
            <p class="mt-1">Gestión y seguimiento de recordatorios automáticos</p>
        </div>
        <button wire:click="procesarPendientes"
                wire:loading.attr="disabled"
                wire:target="procesarPendientes"
                class="btn btn-light btn-sm">
            <span wire:loading.remove wire:target="procesarPendientes">
                <i class="ri ri-play-line me-1"></i> Procesar Pendientes
            </span>
            <span wire:loading wire:target="procesarPendientes">
                <i class="ri ri-loader-4-line me-1"></i> Procesando...
            </span>
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card {{ $estado === '' ? 'active' : '' }}"
                 style="{{ $estado === '' ? 'color: #2563eb;' : '' }}"
                 wire:click="filtrarPorEstado(null)"
                 title="Ver todos">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-notification-badge-line"></i></div>
                <div>
                    <div class="stat-label">Total recordatorios</div>
                    <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card {{ $estado === 'pendiente' ? 'active' : '' }}"
                 style="{{ $estado === 'pendiente' ? 'color: #d97706;' : '' }}"
                 wire:click="filtrarPorEstado('pendiente')"
                 title="Filtrar pendientes">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-time-line"></i></div>
                <div>
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-value text-warning">{{ $stats['pendientes'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card {{ $estado === 'enviado' ? 'active' : '' }}"
                 style="{{ $estado === 'enviado' ? 'color: #16a34a;' : '' }}"
                 wire:click="filtrarPorEstado('enviado')"
                 title="Filtrar enviados">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="stat-label">Enviados</div>
                    <div class="stat-value text-success">{{ $stats['enviados'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card {{ $estado === 'fallido' ? 'active' : '' }}"
                 style="{{ $estado === 'fallido' ? 'color: #dc2626;' : '' }}"
                 wire:click="filtrarPorEstado('fallido')"
                 title="Filtrar fallidos">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="ri ri-error-warning-line"></i></div>
                <div>
                    <div class="stat-label">Fallidos</div>
                    <div class="stat-value text-danger">{{ $stats['fallidos'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card {{ $estado === 'cancelado' ? 'active' : '' }}"
                 style="{{ $estado === 'cancelado' ? 'color: #64748b;' : '' }}"
                 wire:click="filtrarPorEstado('cancelado')"
                 title="Filtrar cancelados">
                <div class="stat-icon" style="background:#f1f5f9;color:#64748b;"><i class="ri ri-close-circle-line"></i></div>
                <div>
                    <div class="stat-label">Cancelados</div>
                    <div class="stat-value">{{ $stats['cancelados'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-send-plane-line"></i></div>
                <div>
                    <div class="stat-label">Listos para enviar</div>
                    <div class="stat-value text-primary">{{ $stats['por_enviar'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            @if(($stats['fallidos'] ?? 0) > 0 || ($stats['pendientes'] ?? 0) > 0)
                <div class="d-flex gap-2 h-100">
                    @if(($stats['fallidos'] ?? 0) > 0)
                        <button onclick="return confirm('¿Desea reenviar TODOS los recordatorios fallidos?')"
                                wire:click="reenviarTodosFallidos"
                                wire:loading.attr="disabled"
                                wire:target="reenviarTodosFallidos"
                                class="btn btn-outline-warning btn-sm flex-fill">
                            <span wire:loading.remove wire:target="reenviarTodosFallidos">
                                <i class="ri ri-refresh-line me-1"></i> Reenviar fallidos ({{ $stats['fallidos'] }})
                            </span>
                            <span wire:loading wire:target="reenviarTodosFallidos">
                                <i class="ri ri-loader-4-line me-1"></i> Procesando...
                            </span>
                        </button>
                    @endif
                    @if(($stats['pendientes'] ?? 0) > 0)
                        <button onclick="return confirm('¿Desea cancelar TODOS los recordatorios pendientes?')"
                                wire:click="cancelarTodosPendientes"
                                wire:loading.attr="disabled"
                                wire:target="cancelarTodosPendientes"
                                class="btn btn-outline-danger btn-sm flex-fill">
                            <span wire:loading.remove wire:target="cancelarTodosPendientes">
                                <i class="ri ri-close-line me-1"></i> Cancelar pendientes ({{ $stats['pendientes'] }})
                            </span>
                            <span wire:loading wire:target="cancelarTodosPendientes">
                                <i class="ri ri-loader-4-line me-1"></i> Procesando...
                            </span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="small fw-bold mb-1">Búsqueda</label>
                <input type="text" class="form-control form-control-sm"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Paciente, médico o cédula...">
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Estado</label>
                <select class="form-select form-select-sm" wire:model.live="estado">
                    <option value="">Todos</option>
                    @foreach($estados as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Tipo</label>
                <select class="form-select form-select-sm" wire:model.live="tipo">
                    <option value="">Todos</option>
                    @foreach($tipos as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Canal</label>
                <select class="form-select form-select-sm" wire:model.live="canal">
                    <option value="">Todos</option>
                    @foreach($canales as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Desde</label>
                <input type="date" class="form-control form-control-sm" wire:model.live="fechaDesde">
            </div>

            <div class="col-md-1">
                <label class="small fw-bold mb-1">&nbsp;</label>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="limpiarFiltros" title="Limpiar filtros">
                    <i class="ri ri-close-line"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm" style="border: 1px solid rgba(0,0,0,.06); border-radius: .75rem;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover recordatorio-table mb-0">
                    <thead>
                        <tr>
                            <th>Cita</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Tipo / Canal</th>
                            <th>Programado</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th class="text-end">Acciones</th>
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
                                    <div class="fw-semibold">{{ $recordatorio->cita->paciente->nombre_completo }}</div>
                                    @if($recordatorio->cita->paciente->nickname)
                                        <small class="text-muted">{{ $recordatorio->cita->paciente->nickname }}</small>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $recordatorio->cita->paciente->telefono ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $recordatorio->cita->medico->nombre_completo ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $tipos[$recordatorio->tipo] ?? $recordatorio->tipo }}
                                        </span>
                                    </div>
                                    <div>
                                        @switch($recordatorio->canal)
                                            @case('whatsapp')
                                                <span class="badge-canal badge-whatsapp">
                                                    <i class="ri ri-whatsapp-line me-1"></i> WhatsApp
                                                </span>
                                                @break
                                            @case('email')
                                                <span class="badge-canal badge-email">
                                                    <i class="ri ri-mail-line me-1"></i> Email
                                                </span>
                                                @break
                                            @case('sms')
                                                <span class="badge-canal badge-sms">
                                                    <i class="ri ri-message-2-line me-1"></i> SMS
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $recordatorio->canal }}</span>
                                        @endswitch
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $recordatorio->fecha_envio_programado?->format('d/m/Y H:i') }}</div>
                                    <small class="text-muted">{{ $recordatorio->fecha_envio_programado?->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @switch($recordatorio->estado)
                                        @case('pendiente')
                                            <span class="badge-estado badge-pendiente">
                                                <i class="ri ri-time-line me-1"></i> Pendiente
                                            </span>
                                            @break
                                        @case('enviado')
                                            <span class="badge-estado badge-enviado">
                                                <i class="ri ri-check-line me-1"></i> Enviado
                                            </span>
                                            @break
                                        @case('fallido')
                                            <span class="badge-estado badge-fallido">
                                                <i class="ri ri-close-line me-1"></i> Fallido
                                            </span>
                                            @break
                                        @case('cancelado')
                                            <span class="badge-estado badge-cancelado">
                                                <i class="ri ri-forbid-line me-1"></i> Cancelado
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $recordatorio->estado }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $recordatorio->intentos ?? 0 }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn-action-dropdown dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @if($recordatorio->mensaje)
                                                <button class="dropdown-item" wire:click="verMensaje({{ $recordatorio->id }})">
                                                    <i class="ri ri-eye-line me-2"></i>Ver mensaje
                                                </button>
                                            @endif
                                            @if($recordatorio->estado === 'fallido')
                                                <button class="dropdown-item"
                                                        onclick="return confirm('¿Reenviar este recordatorio?')"
                                                        wire:click="reenviarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="ri ri-refresh-line me-2"></i>Reenviar
                                                </button>
                                            @endif
                                            @if($recordatorio->estado === 'pendiente')
                                                <button class="dropdown-item text-danger"
                                                        onclick="return confirm('¿Cancelar este recordatorio?')"
                                                        wire:click="cancelarRecordatorio({{ $recordatorio->id }})">
                                                    <i class="ri ri-close-circle-line me-2"></i>Cancelar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @if($recordatorio->error_mensaje)
                                        <small class="text-danger d-block mt-1" title="{{ $recordatorio->error_mensaje }}">
                                            <i class="ri ri-error-warning-line me-1"></i>{{ Str::limit($recordatorio->error_mensaje, 20) }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No se encontraron recordatorios</p>
                                        @if($this->hayFiltrosActivos)
                                            <small class="text-muted">No hay resultados con los filtros aplicados</small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recordatorios->hasPages())
                <div class="p-3 border-top">
                    {{ $recordatorios->links('livewire.pagination') }}
                </div>
            @endif
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
                                <i class="ri ri-mail-open-line text-primary me-2"></i>
                                Mensaje del Recordatorio #{{ $viendoRecordatorio->id }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="cerrarMensaje"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted fw-bold text-uppercase">Paciente</small>
                                    <span class="badge-estado badge-{{ $viendoRecordatorio->estado }}">
                                        {{ ucfirst($viendoRecordatorio->estado) }}
                                    </span>
                                </div>
                                <p class="mb-1 fw-semibold">{{ $viendoRecordatorio->cita->paciente->nombre_completo }}</p>
                                @if($viendoRecordatorio->cita->paciente->nickname)
                                    <small class="text-muted">{{ $viendoRecordatorio->cita->paciente->nickname }}</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <small class="text-muted fw-bold text-uppercase">Canal</small>
                                <p class="mb-1">
                                    @switch($viendoRecordatorio->canal)
                                        @case('whatsapp') <i class="ri ri-whatsapp-line text-success me-1"></i> WhatsApp @break
                                        @case('email') <i class="ri ri-mail-line text-primary me-1"></i> Email @break
                                        @case('sms') <i class="ri ri-message-2-line text-secondary me-1"></i> SMS @break
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
                                    <i class="ri ri-error-warning-line me-2 mt-1"></i>
                                    <div>
                                        <strong>Error:</strong><br>
                                        {{ $viendoRecordatorio->error_mensaje }}
                                    </div>
                                </div>
                            @endif

                            @if($viendoRecordatorio->confirmacion_respuesta !== null)
                                <div class="alert {{ $viendoRecordatorio->confirmacion_respuesta ? 'alert-success' : 'alert-warning' }} mb-0 mt-3">
                                    <i class="ri {{ $viendoRecordatorio->confirmacion_respuesta ? 'ri-checkbox-circle-line' : 'ri-question-line' }} me-1"></i>
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
                                <i class="ri ri-close-line me-1"></i> Cerrar
                            </button>
                            @if($viendoRecordatorio->estado === 'fallido')
                                <button onclick="return confirm('¿Reenviar este recordatorio?')"
                                        wire:click="reenviarRecordatorio({{ $viendoRecordatorio->id }})"
                                        class="btn btn-warning">
                                    <i class="ri ri-refresh-line me-1"></i> Reenviar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
