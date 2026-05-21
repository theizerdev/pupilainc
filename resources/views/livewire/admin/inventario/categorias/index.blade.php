<div class="w-100">
    @section('title', 'Categorías de Producto')

    @push('styles')
    <style>
        /* Hero Section */
        .categorias-hero {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .categorias-hero h2 {
            color: #fff;
            margin: 0;
        }
        .categorias-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards - Compact Style */
        .stat-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
        }
        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .stat-card .stat-value {
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-categorias thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-categorias tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .categoria-row { transition: background 0.15s; }
        .categoria-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="categorias-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-price-tag-3-line me-2"></i>Categorías de Producto</h2>
            <p class="mt-1">Gestión de categorías para el inventario</p>
        </div>
        @can('create categorias-producto')
            <a href="{{ route('admin.inventario.categorias.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nueva Categoría
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total categorías</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-value text-success">{{ $stats['activas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-pause-circle-line"></i></div>
                <div>
                    <div class="stat-label">Inactivas</div>
                    <div class="stat-value text-warning">{{ $stats['inactivas'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar categoría...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.change="status">
                        <option value="">Todos los estados</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        wire:click="$set('selected', {{ json_encode($categorias->pluck('id')) }})">
                    Seleccionar página
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('selected', [])">
                    Deseleccionar
                </button>
                @can('delete categorias-producto')
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="confirm('¿Eliminar las categorías seleccionadas?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected" @if(!count($selected)) disabled @endif>
                        <i class="ri ri-delete-bin-line me-1"></i>Eliminar seleccionados
                        @if(count($selected))
                            <span class="badge bg-white text-danger ms-2">{{ count($selected) }}</span>
                        @endif
                    </button>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-categorias">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                            Nombre
                            @if($sortField === 'nombre')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Descripción</th>
                        <th>Color</th>
                        <th>Estado</th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            Creado
                            @if($sortField === 'created_at')
                                <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line ms-1"></i>
                            @endif
                        </th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                        <tr class="categoria-row">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="selected" value="{{ $categoria->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block" style="width:14px;height:14px;background:{{ $categoria->color }};"></span>
                                    <span class="fw-semibold">{{ $categoria->nombre }}</span>
                                </div>
                            </td>
                            <td>{{ $categoria->descripcion ?? '-' }}</td>
                            <td>
                                <span class="badge rounded-pill" style="background:{{ $categoria->color }};color:#fff;">{{ $categoria->color }}</span>
                            </td>
                            <td>
                                @can('edit categorias-producto')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               wire:click="toggleStatus({{ $categoria->id }})"
                                               {{ $categoria->status ? 'checked' : '' }}
                                               style="cursor:pointer;">
                                    </div>
                                @else
                                    <span class="badge rounded-pill bg-{{ $categoria->status ? 'success' : 'secondary' }}-label">
                                        {{ $categoria->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </td>
                            <td>{{ $categoria->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('edit categorias-producto')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.inventario.categorias.edit', $categoria) }}">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete categorias-producto')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $categoria->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta categoría?">
                                                    <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">No se encontraron categorías</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($categorias->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $categorias->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
