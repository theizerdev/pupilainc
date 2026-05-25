<div class="w-100">
    @section('title', 'Sesiones Activas')

    @push('styles')
    <style>
        .sessions-hero { background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .sessions-hero h2 { color:#fff; margin:0; }
        .sessions-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .session-row { transition:background .12s; }
        .session-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="sessions-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-group-line me-2"></i>Sesiones Activas</h2>
            <p class="mt-1">Administra y monitorea las sesiones activas del sistema</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm" wire:click="loadSessions">
                <i class="ri ri-refresh-line me-1"></i>Actualizar
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-group-line"></i></div>
                <div>
                    <div class="stat-label">Total sesiones</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-door-open-line"></i></div>
                <div>
                    <div class="stat-label">Sesiones activas</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-computer-line"></i></div>
                <div>
                    <div class="stat-label">Sesión actual</div>
                    <div class="stat-value">{{ $stats['current'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-smartphone-line"></i></div>
                <div>
                    <div class="stat-label">Dispositivos móviles</div>
                    <div class="stat-value">{{ $stats['mobile'] }}</div>
                </div>
            </div>
        </div>


    {{-- Main Card with Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ri ri-list-check me-2"></i>Listado de sesiones</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmBulkTerminate()" @if(count($selectedSessions) === 0) disabled @endif>
                        <i class="ri ri-stop-circle-line me-1"></i>Terminar Seleccionadas
                    </button>
                    <div class="dropdown">
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri ri-download-line me-1"></i>Exportar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item" wire:click="exportSessions('csv')"><i class="ri ri-file-text-line me-2"></i>CSV</button></li>
                            <li><button class="dropdown-item" wire:click="exportSessions('json')"><i class="ri ri-code-line me-2"></i>JSON</button></li>
                            <li><button class="dropdown-item" wire:click="exportSessions('xml')"><i class="ri ri-code-s-slash-line me-2"></i>XML</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact Filters --}}
        <div class="card-body border-bottom">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-search-line me-1"></i>Buscar
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Usuario, IP, ubicación..." wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                                <i class="ri ri-close-line"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-filter-line me-1"></i>Estado
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="status">
                        <option value="">Todos</option>
                        <option value="active">Activas</option>
                        <option value="inactive">Inactivas</option>
                        <option value="current">Sesión actual</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-device-line me-1"></i>Dispositivo
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="deviceType">
                        <option value="">Todos</option>
                        <option value="mobile">Móvil</option>
                        <option value="tablet">Tablet</option>
                        <option value="desktop">Escritorio</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="ri ri-list-check me-1"></i>Mostrar
                    </label>
                    <select class="form-select form-select-sm" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                        <i class="ri ri-broom-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

        {{-- Main Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="50" class="ps-3">
                            <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                        </th>
                        <th wire:click="sortBy('user.name')" style="cursor: pointer;">
                            <i class="ri ri-user-line me-1"></i>Usuario
                            @if($sortBy === 'user.name')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('user_agent')" style="cursor: pointer;">
                            <i class="ri ri-device-line me-1"></i>Dispositivo
                            @if($sortBy === 'user_agent')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('ip_address')" style="cursor: pointer;">
                            <i class="ri ri-global-line me-1"></i>IP
                            @if($sortBy === 'ip_address')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th><i class="ri ri-map-pin-line me-1"></i>Ubicación</th>
                        <th wire:click="sortBy('last_activity')" style="cursor: pointer;">
                            <i class="ri ri-time-line me-1"></i>Última Actividad
                            @if($sortBy === 'last_activity')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                            @endif
                        </th>
                        <th><i class="ri ri-information-line me-1"></i>Estado</th>
                        <th class="text-center"><i class="ri ri-settings-line me-1"></i>Acciones</th>
                    </tr>
                </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr @if($session->is_current) class="table-info" @endif>
                                    <td>
                                        @if(!$session->is_current)
                                            <input type="checkbox" wire:model="selectedSessions"
                                                   value="{{ $session->id }}" class="form-check-input">
                                        @else
                                            <span class="text-muted" title="Sesión actual">
                                                <i class="ri ri-star-line"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-primary">
                                                        {{ substr($session->user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $session->user->name }}</h6>
                                                    <small class="text-muted">{{ $session->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="ri ri-user-unfollow-line me-1"></i>Usuario no encontrado
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-secondary">
                                                    @if(str_contains(strtolower($session->user_agent), 'mobile'))
                                                        <i class="ri ri-smartphone-line"></i>
                                                    @elseif(str_contains(strtolower($session->user_agent), 'tablet'))
                                                        <i class="ri ri-tablet-line"></i>
                                                    @else
                                                        <i class="ri ri-computer-line"></i>
                                                    @endif
                                                </span>
                                            </div>
                                            <div>
                                                @if(str_contains(strtolower($session->user_agent), 'mobile'))
                                                    <span class="badge bg-success">Móvil</span>
                                                @elseif(str_contains(strtolower($session->user_agent), 'tablet'))
                                                    <span class="badge bg-info">Tablet</span>
                                                @else
                                                    <span class="badge bg-primary">Escritorio</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    {{ Str::limit($session->user_agent, 30) }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-global-line me-2 text-primary"></i>
                                            <span class="font-monospace">{{ $session->ip_address }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->location)
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-map-pin-line me-2 text-danger"></i>
                                                <span>{{ $session->location }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="ri ri-question-line me-1"></i>Desconocida
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->last_activity)
                                            <div class="d-flex flex-column">
                                                <span>{{ $session->last_activity->diffForHumans() }}</span>
                                                <small class="text-muted">
                                                    {{ $session->last_activity->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="ri ri-time-line me-1"></i>Desconocida
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->is_current)
                                            <span class="badge bg-info text-dark" title="Esta es tu sesión actual">
                                                <i class="ri ri-star-line me-1"></i>Sesión Actual
                                            </span>
                                        @elseif($session->is_active)
                                            <span class="badge bg-success" title="Sesión activa">
                                                <i class="ri ri-door-open-line me-1"></i>Activa
                                            </span>
                                        @else
                                            <span class="badge bg-secondary" title="Sesión inactiva">
                                                <i class="ri ri-close-circle-line me-1"></i>Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$session->is_current)
                                            <button class="btn btn-sm btn-danger"
                                                    wire:click="terminateSession('{{ $session->id }}')"
                                                    wire:confirm="¿Estás seguro de terminar esta sesión?"
                                                    data-bs-toggle="tooltip" title="Terminar sesión">
                                                <i class="ri ri-logout-box-line"></i>
                                            </button>
                                        @else
                                            <span class="text-muted" title="Sesión actual">
                                                <i class="ri ri-home-line"></i>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri ri-user-unfollow-line ri-3x mb-3"></i>
                                            <h5>No se encontraron sesiones activas</h5>
                                            <p>No hay sesiones que coincidan con los filtros aplicados.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación mejorada -->
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">Mostrar:</span>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-muted ms-3">
                                    Mostrando {{ $sessions->firstItem() }} a {{ $sessions->lastItem() }} de {{ $sessions->total() }} resultados
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $sessions->links('livewire.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>

@script
<script>
    // Código JavaScript para el componente
    document.addEventListener('livewire:init', function () {
        // Inicializar tooltips de Bootstrap 5
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Escuchar eventos de Livewire para notificaciones
        Livewire.on('showToast', (event) => {
            if (typeof showToast === 'function') {
                showToast(event[0].type, event[0].message);
            } else {
                // Fallback si no existe la función showToast
                alert(event[0].message);
            }
        });

        // Evento para filtros limpiados
        Livewire.on('filtersCleared', () => {
            if (typeof showToast === 'function') {
                showToast('success', 'Filtros limpiados correctamente');
            }
        });

        // Evento para sesión terminada
        Livewire.on('sessionTerminated', () => {
            if (typeof showToast === 'function') {
                showToast('success', 'Sesión terminada exitosamente');
            }
        });

        // Evento para sesiones terminadas masivamente
        Livewire.on('sessionsTerminated', (event) => {
            const count = event[0].count;
            if (typeof showToast === 'function') {
                showToast('success', `${count} sesiones terminadas exitosamente`);
            }
        });

        // Evento para exportación
        Livewire.on('sessionsExported', (event) => {
            const format = event[0].format;
            if (typeof showToast === 'function') {
                showToast('success', `Sesiones exportadas en formato ${format.toUpperCase()}`);
            }
        });

        // Auto-refresh cada 30 segundos
        let refreshInterval = setInterval(() => {
            Livewire.dispatch('refreshSessions');
        }, 30000);

        // Limpiar intervalo cuando se desmonta el componente
        Livewire.on('destroy', () => {
            clearInterval(refreshInterval);
        });
    });

    // Función para confirmar eliminación masiva
    function confirmBulkTerminate() {
        const selectedCount = document.querySelectorAll('input[type="checkbox"]:checked:not([wire\:model="selectAll"])').length;

        if (selectedCount === 0) {
            if (typeof showToast === 'function') {
                showToast('warning', 'Por favor selecciona al menos una sesión para terminar');
            }
            return;
        }

        if (confirm(`¿Estás seguro de terminar ${selectedCount} sesión(es) seleccionada(s)?`)) {
            @this.bulkTerminateSessions();
        }
    }

    // Función para exportar sesiones
    function exportSessions(format) {
        @this.exportSessions(format);
    }
</script>
@endscript
