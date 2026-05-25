<div>
    @section('title', 'Baremos')

    @push('styles')
    <style>
        .baremo-hero { background: linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .baremo-hero h2 { color:#fff; margin:0; }
        .baremo-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .baremo-row { transition:background .12s; }
        .baremo-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Baremos</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="baremo-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-stethoscope-line me-2"></i>Baremos</h2>
                <p class="mt-1">Gestión de servicios y procedimientos médicos</p>
            </div>
            @can('create baremos')
                <a href="{{ route('admin.baremos.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nuevo Servicio
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-stethoscope-line"></i></div>
                    <div>
                        <div class="stat-label">Total servicios</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Servicios activos</div>
                        <div class="stat-value">{{ $stats['activos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Servicios inactivos</div>
                        <div class="stat-value">{{ $stats['inactivos'] }}</div>
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
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, código o descripción...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Categoría</label>
                        <select class="form-select form-select-sm" wire:model.live="categoria_id">
                            <option value="">Todas</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="activo">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="$set('search', ''); $set('categoria_id', ''); $set('activo', '')" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de servicios</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none text-primary">
                                        Código
                                        @if($sortField === 'codigo')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('nombre_servicio')" href="#" class="text-decoration-none text-primary">
                                        Servicio
                                        @if($sortField === 'nombre_servicio')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Categoría</th>
                                <th class="fw-semibold text-end">Precio</th>
                                <th class="fw-semibold text-center">IVA</th>
                                <th class="fw-semibold text-center">Estado</th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($baremos as $baremo)
                                <tr class="baremo-row">
                                    <td>
                                        <span class="badge bg-label-dark">{{ $baremo->codigo ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $baremo->nombre_servicio }}</div>
                                            @if($baremo->descripcion)
                                                <small class="text-muted">{{ Str::limit($baremo->descripcion, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($baremo->categoria)
                                            <span class="badge bg-label-info">{{ $baremo->categoria->nombre }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-primary">{{ money($baremo->costo_usd, 2) }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($baremo->aplica_iva)
                                            <span class="badge bg-label-success">Sí</span>
                                        @else
                                            <span class="badge bg-label-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $baremo->id }}"
                                                   wire:click="toggleEstado({{ $baremo->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $baremo->activo ? 'desactivar' : 'activar' }} este servicio?"
                                                   {{ $baremo->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('access baremos')
                                                <a class="dropdown-item" href="{{ route('admin.baremos.show', $baremo) }}">
                                                    <i class="ri ri-eye-line me-1 text-info"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit baremos')
                                                <a class="dropdown-item" href="{{ route('admin.baremos.edit', $baremo) }}">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete baremos')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteBaremo({{ $baremo->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este servicio?">
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
                                        <i class="ri ri-stethoscope-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron servicios</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $baremos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
