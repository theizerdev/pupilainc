<div>
    @section('title', 'Almacenes')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Almacenes</h1>
            <p class="text-muted">Gestión de almacenes y ubicaciones de inventario</p>
        </div>
        @can('create almacenes')
            <button class="btn btn-primary" wire:click="openModal()">
                <i class="ri ri-add-line me-1"></i> Nuevo Almacén
            </button>
        @endcan
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar almacén...">
                </div>
                <div class="col-md-3">
                    <select class="form-control" wire:model.change="status">
                        <option value="">Todos los estados</option>
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
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Principal</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($almacenes as $almacen)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $almacen->nombre }}</div>
                                    @if($almacen->descripcion)
                                        <small class="text-muted">{{ $almacen->descripcion }}</small>
                                    @endif
                                </td>
                                <td>{{ $almacen->ubicacion ?? '-' }}</td>
                                <td>
                                    @if($almacen->es_principal)
                                        <span class="badge bg-primary"><i class="ri ri-star-line me-1"></i>Principal</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $almacen->status ? 'success' : 'secondary' }}">
                                        {{ $almacen->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @can('edit almacenes')
                                                <button class="dropdown-item" wire:click="openModal({{ $almacen->id }})">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </button>
                                            @endcan
                                            @can('delete almacenes')
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $almacen->id }})"
                                                        wire:confirm="¿Eliminar este almacén?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron almacenes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $almacenes->links('livewire.pagination') }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Editar' : 'Nuevo' }} Almacén</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Nombre *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre">
                        @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label>Descripción</label>
                        <input type="text" class="form-control" wire:model="descripcion">
                    </div>
                    <div class="form-group mb-3">
                        <label>Ubicación física</label>
                        <input type="text" class="form-control" wire:model="ubicacion" placeholder="Ej: Piso 2, Sala B">
                    </div>
                    <div class="d-flex gap-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="es_principal" id="esPrincipal">
                            <label class="form-check-label" for="esPrincipal">Almacén principal</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="modalStatus" id="modalStatus">
                            <label class="form-check-label" for="modalStatus">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showModal', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="save">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
