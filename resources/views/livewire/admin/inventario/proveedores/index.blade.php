<div>
    @section('title', 'Proveedores')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Proveedores</h1>
            <p class="text-muted">Gestión de proveedores de inventario</p>
        </div>
        @can('create proveedores')
            <a href="{{ route('admin.inventario.proveedores.create') }}" class="btn btn-primary">
                <i class="ri ri-add-line me-1"></i> Nuevo Proveedor
            </a>
        @endcan
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['activos'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <div class="row align-items-end gap-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, documento, email...">
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="status">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none">Nombre <i class="fas fa-sort"></i></a></th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Ubicación</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proveedores as $proveedor)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $proveedor->nombre }}</div>
                                    @if($proveedor->email)
                                        <small class="text-muted">{{ $proveedor->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $proveedor->documento ?? '-' }}</td>
                                <td>{{ $proveedor->contacto ?? '-' }}</td>
                                <td>{{ $proveedor->telefono ?? '-' }}</td>
                                <td>
                                    @if($proveedor->latitud && $proveedor->longitud)
                                        <a href="https://www.google.com/maps?q={{ $proveedor->latitud }},{{ $proveedor->longitud }}"
                                           target="_blank" class="btn btn-sm btn-outline-secondary py-0">
                                            <i class="ri ri-map-pin-line me-1"></i>Ver mapa
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $proveedor->productos_count }}</span></td>
                                <td>
                                    @can('edit proveedores')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   wire:click="toggleStatus({{ $proveedor->id }})"
                                                   {{ $proveedor->status ? 'checked' : '' }} style="cursor:pointer;">
                                        </div>
                                    @else
                                        <span class="badge bg-{{ $proveedor->status ? 'success' : 'secondary' }}">
                                            {{ $proveedor->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endcan
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @can('edit proveedores')
                                                <a class="dropdown-item" href="{{ route('admin.inventario.proveedores.edit', $proveedor) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                            @endcan
                                            @can('delete proveedores')
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $proveedor->id }})"
                                                        wire:confirm="¿Eliminar este proveedor?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron proveedores</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $proveedores->links('livewire.pagination') }}
        </div>
    </div>
</div>
