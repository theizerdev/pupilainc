<div>
    @section('title', 'Sucursales')

    @push('styles')
    <style>
        .sucursal-hero { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .sucursal-hero h2 { color:#fff; margin:0; }
        .sucursal-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .sucursal-row { transition:background .12s; }
        .sucursal-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Sucursales</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="sucursal-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-building-2-line me-2"></i>Sucursales</h2>
                <p class="mt-1">Gestión de sucursales del sistema</p>
            </div>
            @can('create sucursales')
                <a href="{{ route('admin.sucursales.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Sucursal
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-building-2-line"></i></div>
                    <div>
                        <div class="stat-label">Total sucursales</div>
                        <div class="stat-value">{{ $totalSucursales }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Sucursales activas</div>
                        <div class="stat-value">{{ $sucursalesActivas }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Sucursales inactivas</div>
                        <div class="stat-value">{{ $sucursalesInactivas }}</div>
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
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, teléfono, dirección...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Empresa</label>
                        <select class="form-select form-select-sm" wire:model.live="empresa_id">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Mostrar</label>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="clearFilters">
                                <i class="ri ri-eraser-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-label-success" wire:click="export">
                                <i class="ri ri-file-excel-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-building-2-line me-2 text-primary"></i>Listado de sucursales</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                    Nombre
                                    @if($sortBy === 'nombre')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th>Empresa</th>
                                <th wire:click="sortBy('telefono')" style="cursor: pointer;">
                                    Teléfono
                                    @if($sortBy === 'telefono')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th>Dirección</th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                    Fecha de Registro
                                    @if($sortBy === 'created_at')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('status')" style="cursor: pointer;">
                                    Estado
                                    @if($sortBy === 'status')
                                        <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i>
                                    @endif
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sucursales as $sucursal)
                            <tr class="sucursal-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 d-flex align-items-center justify-content-center"
                                                 style="width: 42px; height: 42px; background-color: {{ $sucursal->empresa ? '#dbeafe' : '#f3f4f6' }}; border-radius: 8px;">
                                                <i class="ri ri-building-2-line text-primary fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $sucursal->nombre }}</div>
                                                <small class="text-muted">{{ $sucursal->empresa->razon_social ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $sucursal->telefono ?? 'No especificado' }}</span>
                                    </td>
                                    <td>
                                        @if($sucursal->direccion)
                                            <span data-bs-toggle="tooltip" data-bs-title="{{ $sucursal->direccion }}">
                                                {{ Str::limit($sucursal->direccion, 50) }}
                                            </span>
                                        @else
                                            <span class="text-muted">No especificada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $sucursal->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $sucursal->id }}"
                                                   wire:click="toggleStatus({{ $sucursal->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $sucursal->status ? 'desactivar' : 'activar' }} esta sucursal?"
                                                   {{ $sucursal->status ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('access sucursales')
                                                <a class="dropdown-item" href="{{ route('admin.sucursales.show', $sucursal->id) }}">
                                                    <i class="ri ri-eye-line me-1 text-info"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit sucursales')
                                                <a class="dropdown-item" href="{{ route('admin.sucursales.edit', $sucursal->id) }}">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete sucursales')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $sucursal->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta sucursal?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="ri ri-building-2-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron sucursales</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $sucursales->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
