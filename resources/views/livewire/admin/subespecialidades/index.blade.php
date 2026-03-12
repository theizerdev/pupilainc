<div>
    <div >
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">
                    <i class="icon-base ri ri-stethoscope-line text-primary me-2"></i>
                    Subespecialidades Médicas
                </h4>
                <p class="text-muted mb-0">Gestione las subespecialidades médicas de su clínica</p>
            </div>
            <div>
                @can('create subespecialidades')
                <a href="{{ route('admin.subespecialidades.create') }}" class="btn btn-primary">
                    <i class="icon-base ri ri-add-line me-1"></i> Nueva Subespecialidad
                </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block">Total Subespecialidades</small>
                                <h3 class="mb-0 mt-1">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-soft-primary">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base ri ri-list-check text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block">Subespecialidades Activas</small>
                                <h3 class="mb-0 mt-1 text-success">{{ $stats['activas'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-soft-success">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block">Subespecialidades Inactivas</small>
                                <h3 class="mb-0 mt-1 text-warning">{{ $stats['inactivas'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-soft-warning">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base ri ri-pause-circle-line text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block">Costo Promedio</small>
                                <h3 class="mb-0 mt-1 text-info">
                                    @if($stats['promedio_costo'] > 0)
                                        ${{ number_format($stats['promedio_costo'], 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </h3>
                            </div>
                            <div class="avatar avatar-lg bg-soft-info">
                                <span class="avatar-initial rounded-circle">
                                    <i class="icon-base ri ri-money-dollar-circle-line text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <i class="icon-base ri ri-filter-3-line me-2"></i>
                <h5 class="mb-0">Filtros de Búsqueda</h5>
            </div>
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
                            <select class="form-select" id="especialidad_id" wire:model.change="especialidad_id">
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
                            <select class="form-select" id="empresa_id" wire:model.change="empresa_id">
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
                            <select class="form-select" id="status" wire:model.change="status">
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
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="icon-base ri ri-table-line me-2"></i>
                    <h5 class="mb-0">Listado de Subespecialidades</h5>
                </div>
                <span class="badge bg-label-primary">{{ $subespecialidades->total() }} registros</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-nowrap">
                                    <a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none text-body">
                                        <i class="icon-base ri ri-barcode-line me-1"></i> Código
                                        @if($sortField === 'codigo')
                                            <i class="ri-sort-{{ $sortDirection === 'asc' ? 'asc' : 'desc' }}-line ms-1"></i>
                                        @else
                                            <i class="ri-arrow-up-down-line ms-1 opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-nowrap">
                                    <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none text-body">
                                        <i class="icon-base ri ri-edit-line me-1"></i> Nombre
                                        @if($sortField === 'nombre')
                                            <i class="ri-sort-{{ $sortDirection === 'asc' ? 'asc' : 'desc' }}-line ms-1"></i>
                                        @else
                                            <i class="ri-arrow-up-down-line ms-1 opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-stack-line me-1"></i> Especialidad
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-building-2-line me-1"></i> Empresa
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-store-2-line me-1"></i> Sucursal
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-money-dollar-circle-line me-1"></i> Costo
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-timer-flash-line me-1"></i> Duración
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-toggle-line me-1"></i> Estado
                                </th>
                                <th class="text-nowrap">
                                    <a wire:click.prevent="sortBy('created_at')" href="#" class="text-decoration-none text-body">
                                        <i class="icon-base ri ri-time-line me-1"></i> Creado
                                        @if($sortField === 'created_at')
                                            <i class="ri-sort-{{ $sortDirection === 'asc' ? 'asc' : 'desc' }}-line ms-1"></i>
                                        @else
                                            <i class="ri-arrow-up-down-line ms-1 opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-nowrap">
                                    <i class="icon-base ri ri-settings-4-line me-1"></i> Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subespecialidades as $subespecialidad)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle" style="background-color: {{ $subespecialidad->color }}; color: white;">
                                                    <i class="icon-base ri ri-stethoscope-line"></i>
                                                </span>
                                            </div>
                                            <span class="badge rounded-pill" style="background-color: {{ $subespecialidad->color }}; color: white;">
                                                {{ $subespecialidad->codigo }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $subespecialidad->nombre }}</strong>
                                            @if($subespecialidad->descripcion)
                                                <small class="text-muted">{{ Str::limit($subespecialidad->descripcion, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            <i class="icon-base ri ri-stack-line me-1"></i>
                                            {{ $subespecialidad->especialidad->nombre ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">Empresa</small>
                                        <strong>{{ $subespecialidad->empresa->razon_social ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">Sucursal</small>
                                        <strong>{{ $subespecialidad->sucursal->nombre ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        @if($subespecialidad->costo_consulta > 0)
                                            <strong class="text-success">${{ number_format($subespecialidad->costo_consulta, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $subespecialidad->duracion_consulta }} min</td>
                                    <td>
                                        @can('edit subespecialidades')
                                            <div class="form-check form-switch form-switch-lg">
                                                <input class="form-check-input" type="checkbox"
                                                       id="statusSwitch{{ $subespecialidad->id }}"
                                                       wire:click="toggleStatus({{ $subespecialidad->id }})"
                                                       {{ $subespecialidad->status ? 'checked' : '' }}
                                                       style="cursor: pointer;">
                                            </div>
                                        @else
                                            <span class="badge badge-{{ $subespecialidad->status ? 'success' : 'secondary' }}">
                                                {{ $subespecialidad->status ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">Creado el</small>
                                        <strong>{{ $subespecialidad->created_at->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="icon-base ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('access subespecialidades')
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.show', $subespecialidad) }}">
                                                    <i class="icon-base ri ri-eye-line me-1"></i> Ver
                                                </a>
                                                @endcan
                                                @can('edit subespecialidades')
                                                <a class="dropdown-item" href="{{ route('admin.subespecialidades.edit', $subespecialidad) }}">
                                                    <i class="icon-base ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete subespecialidades')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="deleteSubespecialidad({{ $subespecialidad->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta subespecialidad?">
                                                    <i class="icon-base ri ri-delete-bin-line me-1"></i> Eliminar
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