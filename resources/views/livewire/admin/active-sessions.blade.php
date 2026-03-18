<div>
    <!-- Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded">
                                <i class="ri ri-group-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ format_money($stats['total']) }}</h3>
                            <small class="text-muted">Total Sesiones</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-success bg-opacity-10 text-success rounded">
                                <i class="ri ri-door-open-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ format_money($stats['active']) }}</h3>
                            <small class="text-muted">Sesiones Activas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-info bg-opacity-10 text-info rounded">
                                <i class="ri ri-computer-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ format_money($stats['current']) }}</h3>
                            <small class="text-muted">Sesión Actual</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-warning bg-opacity-10 text-warning rounded">
                                <i class="ri ri-smartphone-line ri-24px"></i>
                            </div>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ format_money($stats['mobile']) }}</h3>
                            <small class="text-muted">Dispositivos Móviles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="ri ri-list-check ri-20px me-2"></i>
                                Gestión de Sesiones Activas
                            </h5>
                            <p class="mb-0">Administra y monitorea las sesiones activas del sistema</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmBulkTerminate()" @if(count($selectedSessions) === 0) disabled @endif>
                                <i class="ri ri-stop-circle-line"></i> Terminar Seleccionadas
                            </button>
                            <div class="dropdown">
                                <button type="button" class="btn btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri ri-download-line ri-16px me-1"></i>
                                    Exportar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item" wire:click="exportSessions('csv')">
                                            <i class="ri ri-file-text-line ri-16px me-2"></i>CSV
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="exportSessions('json')">
                                            <i class="ri ri-code-line ri-16px me-2"></i>JSON
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="exportSessions('xml')">
                                            <i class="ri ri-code-s-slash-line ri-16px me-2"></i>XML
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-light" wire:click="loadSessions" 
                                    data-bs-toggle="tooltip" title="Actualizar lista">
                                <i class="ri ri-refresh-line ri-16px"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros mejorados -->
                <div class="card-header bg-light">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri ri-search-line me-2"></i>Buscar
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Usuario, IP, ubicación, dispositivo..."
                                       wire:model.live.debounce.300ms="search">
                                @if($search)
                                    <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                                        <i class="ri ri-close-line"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                <i class="ri ri-filter-line me-2"></i>Estado
                            </label>
                            <select class="form-select" wire:model.live="status">
                                <option value="">Todos</option>
                                <option value="active">Activas</option>
                                <option value="inactive">Inactivas</option>
                                <option value="current">Sesión actual</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                <i class="ri ri-device-line me-2"></i>Dispositivo
                            </label>
                            <select class="form-select" wire:model.live="deviceType">
                                <option value="">Todos</option>
                                <option value="mobile">Móvil</option>
                                <option value="tablet">Tablet</option>
                                <option value="desktop">Escritorio</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                <i class="ri ri-list-check me-2"></i>Mostrar
                            </label>
                            <select class="form-select" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters" 
                                    data-bs-toggle="tooltip" title="Limpiar todos los filtros">
                                <i class="ri ri-broom-line"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Indicadores de filtros activos -->
                    @if($search || $status || $deviceType)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="text-muted">Filtros activos:</span>
                                    @if($search)
                                        <span class="badge bg-primary">Búsqueda: {{ $search }}</span>
                                    @endif
                                    @if($status)
                                        <span class="badge bg-success">Estado: {{ ucfirst($status) }}</span>
                                    @endif
                                    @if($deviceType)
                                        <span class="badge bg-info">Dispositivo: {{ ucfirst($deviceType) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tabla mejorada -->
                <div class="card-datatable table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" wire:model.live="selectAll" 
                                           class="form-check-input">
                                </th>
                                <th wire:click="sortBy('user.name')" style="cursor: pointer;" class="fw-bold">
                                    <i class="ri ri-user-line me-2"></i>Usuario 
                                    @if($sortBy === 'user.name') 
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> 
                                    @endif
                                </th>
                                <th wire:click="sortBy('user_agent')" style="cursor: pointer;" class="fw-bold">
                                    <i class="ri ri-device-line me-2"></i>Dispositivo 
                                    @if($sortBy === 'user_agent') 
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> 
                                    @endif
                                </th>
                                <th wire:click="sortBy('ip_address')" style="cursor: pointer;" class="fw-bold">
                                    <i class="ri ri-global-line me-2"></i>IP 
                                    @if($sortBy === 'ip_address') 
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> 
                                    @endif
                                </th>
                                <th class="fw-bold">
                                    <i class="ri ri-map-pin-line me-2"></i>Ubicación
                                </th>
                                <th wire:click="sortBy('last_activity')" style="cursor: pointer;" class="fw-bold">
                                    <i class="ri ri-time-line me-2"></i>Última Actividad 
                                    @if($sortBy === 'last_activity') 
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> 
                                    @endif
                                </th>
                                <th class="fw-bold">
                                    <i class="ri ri-information-line me-2"></i>Estado
                                </th>
                                <th class="text-center fw-bold">
                                    <i class="ri ri-settings-line me-2"></i>Acciones
                                </th>
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