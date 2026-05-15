<div class="w-100">
    @section('title', 'Registro de Actividades')

    @push('styles')
    <style>
        .activity-hero { background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .activity-hero h2 { color:#fff; margin:0; }
        .activity-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .activity-row { transition:background .12s; }
        .activity-row:hover { background:#f8f9ff; }
        .security-row { transition:background .12s; }
        .security-row:hover { background:#fff5f5; }

        .security-btn { transition:all .2s; }
        .security-btn:hover { transform:translateY(-2px); }

        .modal-modern .modal-header { border-radius:.5rem .5rem 0 0; }
        .modal-modern .modal-content { border:none; box-shadow:0 10px 40px rgba(0,0,0,.15); }
        .modal-modern .modal-body { overflow-y:auto; max-height:75vh; padding:1.5rem !important; }
        .modal-modern .card { overflow:hidden; }
        .modal-modern .badge { white-space:normal !important; word-wrap:break-word; overflow-wrap:break-word; }
        .modal-modern pre { overflow-x:auto; white-space:pre-wrap; word-break:break-word; }
        .modal-modern table { width:100%; }
        .modal-modern td, .modal-modern th { word-wrap:break-word; overflow-wrap:break-word; vertical-align:top; }

        .text-truncate-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; }
        .text-truncate-3 { display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="activity-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-history-line me-2"></i>Registro de Actividades</h2>
            <p class="mt-1">Monitorea y administra todas las actividades del sistema</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ri ri-download-line me-1"></i>Exportar
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" wire:click="export('csv')"><i class="ri ri-file-text-line me-2"></i>CSV</button></li>
                    <li><button class="dropdown-item" wire:click="export('json')"><i class="ri ri-code-line me-2"></i>JSON</button></li>
                    <li><button class="dropdown-item" wire:click="export('xml')"><i class="ri ri-code-s-slash-line me-2"></i>XML</button></li>
                </ul>
            </div>
            @if(count($selectedActivities) > 0)
                <button class="btn btn-danger btn-sm" wire:click="deleteSelected" wire:confirm="¿Eliminar {{ count($selectedActivities) }} actividades?">
                    <i class="ri ri-delete-bin-line me-1"></i>Eliminar ({{ count($selectedActivities) }})
                </button>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-list-check-line"></i></div>
                <div>
                    <div class="stat-label">Total actividades</div>
                    <div class="stat-value">{{ $activities->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-shield-check-line"></i></div>
                <div>
                    <div class="stat-label">Seguridad total</div>
                    <div class="stat-value">{{ $securityCounts['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-error-warning-line"></i></div>
                <div>
                    <div class="stat-label">Logins fallidos</div>
                    <div class="stat-value">{{ $securityCounts['login_fallido'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="ri ri-lock-line"></i></div>
                <div>
                    <div class="stat-label">Usuarios bloqueados</div>
                    <div class="stat-value">{{ $securityCounts['usuario_bloqueado'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Compact Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-search-line me-1"></i>Buscar
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Usuario, acción, modelo..." wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                                <i class="ri ri-close-line"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-user-line me-1"></i>Usuario
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="userFilter">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-function-line me-1"></i>Acción
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="actionFilter">
                        <option value="">Todas</option>
                        @foreach($actions as $actionValue => $actionLabel)
                            <option value="{{ $actionValue }}">{{ $actionLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-calendar-line me-1"></i>Fecha
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="dateRange">
                        <option value="">Todo</option>
                        <option value="today">Hoy</option>
                        <option value="yesterday">Ayer</option>
                        <option value="last7days">7 días</option>
                        <option value="week">Semana</option>
                        <option value="last30days">30 días</option>
                        <option value="month">Mes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                        <i class="ri ri-broom-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>

            @if($search || $userFilter || $actionFilter || $dateRange || $doctorFilter || $onlyCitaEstados)
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted small">Filtros activos:</span>
                    @if($search)
                        <span class="badge bg-primary">Búsqueda: "{{ $search }}"</span>
                    @endif
                    @if($userFilter)
                        <span class="badge bg-info">Usuario: {{ $users->find($userFilter)?->name ?? 'N/A' }}</span>
                    @endif
                    @if($actionFilter)
                        <span class="badge bg-warning text-dark">Acción: {{ $actions[$actionFilter] ?? $actionFilter }}</span>
                    @endif
                    @if($dateRange)
                        <span class="badge bg-success">Fecha: {{ ucfirst($dateRange) }}</span>
                    @endif
                    @if($doctorFilter)
                        <span class="badge bg-info">Médico: {{ $medicos->firstWhere('id', (int) $doctorFilter)?->nombres ?? 'N/A' }}</span>
                    @endif
                    @if($onlyCitaEstados)
                        <span class="badge bg-dark">Solo estados de citas</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Security Events Panel --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri ri-shield-check-line ri-20px text-danger"></i>
                    <h5 class="mb-0 fw-semibold">Eventos de Seguridad</h5>
                    @if($securityCounts['total'] > 0)
                        <span class="badge bg-danger rounded-pill">{{ $securityCounts['total'] }}</span>
                    @endif
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('securityFilter', '')">
                    <i class="ri ri-close-line me-1"></i>Cerrar Panel
                </button>
            </div>

            {{-- Quick Stats --}}
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <button wire:click="$set('securityFilter', 'todos')" class="btn {{ $securityFilter === 'todos' ? 'btn-danger' : 'btn-outline-danger' }} btn-sm security-btn">
                        <i class="ri ri-error-warning-line me-1"></i>Todos ({{ $securityCounts['total'] }})
                    </button>
                </div>
                <div class="col-auto">
                    <button wire:click="$set('securityFilter', 'login_fallido')" class="btn {{ $securityFilter === 'login_fallido' ? 'btn-warning' : 'btn-outline-warning' }} btn-sm security-btn">
                        <i class="ri ri-login-circle-line me-1"></i>Logins Fallidos ({{ $securityCounts['login_fallido'] }})
                    </button>
                </div>
                <div class="col-auto">
                    <button wire:click="$set('securityFilter', 'usuario_bloqueado')" class="btn {{ $securityFilter === 'usuario_bloqueado' ? 'btn-danger' : 'btn-outline-danger' }} btn-sm security-btn">
                        <i class="ri ri-user-lock-line me-1"></i>Bloqueados ({{ $securityCounts['usuario_bloqueado'] }})
                    </button>
                </div>
                <div class="col-auto">
                    <button wire:click="$set('securityFilter', 'acceso_no_autorizado')" class="btn {{ $securityFilter === 'acceso_no_autorizado' ? 'btn-dark' : 'btn-outline-dark' }} btn-sm security-btn">
                        <i class="ri ri-prohibited-line me-1"></i>No Autorizados ({{ $securityCounts['acceso_no_autorizado'] }})
                    </button>
                </div>
                <div class="col-auto">
                    <button wire:click="$set('securityFilter', 'restriccion')" class="btn {{ $securityFilter === 'restriccion' ? 'btn-info' : 'btn-outline-info' }} btn-sm security-btn">
                        <i class="ri ri-hand-line me-1"></i>Restricciones ({{ $securityCounts['restriccion'] }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($securityFilter)
    {{-- Security Events Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-danger text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ri ri-shield-check-line me-2"></i>Eventos de Seguridad</h5>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ri ri-download-line me-1"></i>Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item" wire:click="export('csv')"><i class="ri ri-file-text-line me-2"></i>CSV</button></li>
                        <li><button class="dropdown-item" wire:click="export('json')"><i class="ri ri-code-line me-2"></i>JSON</button></li>
                        <li><button class="dropdown-item" wire:click="export('xml')"><i class="ri ri-code-s-slash-line me-2"></i>XML</button></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-danger text-white">
                    <tr>
                        <th><i class="ri ri-time-line me-1"></i>Fecha y Hora</th>
                        <th><i class="ri ri-alarm-warning-line me-1"></i>Tipo de Evento</th>
                        <th><i class="ri ri-file-text-line me-1"></i>Descripción</th>
                        <th><i class="ri ri-user-line me-1"></i>Identificador / Usuario</th>
                        <th class="text-center"><i class="ri ri-ip-line me-1"></i>IP</th>
                        <th class="text-center"><i class="ri ri-eye-line me-1"></i>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($securityEvents as $event)
                        @php
                            $tipoEvento = str_replace('seguridad.', '', $event->action);
                            $badgeColor = match($tipoEvento) {
                                'login_fallido' => 'warning',
                                'usuario_bloqueado' => 'danger',
                                'acceso_no_autorizado' => 'dark',
                                'acceso_denegado' => 'secondary',
                                'acceso_bloqueado' => 'danger',
                                'restriccion' => 'info',
                                default => 'secondary',
                            };
                            $iconoEvento = match($tipoEvento) {
                                'login_fallido' => 'ri-login-circle-line',
                                'usuario_bloqueado' => 'ri-user-lock-line',
                                'acceso_no_autorizado' => 'ri-prohibited-line',
                                'acceso_denegado' => 'ri-close-circle-line',
                                'acceso_bloqueado' => 'ri-lock-line',
                                'restriccion' => 'ri-hand-line',
                                default => 'ri-error-warning-line',
                            };
                            $etiquetaEvento = match($tipoEvento) {
                                'login_fallido' => 'Login Fallido',
                                'usuario_bloqueado' => 'Usuario Bloqueado',
                                'acceso_no_autorizado' => 'Acceso No Autorizado',
                                'acceso_denegado' => 'Acceso Denegado',
                                'acceso_bloqueado' => 'Cuenta Bloqueada',
                                'restriccion' => 'Restricción',
                                default => ucfirst($tipoEvento),
                            };
                        @endphp
                        <tr class="security-row">
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $event->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted"><i class="ri ri-time-line me-1"></i>{{ $event->created_at->format('H:i:s') }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }}">
                                    <i class="{{ $iconoEvento }} me-1"></i>{{ $etiquetaEvento }}
                                </span>
                            </td>
                            <td>{{ $event->metadata['descripcion'] ?? '-' }}</td>
                            <td>
                                @if($event->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-initial rounded-circle bg-danger">{{ substr($event->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $event->user->name }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">
                                        <i class="ri ri-user-search-line me-1"></i>
                                        {{ $event->new_values['identificador'] ?? 'Desconocido' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center"><code>{{ $event->ip_address ?? 'N/A' }}</code></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#securityDetailModal{{ $event->id }}" title="Ver detalles">
                                    <i class="ri ri-eye-line"></i>
                                </button>

                                {{-- Security Detail Modal --}}
                                <div class="modal fade modal-modern" id="securityDetailModal{{ $event->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-danger text-white border-0">
                                                <h5 class="modal-title fw-semibold">
                                                    <i class="{{ $iconoEvento }} me-2"></i>{{ $etiquetaEvento }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body" style="padding:1.5rem;">
                                                {{-- Info Cards --}}
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm h-100">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                                    <div class="rounded-circle p-2">
                                                                        <i class="ri ri-time-line ri-lg text-primary"></i>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-muted d-block">Fecha y Hora</small>
                                                                        <strong class="fs-6">{{ $event->created_at->format('d/m/Y H:i:s') }}</strong>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted ms-5">{{ $event->created_at->diffForHumans() }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm h-100">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                                    <div class="rounded-circle p-2" style="background:#fef3c7;">
                                                                        <i class="ri ri-map-pin-line ri-lg text-warning"></i>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-muted d-block">Origen</small>
                                                                        <p class="mb-0"><strong>IP:</strong> <code class="bg-white px-2 py-1 rounded">{{ $event->ip_address }}</code></p>
                                                                        <p class="mb-0"><strong>MAC:</strong> <code class="bg-white px-2 py-1 rounded">{{ $event->metadata['mac'] ?? 'N/A' }}</code></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Event Details --}}
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header border-0 py-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="rounded-circle p-2">
                                                                <i class="ri ri-information-line ri-lg text-info"></i>
                                                            </div>
                                                            <h6 class="mb-0 fw-semibold">Información del Evento</h6>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover mb-0">
                                                                <tbody>
                                                                    @foreach($event->new_values ?? [] as $key => $value)
                                                                        <tr>
                                                                            <td class="fw-semibold text-muted" style="width: 30%; background:#f8f9fa;">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                                            <td>
                                                                                @if(is_array($value) || is_object($value))
                                                                                    <pre class="mb-0 small p-2 rounded border">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                @else
                                                                                    <span class="text-dark">{{ $value }}</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($event->user_agent)
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-header border-0 py-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="rounded-circle p-2" style="background:#e5e7eb;">
                                                                <i class="ri ri-computer-line ri-lg text-secondary"></i>
                                                            </div>
                                                            <h6 class="mb-0 fw-semibold">User Agent</h6>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <small class="text-muted font-monospace d-block">{{ $event->user_agent }}</small>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="ri ri-close-line me-1"></i>Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ri ri-shield-check-line ri-5x text-muted opacity-25 mb-3 d-block"></i>
                                    <h5 class="text-muted mb-2">No se encontraron eventos de seguridad</h5>
                                    <p class="text-muted mb-3">No hay eventos que coincidan con los filtros actuales.</p>
                                    <button wire:click="clearFilters" class="btn btn-outline-primary btn-sm">
                                        <i class="ri ri-broom-line me-1"></i>Limpiar filtros
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($securityEvents instanceof \Illuminate\Pagination\LengthAwarePaginator && $securityEvents->hasPages())
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted">
                        Mostrando {{ $securityEvents->firstItem() ?? 0 }} a {{ $securityEvents->lastItem() ?? 0 }} de {{ $securityEvents->total() }} eventos
                    </span>
                </div>
                <div class="col-md-6">
                    {{ $securityEvents->links('livewire.pagination') }}
                </div>
            </div>
        </div>
        @endif
    </div>

    @else
    {{-- Main Activities Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ri ri-history-line me-2"></i>Listado de Actividades</h5>
                <div class="d-flex gap-2">
                    @if(count($selectedActivities) > 0)
                        <button class="btn btn-danger btn-sm" wire:click="deleteSelected" wire:confirm="¿Eliminar {{ count($selectedActivities) }} actividades seleccionadas? Esta acción no se puede deshacer.">
                            <i class="ri ri-delete-bin-line me-1"></i>Eliminar ({{ count($selectedActivities) }})
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="50" class="ps-3">
                            <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                        </th>
                        <th wire:click="sort('created_at')" style="cursor: pointer;">
                            <i class="ri ri-time-line me-1"></i>Fecha y Hora
                            @if($sortBy === 'created_at')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th wire:click="sort('causer_id')" style="cursor: pointer;">
                            <i class="ri ri-user-line me-1"></i>Usuario
                            @if($sortBy === 'causer_id')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th wire:click="sort('description')" style="cursor: pointer;">
                            <i class="ri ri-function-line me-1"></i>Acción
                            @if($sortBy === 'description')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th><i class="ri ri-draft-line me-1"></i>Elemento</th>
                        <th class="text-center"><i class="ri ri-ip-line me-1"></i>IP</th>
                        <th class="text-center"><i class="ri ri-eye-line me-1"></i>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr class="activity-row {{ in_array($activity->id, $selectedActivities) ? 'table-primary' : '' }}">
                            <td class="ps-3">
                                <input type="checkbox" wire:model.live="selectedActivities" value="{{ $activity->id }}" class="form-check-input">
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $activity->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted"><i class="ri ri-time-line me-1"></i>{{ $activity->created_at->format('H:i:s') }}</small>
                                </div>
                            </td>
                            <td>
                                @if($activity->causer)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-initial rounded-circle bg-primary">{{ substr($activity->causer->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $activity->causer->name }}</div>
                                            <small class="text-muted">{{ $activity->causer->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-secondary"><i class="ri ri-robot-line me-1"></i>Sistema</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $this->getActionColor($activity->description) }}" title="{{ $activity->description }}" style="max-width:300px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <i class="ri ri-{{ $this->getActionIcon($activity->description) }}-line me-1"></i>
                                    {{ Str::limit(ucfirst($activity->description), 100, '...') }}
                                </span>
                            </td>
                            <td>
                                @if($activity->subject)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-initial rounded bg-label-secondary">{{ substr(class_basename($activity->subject_type), 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ class_basename($activity->subject_type) }}</div>
                                            <small class="text-muted">
                                                @if(isset($activity->subject->name))
                                                    {{ $activity->subject->name }}
                                                @elseif(isset($activity->subject->nombres))
                                                    {{ $activity->subject->nombres }}
                                                @elseif(isset($activity->subject->razon_social))
                                                    {{ $activity->subject->razon_social }}
                                                @else
                                                    ID: {{ $activity->subject_id }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </td>
                            <td class="text-center"><code>{{ $activity->properties->get('ip_address', 'N/A') }}</code></td>
                            <td class="text-center">
                                @if($activity->properties->count() > 0)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#activityDetailsModal{{ $activity->id }}" title="Ver detalles">
                                        <i class="ri ri-eye-line"></i>
                                    </button>

                                    {{-- Activity Detail Modal --}}
                                    <div class="modal fade modal-modern" id="activityDetailsModal{{ $activity->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-primary text-white border-0">
                                                    <h5 class="modal-title fw-semibold">
                                                        <i class="ri ri-information-line me-2"></i>Detalles de la Actividad
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body" style="padding:1.5rem;">
                                                    {{-- Info Cards Row 1 --}}
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-12">
                                                            <div class="card border-0 shadow-sm">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-start gap-3">
                                                                        <div class="rounded-circle p-2 flex-shrink-0">
                                                                            <i class="ri ri-user-line ri-lg text-primary"></i>
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <small class="text-muted d-block mb-1">Usuario</small>
                                                                            @if($activity->causer)
                                                                                <strong class="d-block">{{ $activity->causer->name }}</strong>
                                                                                <small class="text-muted d-block">{{ $activity->causer->email }}</small>
                                                                            @else
                                                                                <span class="badge bg-secondary">Sistema</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="card border-0 shadow-sm">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-start gap-3">
                                                                        <div class="rounded-circle p-2 flex-shrink-0">
                                                                            <i class="ri ri-function-line ri-lg text-info"></i>
                                                                        </div>
                                                                        <div class="flex-grow-1 overflow-hidden">
                                                                            <small class="text-muted d-block mb-1">Acción</small>
                                                                            <span class="badge bg-{{ $this->getActionColor($activity->description) }} d-inline-block mt-1" style="white-space:normal;text-align:left;word-wrap:break-word;overflow-wrap:break-word;max-width:100%;">
                                                                                {{ ucfirst($activity->description) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Info Cards Row 2 --}}
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm h-100">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-start gap-3">
                                                                        <div class="rounded-circle p-2 flex-shrink-0" style="background:#dcfce7;">
                                                                            <i class="ri ri-calendar-line ri-lg text-success"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block mb-1">Fecha</small>
                                                                            <strong class="d-block">{{ $activity->created_at->format('d/m/Y H:i:s') }}</strong>
                                                                            <small class="text-muted d-block">{{ $activity->created_at->diffForHumans() }}</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm h-100">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-start gap-3">
                                                                        <div class="rounded-circle p-2 flex-shrink-0" style="background:#fef3c7;">
                                                                            <i class="ri ri-map-pin-line ri-lg text-warning"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block mb-1">Dirección IP</small>
                                                                            <code class="bg-white px-2 py-1 rounded d-inline-block mt-1">{{ $activity->properties->get('ip_address', 'N/A') }}</code>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Changes Detail Card --}}
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-header border-0 py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="rounded-circle p-2" style="background:#e5e7eb;">
                                                                    <i class="ri ri-settings-3-line ri-lg text-secondary"></i>
                                                                </div>
                                                                <h6 class="mb-0 fw-semibold">Detalle de Cambios</h6>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            @if($activity->properties->has('attributes') && $activity->properties->has('old'))
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-hover mb-0">
                                                                        <thead >
                                                                            <tr>
                                                                                <th class="text-muted" style="width:30%">Campo</th>
                                                                                <th class="text-danger" style="width:35%">Valor Anterior</th>
                                                                                <th class="text-success" style="width:35%">Nuevo Valor</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($activity->properties['attributes'] as $key => $value)
                                                                                @if(!in_array($key, ['updated_at', 'created_at', 'deleted_at']))
                                                                                <tr>
                                                                                    <td class="fw-semibold text-muted" style="word-wrap:break-word;">{{ ucfirst(Str::limit(str_replace('_', ' ', $key), 30, '...')) }}</td>
                                                                                    <td class="text-danger" style="word-wrap:break-word;vertical-align:top;">
                                                                                        @if(isset($activity->properties['old'][$key]))
                                                                                            @if(is_array($activity->properties['old'][$key]) || is_object($activity->properties['old'][$key]))
                                                                                                <pre class="mb-0 small p-2 rounded border bg-light" style="max-height:80px;overflow:auto;font-size:0.85rem;">{{ json_encode($activity->properties['old'][$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                            @else
                                                                                                <span class="text-danger d-inline-block" style="max-width:100%;word-wrap:break-word;" title="{{ $activity->properties['old'][$key] }}">{{ Str::limit($activity->properties['old'][$key], 80, '...') }}</span>
                                                                                            @endif
                                                                                        @else
                                                                                            <span class="text-muted fst-italic">No disponible</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-success" style="word-wrap:break-word;vertical-align:top;">
                                                                                        @if(is_array($value) || is_object($value))
                                                                                            <pre class="mb-0 small p-2 rounded border bg-light" style="max-height:80px;overflow:auto;font-size:0.85rem;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                        @else
                                                                                            <span class="text-success d-inline-block" style="max-width:100%;word-wrap:break-word;" title="{{ $value }}">{{ Str::limit($value, 50, '...') }}</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                                @endif
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @elseif($activity->properties->has('attributes'))
                                                                <div class="p-3">
                                                                    <h6 class="text-primary mb-3"><i class="ri ri-draft-line me-2"></i>Datos del Registro:</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-hover mb-0">
                                                                            <tbody>
                                                                                @foreach($activity->properties['attributes'] as $key => $value)
                                                                                    @if(!in_array($key, ['updated_at', 'created_at', 'deleted_at']))
                                                                                    <tr>
                                                                                        <td class="fw-semibold text-muted" style="width:30%;word-wrap:break-word;">{{ ucfirst(Str::limit(str_replace('_', ' ', $key), 30, '...')) }}</td>
                                                                                        <td style="word-wrap:break-word;vertical-align:top;">
                                                                                            @if(is_array($value) || is_object($value))
                                                                                                <pre class="mb-0 small p-2 rounded border bg-light" style="max-height:80px;overflow:auto;font-size:0.85rem;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                            @else
                                                                                                <span class="text-dark d-inline-block" style="max-width:100%;word-wrap:break-word;" title="{{ $value }}">{{ Str::limit($value, 80, '...') }}</span>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>
                                                                                    @endif
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="text-center py-5 text-muted">
                                                                    <i class="ri ri-information-line ri-4x opacity-25 mb-3 d-block"></i>
                                                                    <p class="mb-0 fs-5">No hay detalles adicionales disponibles para esta actividad.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="ri ri-close-line me-1"></i>Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Sin detalles</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ri ri-history-line ri-5x text-muted opacity-25 mb-3 d-block"></i>
                                    <h5 class="text-muted mb-2">No hay actividades registradas</h5>
                                    <p class="text-muted mb-3">No se encontraron actividades con los filtros actuales.</p>
                                    <button wire:click="clearFilters" class="btn btn-outline-primary btn-sm">
                                        <i class="ri ri-broom-line me-1"></i>Limpiar filtros
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted">
                        Mostrando {{ $activities->firstItem() ?? 0 }} a {{ $activities->lastItem() ?? 0 }} de {{ $activities->total() }} actividades
                    </span>
                </div>
                <div class="col-md-6">
                    {{ $activities->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('showToast', (event) => {
            if (typeof showToast === 'function') {
                showToast(event[0].type, event[0].message);
            }
        });
    });
</script>
@endpush
