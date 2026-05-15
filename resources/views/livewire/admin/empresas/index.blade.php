<div>
    @section('title', 'Empresas')

    @push('styles')
    <style>
        .empresa-hero { background: linear-gradient(135deg, #3B82F6 0%, #8B5CF6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .empresa-hero h2 { color:#fff; margin:0; }
        .empresa-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .empresa-row { transition:background .12s; }
        .empresa-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Empresas</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="empresa-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-building-line me-2"></i>Empresas</h2>
                <p class="mt-1">Gestión de empresas del sistema</p>
            </div>
            @can('create empresas')
                <a href="{{ route('admin.empresas.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Empresa
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-building-line"></i></div>
                    <div>
                        <div class="stat-label">Total empresas</div>
                        <div class="stat-value">{{ $totalEmpresas }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Empresas activas</div>
                        <div class="stat-value">{{ $empresasActivas }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Empresas inactivas</div>
                        <div class="stat-value">{{ $empresasInactivas }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, RUC, email...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="activo">Activas</option>
                            <option value="inactivo">Inactivas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Mostrar</label>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="clearFilters">
                                <i class="ri ri-eraser-line"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-label-success" wire:click="export">
                                <i class="ri ri-file-excel-line"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-building-line me-2 text-primary"></i>Listado de empresas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                    Nombre @if($sortBy === 'nombre') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('ruc')" style="cursor: pointer;">
                                    Documento @if($sortBy === 'ruc') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('email')" style="cursor: pointer;">
                                    Email @if($sortBy === 'email') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('telefono')" style="cursor: pointer;">
                                    Teléfono @if($sortBy === 'telefono') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('estado')" style="cursor: pointer;">
                                    Estado @if($sortBy === 'estado') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                    Creado @if($sortBy === 'created_at') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empresas as $empresa)
                                <tr class="empresa-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">{{ substr($empresa->nombre, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $empresa->nombre }}</h6>
                                                <small class="text-muted">{{ $empresa->razon_social ?? $empresa->nombre }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $empresa->documento }}</span>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $empresa->telefono }}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $empresa->id }}"
                                                   {{ $empresa->status ? 'checked' : '' }}
                                                   @can('edit empresas') wire:click="toggleStatus({{ $empresa->id }})" @endcan>
                                            <label class="form-check-label" for="statusSwitch{{ $empresa->id }}">
                                                {{ $empresa->status ? 'Activo' : 'Inactivo' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $empresa->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">

                                                @can('edit empresas')
                                                <a class="dropdown-item" href="{{ route('admin.empresas.edit', $empresa) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete empresas')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteEmpresa({{ $empresa->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta empresa?">
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
                                        <i class="ri ri-building-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron empresas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $empresas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
