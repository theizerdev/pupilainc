<div>
    <div >
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Subespecialidades Médicas</h1>
                <p class="text-muted">Gestione las subespecialidades médicas de su clínica</p>
            </div>
            <div>
                @can('create subespecialidades')
                <a href="{{ route('admin.subespecialidades.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Subespecialidad
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
                                    Total Subespecialidades
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list fa-2x text-gray-300"></i>
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
                                    Subespecialidades Activas
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
                                    Subespecialidades Inactivas
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
                                    @if($stats['promedio_costo'] > 0)
                                        {{ format_money($stats['promedio_costo']) }}
                                    @else
                                        <span class="text-muted">Sin datos</span>
                                    @endif
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
                            <input type="text" class="form-control" id="search" wire:model.debounce.300ms="search" placeholder="Buscar subespecialidad...">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="especialidad_id">Especialidad:</label>
                            <select class="form-control" id="especialidad_id" wire:model.change="especialidad_id">
                                <option value="">Todas las especialidades</option>
                                @foreach($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="empresa_id">Empresa:</label>
                            <select class="form-control" id="empresa_id" wire:model.change="empresa_id">
                                <option value="">Todas las empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" wire:model.change="status">
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
                <h6 class="m-0 font-weight-bold text-primary">Listado de Subespecialidades</h6>
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
                                <th>Especialidad</th>
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
                            @forelse($subespecialidades as $subespecialidad)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $subespecialidad->color }}; color: white;">
                                            {{ $subespecialidad->codigo }}
                                        </span>
                                    </td>
                                    <td>{{ $subespecialidad->nombre }}</td>
                                    <td>{{ $subespecialidad->especialidad->nombre ?? '-' }}</td>
                                    <td>{{ $subespecialidad->empresa->razon_social ?? '-' }}</td>
                                    <td>{{ $subespecialidad->sucursal->nombre ?? '-' }}</td>
                                    <td>
                                        @if($subespecialidad->costo_consulta > 0)
                                            {{ format_money($subespecialidad->costo_consulta) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $subespecialidad->duracion_consulta }} min</td>
                                    <td>
                                        <span class="badge badge-{{ $subespecialidad->status ? 'success' : 'secondary' }}">
                                            {{ $subespecialidad->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $subespecialidad->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('access subespecialidades')
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.show', $subespecialidad) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit subespecialidades')
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.edit', $subespecialidad) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete subespecialidades')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteSubespecialidad({{ $subespecialidad->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta subespecialidad?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No se encontraron subespecialidades</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $subespecialidades->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>