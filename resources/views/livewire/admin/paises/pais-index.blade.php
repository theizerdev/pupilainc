<div>
    @section('title', 'Países')

    @push('styles')
    <style>
        .pais-hero { background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .pais-hero h2 { color:#fff; margin:0; }
        .pais-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .pais-row { transition:background .12s; }
        .pais-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Países</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="pais-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-earth-line me-2"></i>Países</h2>
                <p class="mt-1">Gestión de países y configuraciones geográficas</p>
            </div>
            @can('create paises')
                <a href="{{ route('admin.paises.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nuevo País
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-earth-line"></i></div>
                    <div>
                        <div class="stat-label">Total países</div>
                        <div class="stat-value">{{ $totalPaises }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Países activos</div>
                        <div class="stat-value">{{ $paisesActivos }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Países inactivos</div>
                        <div class="stat-value">{{ $paisesInactivos }}</div>
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
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, código ISO, moneda...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="activo">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
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
                <h6 class="mb-0"><i class="ri ri-earth-line me-2 text-primary"></i>Listado de países</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                    Nombre @if($sortBy === 'nombre') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('codigo_iso2')" style="cursor: pointer;">
                                    Código ISO @if($sortBy === 'codigo_iso2') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('moneda_principal')" style="cursor: pointer;">
                                    Moneda @if($sortBy === 'moneda_principal') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('continente')" style="cursor: pointer;">
                                    Continente @if($sortBy === 'continente') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th>
                                    Coordenadas
                                </th>
                                <th wire:click="sortBy('activo')" style="cursor: pointer;">
                                    Estado @if($sortBy === 'activo') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paises as $pais)
                                <tr class="pais-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 d-flex align-items-center justify-content-center"
                                                 style="width: 42px; height: 42px; background-color: {{ $pais->activo ? '#dbeafe' : '#f3f4f6' }}; border-radius: 8px;">
                                                <i class="ri ri-earth-line text-primary fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $pais->nombre }}</div>
                                                <small class="text-muted">{{ $pais->codigo_iso2 }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $pais->codigo_iso2 }} / {{ $pais->codigo_iso3 }}</span>
                                    </td>
                                    <td>{{ $pais->moneda_principal }}</td>
                                    <td>{{ $pais->continente }}</td>
                                    <td>
                                        @if($pais->tieneCoordenadas())
                                            <span class="badge bg-success bg-opacity-10 text-success" title="Lat: {{ $pais->latitud }}, Lng: {{ $pais->longitud }}">
                                                <i class="ri ri-map-pin-line me-1"></i>Sí
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <i class="ri ri-map-pin-line me-1"></i>No
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $pais->id }}"
                                                   wire:click="toggleStatus({{ $pais->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $pais->activo ? 'desactivar' : 'activar' }} este país?"
                                                   {{ $pais->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('edit paises')
                                                <a class="dropdown-item" href="{{ route('admin.paises.edit', $pais) }}">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete paises')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deletePais({{ $pais->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este país?">
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
                                        <i class="ri ri-earth-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron países</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $paises->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
