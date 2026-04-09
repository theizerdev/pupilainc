<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Tipos de Atención</h1>
                <p class="text-muted">Gestión de tipos de atención y servicios médicos</p>
            </div>
            <div>
                @can('create tipo-consultas')
                    <a href="{{ route('admin.tipo-consultas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Tipo de Atención
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
                                    Total Tipos de Atención
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-stethoscope fa-2x text-gray-300"></i>
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
                                    Tipos de Atención Activos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activas'] }}</div>
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
                                    Tipos de Atención Inactivos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivas'] }}</div>
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
                    <div class="col-md-5">
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

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="status" class="fw-bold"><i class="fas fa-filter me-1"></i>Estado:</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    wire:model.live="status">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="button" class="btn btn-secondary w-100 mb-1" wire:click="clearFilters" title="Limpiar Filtros">
                                <i class="fas fa-eraser"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Tipos de Atención</h6>
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
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
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
                                <th>Icono</th>
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
                            @forelse($tipoConsultas as $tipoConsulta)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $tipoConsulta->color }}; color: white;">
                                            <i class="fas {{ $tipoConsulta->icono }}"></i> {{ $tipoConsulta->codigo }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $tipoConsulta->nombre }}</strong></td>
                                    <td>{{ Str::limit($tipoConsulta->descripcion, 50) ?? '-' }}</td>
                                    <td class="text-center">
                                        <i class="fas {{ $tipoConsulta->icono }} fa-2x" style="color: {{ $tipoConsulta->color }};"></i>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $tipoConsulta->status ? 'success' : 'secondary' }}">
                                            {{ $tipoConsulta->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $tipoConsulta->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('access tipo-consultas')
                                                <a class="dropdown-item" href="{{ route('admin.tipo-consultas.show', $tipoConsulta) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit tipo-consultas')
                                                <a class="dropdown-item" href="{{ route('admin.tipo-consultas.edit', $tipoConsulta) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete tipo-consultas')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteTipoConsulta({{ $tipoConsulta->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este tipo de atención?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron tipos de atención</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                      {{ $tipoConsultas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>