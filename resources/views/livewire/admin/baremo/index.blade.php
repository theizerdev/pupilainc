<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Servicios</h1>
                <p class="text-muted">Gestión de servicios y procedimientos médicos</p>
            </div>
            <div>
                @can('create baremos')
                    <a href="{{ route('admin.baremos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Servicio
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Servicios
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-concierge-bell fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Servicios Activos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Servicios Inactivos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search" class="fw-bold"><i class="fas fa-search me-1"></i>Búsqueda:</label>
                            <input type="text" 
                                   class="form-control @error('search') is-invalid @enderror" 
                                   id="search" 
                                   wire:model.live.debounce.300ms="search" 
                                   placeholder="Buscar por nombre, código o descripción..."
                                   autocomplete="off">
                            @error('search')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="categoria_id" class="fw-bold"><i class="fas fa-tag me-1"></i>Categoría:</label>
                            <select class="form-control @error('categoria_id') is-invalid @enderror" 
                                    id="categoria_id" 
                                    wire:model.live="categoria_id">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="activo" class="fw-bold"><i class="fas fa-filter me-1"></i>Estado:</label>
                            <select class="form-control @error('activo') is-invalid @enderror" 
                                    id="activo" 
                                    wire:model.live="activo">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('activo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Servicios</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Servicio</th>
                                <th>Categoría</th>
                                <th>Costo (USD)</th>
                               
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
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($baremos as $baremo)
                                <tr>
                                    <td><strong>{{ $baremo->codigo }}</strong></td>
                                    <td>
                                        <div>
                                            <strong>{{ $baremo->nombre_servicio }}</strong>
                                            @if($baremo->descripcion)
                                                <br><small class="text-muted">{{ Str::limit($baremo->descripcion, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($baremo->categoria)
                                            <span class="badge" style="background-color: {{ $baremo->categoria->color }}; color: white;">
                                                {{ $baremo->categoria->nombre }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Sin categoría</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ format_money($baremo->costo_usd, 2) }}</strong>
                                      
                                    </td>
                                    
                                    <td>{{ $baremo->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   wire:click="toggleEstado({{ $baremo->id }})"
                                                   {{ $baremo->activo ? 'checked' : '' }}>
                                            
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('access baremos')
                                                <a class="dropdown-item" href="{{ route('admin.baremos.show', $baremo) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit baremos')
                                                <a class="dropdown-item" href="{{ route('admin.baremos.edit', $baremo) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
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
                                    <td colspan="8" class="text-center">No se encontraron servicios</td>
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
