<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Médicos</h1>
                <p class="text-muted">Gestión de médicos y profesionales de la salud</p>
            </div>
            <div>
                @can('create medicos')
                    <a href="{{ route('admin.medicos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Médico
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
                                    Total Médicos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-md fa-2x text-gray-300"></i>
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
                                    Médicos Activos
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Médicos Inactivos
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Años Promedio Experiencia
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['promedio_experiencia'] }} años
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                            <input type="text" class="form-control" id="search" wire:model.debounce.300ms="search" placeholder="Buscar médico...">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
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

                    <div class="col-md-2">
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

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="especialidad_id">Especialidad:</label>
                            <select class="form-control" id="especialidad_id" wire:model="especialidad_id">
                                <option value="">Todas las especialidades</option>
                                @foreach($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" wire:model="status">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="resetFilters">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Médicos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a wire:click.prevent="sortBy('nombres')" href="#" class="text-decoration-none">
                                        Nombre
                                        @if($sortField === 'nombres')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('documento_identidad')" href="#" class="text-decoration-none">
                                        Documento
                                        @if($sortField === 'documento_identidad')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Especialidad</th>
                                <th>Empresa</th>
                                <th>Sucursal</th>
                                <th>
                                    <a wire:click.prevent="sortBy('anios_experiencia')" href="#" class="text-decoration-none">
                                        Experiencia
                                        @if($sortField === 'anios_experiencia')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Nivel</th>
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
                            @forelse($medicos as $medico)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-primary">
                                                    {{ substr($medico->nombres, 0, 1) }}{{ substr($medico->apellidos, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $medico->nombres }} {{ $medico->apellidos }}</div>
                                                <small class="text-muted">{{ $medico->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $medico->documento_identidad }}</td>
                                    <td>
                                        @if($medico->especialidades->count() > 0)
                                            @foreach($medico->especialidades->take(2) as $especialidad)
                                                <span class="badge bg-info me-1">{{ $especialidad->nombre }}</span>
                                            @endforeach
                                            @if($medico->especialidades->count() > 2)
                                                <span class="badge bg-secondary">+{{ $medico->especialidades->count() - 2 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $medico->empresa->razon_social ?? '-' }}</td>
                                    <td>{{ $medico->sucursal->nombre ?? '-' }}</td>
                                    <td>{{ $medico->anios_experiencia }} años</td>
                                    <td>
                                        <span class="badge bg-{{ $medico->nivel_experiencia == 'experto' ? 'danger' : ($medico->nivel_experiencia == 'intermedio' ? 'warning' : 'success') }}">
                                            {{ ucfirst($medico->nivel_experiencia) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $medico->status ? 'success' : 'secondary' }}">
                                            {{ $medico->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $medico->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('admin.medicos.show')
                                                <a class="dropdown-item" href="{{ route('admin.medicos.show', $medico) }}">
                                                    <i class="ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit medicos')
                                                <a class="dropdown-item" href="{{ route('admin.medicos.edit', $medico) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @if($medico->status)
                                                    @can('edit medicos')
                                                    <button type="button" class="dropdown-item text-warning"
                                                            wire:click="toggleStatus({{ $medico->id }})"
                                                            wire:confirm="¿Estás seguro de desactivar este médico?">
                                                        <i class="ri ri-pause-circle-line me-1"></i> Desactivar
                                                    </button>
                                                    @endcan
                                                @else
                                                    @can('edit medicos')
                                                    <button type="button" class="dropdown-item text-success"
                                                            wire:click="toggleStatus({{ $medico->id }})"
                                                            wire:confirm="¿Estás seguro de activar este médico?">
                                                        <i class="ri ri-play-circle-line me-1"></i> Activar
                                                    </button>
                                                    @endcan
                                                @endif
                                                @can('admin.medicos.destroy')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $medico->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este médico?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No se encontraron médicos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                      {{ $medicos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>