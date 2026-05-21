<div class="w-100">
    @section('title', 'Marcas')

    @push('styles')
    <style>
        /* Hero Section */
        .marcas-hero {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .marcas-hero h2 {
            color: #fff;
            margin: 0;
        }
        .marcas-hero p {
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
        .table-marcas thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-marcas tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .marca-row { transition: background 0.15s; }
        .marca-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="marcas-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-bookmark-line me-2"></i>Marcas</h2>
            <p class="mt-1">Gestión de marcas para el inventario</p>
        </div>
        @can('create marcas')
            <a href="{{ route('admin.inventario.marcas.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nueva Marca
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total marcas</div>
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
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar marca...">
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
                        wire:click="$set('selected', {{ json_encode($marcas->pluck('id')) }})">
                    Seleccionar página
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('selected', [])">
                    Deseleccionar
                </button>
                @can('delete marcas')
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="confirm('¿Eliminar las marcas seleccionadas?') || event.stopImmediatePropagation()"
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
            <table class="datatables-products table table-marcas">
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
                        <th>Sitio Web</th>
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
                    @forelse($marcas as $marca)
                        <tr class="marca-row">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="selected" value="{{ $marca->id }}">
                                </div>
                            </td>
                            <td class="fw-semibold">{{ $marca->nombre }}</td>
                            <td>{{ $marca->descripcion ?? '-' }}</td>
                            <td>
                                @if($marca->sitio_web)
                                    <a href="{{ $marca->sitio_web }}" target="_blank" class="text-primary">
                                        <i class="ri ri-external-link-line me-1"></i>Ver sitio
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @can('edit marcas')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               wire:click="toggleStatus({{ $marca->id }})"
                                               {{ $marca->status ? 'checked' : '' }}
                                               style="cursor:pointer;">
                                    </div>
                                @else
                                    <span class="badge rounded-pill bg-{{ $marca->status ? 'success' : 'secondary' }}-label">
                                        {{ $marca->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </td>
                            <td>{{ $marca->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('edit marcas')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.inventario.marcas.edit', $marca) }}">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete marcas')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $marca->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta marca?">
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
                                <p class="mb-0">No se encontraron marcas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($marcas->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $marcas->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
