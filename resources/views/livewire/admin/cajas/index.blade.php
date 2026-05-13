<div>
    @section('title', 'Gestión de Cajas')

    @push('styles')
    <style>
        .cajas-hero { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .cajas-hero h2 { color:#fff; margin:0; }
        .cajas-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .caja-row { transition:background .12s; }
        .caja-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    {{-- Alertas --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri ri-check-line me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Cajas</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="cajas-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-safe-line me-2"></i>Gestión de Cajas</h2>
                <p class="mt-1">Administra las cajas diarias del sistema</p>
            </div>
            @can('create cajas')
                <a href="{{ route('admin.cajas.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Abrir Caja
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-safe-line"></i></div>
                    <div>
                        <div class="stat-label">Total cajas</div>
                        <div class="stat-value">{{ $this->stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-lock-unlock-line"></i></div>
                    <div>
                        <div class="stat-label">Cajas abiertas</div>
                        <div class="stat-value">{{ $this->stats['abiertas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-lock-line"></i></div>
                    <div>
                        <div class="stat-label">Cajas cerradas</div>
                        <div class="stat-value">{{ $this->stats['cerradas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Ingresos hoy</div>
                        <div class="stat-value"><x-dual-currency :amount="$this->stats['ingresos_hoy']" /></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Fecha o usuario...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="abierta">Abierta</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Mostrar</label>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                            <i class="ri ri-refresh-line"></i> Limpiar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success btn-sm w-100" wire:click="export">
                            <i class="ri ri-file-excel-line"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de cajas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('fecha')" href="#" class="text-decoration-none text-primary">
                                        Fecha
                                        @if($sortBy === 'fecha')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Usuario</th>
                                <th class="fw-semibold text-end">Monto Inicial</th>
                                <th class="fw-semibold text-end">Total Ingresos</th>
                                <th class="fw-semibold text-end">Monto Final</th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('estado')" href="#" class="text-decoration-none text-primary">
                                        Estado
                                        @if($sortBy === 'estado')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cajas as $caja)
                                <tr class="caja-row">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ format_date($caja->fecha) }}</span>
                                            <small class="text-muted">{{ $caja->fecha_apertura->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">{{ substr($caja->usuario->name ?? '', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $caja->usuario->name ?? '' }}</div>
                                                <small class="text-muted">{{ $caja->usuario->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium">@money($caja->monto_inicial)</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-primary">@money($caja->total_ingresos)</div>
                                        <small class="text-muted d-block">
                                            E: @money($caja->total_efectivo) | T: @money($caja->total_transferencias)
                                        </small>
                                        <small class="text-muted d-block">
                                            TC: @money($caja->total_tarjeta_credito) | TD: @money($caja->total_tarjeta_debito)
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold">@money($caja->monto_final_ajustado)</span>
                                    </td>
                                    <td>
                                        @if($caja->estado === 'abierta')
                                            <span class="badge bg-label-success">Abierta</span>
                                        @else
                                            <span class="badge bg-label-secondary">Cerrada</span>
                                            @if($caja->fecha_cierre)
                                                <small class="d-block text-muted">{{ format_datetime($caja->fecha_cierre, false) }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('view cajas')
                                                <a class="dropdown-item" href="{{ route('admin.cajas.show', $caja) }}">
                                                    <i class="ri ri-eye-line me-1 text-info"></i> Ver Detalle
                                                </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="ri ri-safe-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron cajas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $cajas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
