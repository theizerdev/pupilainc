<div>
    @section('title', 'Marcas')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Marcas</h1>
            <p class="text-muted">Gestión de marcas para el inventario</p>
        </div>
        @can('create marcas')
            <a href="{{ route('admin.inventario.marcas.create') }}" class="btn btn-primary">
                <i class="ri ri-add-line me-1"></i> Nueva Marca
            </a>
        @endcan
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto"><i class="ri ri-bookmark-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activas'] }}</div>
                        </div>
                        <div class="col-auto"><i class="ri ri-checkbox-circle-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Inactivas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivas'] }}</div>
                        </div>
                        <div class="col-auto"><i class="ri ri-pause-circle-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Búsqueda:</label>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar marca...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado:</label>
                        <select class="form-control" wire:model.change="status">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" wire:click="resetFilters">
                        <i class="ri ri-close-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Marcas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none">
                                    Nombre
                                    @if($sortField === 'nombre')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Descripción</th>
                            <th>Sitio Web</th>
                            <th>Estado</th>
                            <th>
                                <a wire:click.prevent="sortBy('created_at')" href="#" class="text-decoration-none">
                                    Creado
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($marcas as $marca)
                            <tr>
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
                                        <span class="badge bg-{{ $marca->status ? 'success' : 'secondary' }}">
                                            {{ $marca->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endcan
                                </td>
                                <td>{{ $marca->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @can('edit marcas')
                                                <a class="dropdown-item" href="{{ route('admin.inventario.marcas.edit', $marca) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                            @endcan
                                            @can('delete marcas')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $marca->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta marca?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron marcas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $marcas->links('livewire.pagination') }}
            </div>
        </div>
    </div>
</div>
