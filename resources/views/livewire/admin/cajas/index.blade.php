<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Cajas</h6>
                            <h2 class="mb-0">{{ format_money($this->stats['total'], false) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="ri ri-safe-line text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Cajas Abiertas</h6>
                            <h2 class="mb-0">{{ format_money($this->stats['abiertas'], false) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="ri ri-lock-unlock-line text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Cajas Cerradas</h6>
                            <h2 class="mb-0">{{ format_money($this->stats['cerradas'], false) }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="ri ri-lock-line text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Ingresos Hoy</h6>
                            <h2 class="mb-0"><x-dual-currency :amount="$this->stats['ingresos_hoy']" /></h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="ri ri-money-dollar-circle-line text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Lista de Cajas</h5>
                            <p class="mb-0">Administra las cajas diarias del sistema</p>
                        </div>
                        @can('create cajas')
                        <div>
                            <a href="{{ route('admin.cajas.create') }}" class="btn btn-primary">
                                <i class="ri ri-add-line"></i> Abrir Caja
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card-header border-bottom">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" placeholder="Fecha o usuario..."
                                   wire:model.live.debounce.300ms="search">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" wire:model.live="status">
                                <option value="">Todos los estados</option>
                                <option value="abierta">Abierta</option>
                                <option value="cerrada">Cerrada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mostrar</label>
                            <select class="form-select" wire:model.live="perPage">
                                <option value="10">10 por página</option>
                                <option value="25">25 por página</option>
                                <option value="50">50 por página</option>
                                <option value="100">100 por página</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-label-secondary" wire:click="clearFilters">
                                <i class="ri ri-eraser-line"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-label-success" wire:click="export">
                                <i class="mdi mdi-file-excel"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-datatable table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('fecha')" style="cursor: pointer;">
                                    Fecha
                                    @if($sortBy === 'fecha')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th>Usuario</th>
                                <th>Monto Inicial</th>
                                <th>Total Ingresos</th>
                                <th>Monto Final</th>
                                <th wire:click="sortBy('estado')" style="cursor: pointer;">
                                    Estado
                                    @if($sortBy === 'estado')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cajas as $caja)
                                <tr>
                                    <td>
                                        <div>{{ format_date($caja->fecha) }}</div>
                                        <small class="text-muted">{{ $caja->fecha_apertura->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">{{ substr($caja->usuario->name ?? '', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $caja->usuario->name ?? '' }}</h6>
                                                <small class="text-muted">{{ $caja->usuario->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>@money($caja->monto_inicial)</td>
                                    <td>
                                        <div class="fw-semibold">@money($caja->total_ingresos)</div>
                                        <small class="text-muted d-inline">
                                            E: @money($caja->total_efectivo) |
                                            T: @money($caja->total_transferencias) |
                                            TC: @money($caja->total_tarjeta_credito) |
                                            TD: @money($caja->total_tarjeta_debito)
                                        </small>
                                    </td>
                                    <td>@money($caja->monto_final_ajustado)</td>
                                    <td>
                                        @if($caja->estado === 'abierta')
                                            <span class="badge bg-success">Abierta</span>
                                        @else
                                            <span class="badge bg-secondary">Cerrada</span>
                                            @if($caja->fecha_cierre)
                                                <small class="d-block text-muted">{{ format_datetime($caja->fecha_cierre, false) }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('view cajas')
                                                <a class="dropdown-item" href="{{ route('admin.cajas.show', $caja) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver Detalle
                                                </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron cajas que coincidan con los filtros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="card-footer">
                   {{ $cajas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
