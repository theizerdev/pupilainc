<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Especialidades Médicas</h1>
                <p class="text-muted">Gestión de especialidades y servicios médicos</p>
            </div>
            <div>
                @can('create especialidades')
                    <a href="{{ route('admin.especialidades.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Especialidad
                    </a>
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
                                    Total Especialidades
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Especialidades Activas
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Especialidades Inactivas
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Costo Promedio
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ format_money($stats['promedio_costo']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <input type="text" class="form-control" id="search" wire:model.debounce.300ms="search" placeholder="Buscar especialidad...">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="empresa_id">Empresa:</label>
                            <select class="form-control" id="empresa_id" wire:model="empresa_id">
                                <option value="">Todas las empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sucursal_id">Sucursal:</label>
                            <select class="form-control" id="sucursal_id" wire:model="sucursal_id">
                                <option value="">Todas las sucursales</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" wire:model="status">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Especialidades</h6>
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
                                <th>Empresa</th>
                                <th>Sucursal</th>
                                <th>Costo Consulta</th>
                                <th>Duración</th>
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
                            @forelse($especialidades as $especialidad)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $especialidad->color }}; color: white;">
                                            <i class="fas {{ $especialidad->icono }}"></i> {{ $especialidad->codigo }}
                                        </span>
                                    </td>
                                    <td>{{ $especialidad->nombre }}</td>
                                    <td>{{ $especialidad->empresa->razon_social ?? '-' }}</td>
                                    <td>{{ $especialidad->sucursal->nombre ?? '-' }}</td>
                                    <td>{{ format_money($especialidad->costo_consulta) }}</td>
                                    <td>{{ $especialidad->duracion_consulta }} min</td>
                                    <td>
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $especialidad->id }}"
                                                   wire:click="toggleStatus({{ $especialidad->id }})"
                                                   {{ $especialidad->status ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td>{{ $especialidad->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('access especialidades')
                                                <a class="dropdown-item" href="{{ route('admin.especialidades.show', $especialidad) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit especialidades')
                                                <a class="dropdown-item" href="{{ route('admin.especialidades.edit', $especialidad) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete especialidades')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteEspecialidad({{ $especialidad->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta especialidad?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No se encontraron especialidades</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                      {{ $especialidades->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>