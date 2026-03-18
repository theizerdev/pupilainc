<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Plan de Cuentas</h1>
                <p class="text-muted">Gestión del plan de cuentas contables</p>
            </div>
            <div>
                @can('create contabilidad')
                    <button wire:click="create" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Nueva Cuenta
                    </button>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Cuentas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-file-list-3-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Cuentas Activas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-check-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Cuentas Inactivas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-close-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Con Movimientos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['con_movimientos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-exchange-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por código o nombre...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control" id="tipo" wire:model.live="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="activo">Activo</option>
                                <option value="pasivo">Pasivo</option>
                                <option value="patrimonio">Patrimonio</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="costo">Costo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="naturaleza">Naturaleza:</label>
                            <select class="form-control" id="naturaleza" wire:model.live="naturaleza">
                                <option value="">Todas</option>
                                <option value="deudora">Deudora</option>
                                <option value="acreedora">Acreedora</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="nivel">Nivel:</label>
                            <select class="form-control" id="nivel" wire:model.live="nivel">
                                <option value="">Todos</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" wire:model.live="status">
                                <option value="">Todos</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="resetFilters" title="Limpiar filtros">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Cuentas Contables</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none">
                                        Código
                                        @if($sortField === 'codigo')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none">
                                        Nombre
                                        @if($sortField === 'nombre')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('tipo')" href="#" class="text-decoration-none">
                                        Tipo
                                        @if($sortField === 'tipo')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('naturaleza')" href="#" class="text-decoration-none">
                                        Naturaleza
                                        @if($sortField === 'naturaleza')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('nivel')" href="#" class="text-decoration-none">
                                        Nivel
                                        @if($sortField === 'nivel')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Movimientos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $cuenta)
                                <tr>
                                    <td><strong>{{ $cuenta->codigo }}</strong></td>
                                    <td>
                                        <div>{{ $cuenta->nombre }}</div>
                                        @if($cuenta->descripcion)
                                            <small class="text-muted">{{ Str::limit($cuenta->descripcion, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $cuenta->tipo === 'activo' ? 'success' : ($cuenta->tipo === 'pasivo' ? 'danger' : ($cuenta->tipo === 'patrimonio' ? 'info' : ($cuenta->tipo === 'ingreso' ? 'primary' : 'warning'))) }}">
                                            {{ ucfirst($cuenta->tipo) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($cuenta->naturaleza) }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $cuenta->nivel }}</span></td>
                                    <td>
                                        @if($cuenta->acepta_movimientos)
                                            <i class="ri-check-line text-success"></i> Sí
                                        @else
                                            <i class="ri-close-line text-danger"></i> No
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $cuenta->id }}"
                                                   wire:click="toggleStatus({{ $cuenta->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $cuenta->activo ? 'desactivar' : 'activar' }} esta cuenta?"
                                                   {{ $cuenta->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('edit contabilidad')
                                                <button class="dropdown-item" wire:click="edit({{ $cuenta->id }})">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </button>
                                                @endcan

                                                @can('delete contabilidad')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $cuenta->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta cuenta?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron cuentas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $cuentas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Editar' : 'Nueva' }} Cuenta Contable</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código *</label>
                                <input type="text" wire:model="codigo" class="form-control @error('codigo') is-invalid @enderror">
                                @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel *</label>
                                <input type="number" wire:model="nivel_cuenta" class="form-control @error('nivel_cuenta') is-invalid @enderror" min="1" max="10">
                                @error('nivel_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nombre *</label>
                                <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select wire:model="tipo_cuenta" class="form-select @error('tipo_cuenta') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="activo">Activo</option>
                                    <option value="pasivo">Pasivo</option>
                                    <option value="patrimonio">Patrimonio</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                    <option value="costo">Costo</option>
                                </select>
                                @error('tipo_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Naturaleza *</label>
                                <select wire:model="naturaleza_cuenta" class="form-select @error('naturaleza_cuenta') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="deudora">Deudora</option>
                                    <option value="acreedora">Acreedora</option>
                                </select>
                                @error('naturaleza_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cuenta Padre</label>
                                <select wire:model="cuenta_padre_id" class="form-select">
                                    <option value="">Sin cuenta padre</option>
                                    @foreach($cuentasPadre as $padre)
                                        <option value="{{ $padre->id }}">{{ $padre->codigo }} - {{ $padre->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opciones</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" wire:model="acepta_movimientos" class="form-check-input" id="acepta_movimientos">
                                    <label class="form-check-label" for="acepta_movimientos">Acepta movimientos</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="activo" class="form-check-input" id="activo">
                                    <label class="form-check-label" for="activo">Activo</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="save">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
