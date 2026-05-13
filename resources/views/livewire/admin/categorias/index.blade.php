<div>
    @section('title', 'Categorías')

    @push('styles')
    <style>
        .cat-hero { background: linear-gradient(135deg, #F59E0B 0%, #EF4444 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .cat-hero h2 { color:#fff; margin:0; }
        .cat-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .categoria-row { transition:background .12s; }
        .categoria-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Categorías</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="cat-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-price-tag-3-line me-2"></i>Categorías</h2>
                <p class="mt-1">Gestión de categorías y clasificación de elementos</p>
            </div>
            @can('create categorias')
                <a href="{{ route('admin.categorias.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Categoría
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-price-tag-3-line"></i></div>
                    <div>
                        <div class="stat-label">Total categorías</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Categorías activas</div>
                        <div class="stat-value">{{ $stats['activas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Categorías inactivas</div>
                        <div class="stat-value">{{ $stats['inactivas'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre o descripción...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="activo">
                            <option value="">Todos</option>
                            <option value="1">Activas</option>
                            <option value="0">Inactivas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de categorías</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none text-primary">
                                        Nombre
                                        @if($sortField === 'nombre')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Descripción</th>
                                <th class="fw-semibold text-center">Color</th>
                                <th class="fw-semibold text-center">Icono</th>
                                <th class="fw-semibold text-center">Estado</th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('created_at')" href="#" class="text-decoration-none text-primary">
                                        Creado
                                        @if($sortField === 'created_at')
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
                            @forelse($categorias as $categoria)
                                <tr class="categoria-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 d-flex align-items-center justify-content-center"
                                                 style="width: 42px; height: 42px; background-color: {{ $categoria->color }}; border-radius: 8px;">
                                                <i class="ri {{ $categoria->icono ?? 'ri-price-tag-3-line' }} text-white fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $categoria->nombre }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($categoria->descripcion, 60) ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: {{ $categoria->color }}; color: white; min-width: 80px;">
                                            {{ $categoria->color }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <code class="small">{{ $categoria->icono ?? '-' }}</code>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $categoria->id }}"
                                                   wire:click="toggleActivo({{ $categoria->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $categoria->activo ? 'desactivar' : 'activar' }} esta categoría?"
                                                   {{ $categoria->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $categoria->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('access categorias')
                                                <a class="dropdown-item" href="{{ route('admin.categorias.show', $categoria) }}">
                                                    <i class="ri ri-eye-line me-1 text-info"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit categorias')
                                                <a class="dropdown-item" href="{{ route('admin.categorias.edit', $categoria) }}">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete categorias')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteCategoria({{ $categoria->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta categoría?">
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
                                        <i class="ri ri-price-tag-3-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron categorías</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $categorias->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
