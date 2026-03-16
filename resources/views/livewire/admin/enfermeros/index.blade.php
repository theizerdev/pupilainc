<div>
    @section('title', 'Enfermeros/as')
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-nurse me-2"></i>Enfermeros/as
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.enfermeros.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nuevo Enfermero/a
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Enfermeros
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-nurse fa-2x text-gray-300"></i>
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
                                Enfermeros Activos
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
                                Enfermeros Inactivos
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

    <!-- Mensajes -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Búsqueda</label>
                        <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, documento, licencia...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select class="form-control" id="status" wire:model.change="status">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="nivel_experiencia">Nivel</label>
                        <select class="form-control" id="nivel_experiencia" wire:model.change="nivel_experiencia">
                            <option value="">Todos</option>
                            <option value="Básico">Básico</option>
                            <option value="Intermedio">Intermedio</option>
                            <option value="Avanzado">Avanzado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tipo_enfermero">Tipo</label>
                        <select class="form-control" id="tipo_enfermero" wire:model.change="tipo_enfermero">
                            <option value="">Todos</option>
                            <option value="General">General</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Jefe de Servicio">Jefe de Servicio</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="empresa_id">Empresa</label>
                        <select class="form-control" id="empresa_id" wire:model.change="empresa_id">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-secondary w-100" wire:click="resetFilters">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de enfermeros -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('nombres')" class="text-decoration-none text-dark">
                                    Nombre
                                    @if($sortField === 'nombres')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('documento_identidad')" class="text-decoration-none text-dark">
                                    Documento
                                    @if($sortField === 'documento_identidad')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('licencia_enfermeria')" class="text-decoration-none text-dark">
                                    Licencia
                                    @if($sortField === 'licencia_enfermeria')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Tipo</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('nivel_experiencia')" class="text-decoration-none text-dark">
                                    Nivel
                                    @if($sortField === 'nivel_experiencia')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Email</th>
                            <th>Empresa</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enfermeros as $enfermero)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3 @if($enfermero->status) avatar-online @else avatar-offline @endif">
                                        @if($enfermero->iniciales)
                                        <span class="avatar-initial rounded-circle {{ $enfermero->color_clase }}">{{ $enfermero->iniciales }}</span>
                                        @else
                                        <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                                        @endif
                                    </div> 
                                        <div>
                                            <div class="fw-bold">{{ $enfermero->nombres }} {{ $enfermero->apellidos }}</div>
                                            <small class="text-muted">{{ $enfermero->anios_experiencia }} años exp.</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $enfermero->documento_identidad }}</td>
                                <td>{{ $enfermero->licencia_enfermeria }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $enfermero->tipo_enfermero }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $enfermero->nivel_experiencia }}</span>
                                </td>
                                <td>{{ $enfermero->user->email }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $enfermero->empresa->razon_social ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    @can('edit enfermeros')
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $enfermero->id }}"
                                                   wire:click="toggleStatus({{ $enfermero->id }})"
                                                   {{ $enfermero->status ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    @else
                                        <span class="badge badge-{{ $enfermero->status ? 'success' : 'secondary' }}">
                                            {{ $enfermero->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endcan
                                </td>
                                <td> 
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.enfermeros.edit', $enfermero->id) }}">
                                                <i class="fas fa-edit me-2"></i>Editar
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.enfermeros.horarios', $enfermero->id) }}">
                                                <i class="fas fa-clock me-2"></i>Horarios
                                            </a>
                                            <button class="dropdown-item" wire:click="enviarMensajeBienvenida({{ $enfermero->id }})"
                                                    onclick="confirm('¿Está seguro de enviar el mensaje de bienvenida por WhatsApp?') || event.stopImmediatePropagation()">
                                                <i class="fab fa-whatsapp me-2 text-success"></i>Enviar Bienvenida
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item" wire:click="toggleStatus({{ $enfermero->id }})" 
                                                    onclick="confirm('¿Está seguro de cambiar el estado?') || event.stopImmediatePropagation()">
                                                <i class="fas fa-power-off me-2"></i>{{ $enfermero->status ? 'Desactivar' : 'Activar' }}
                                            </button>
                                            @if($enfermero->signosVitales->count() > 0)
                                               <i class="fas fa-trash-outline"></i><span class="dropdown-item text-danger">No puede ser eliminado</span>
                                               @else
                                               <button class="dropdown-item text-danger" wire:click="delete({{ $enfermero->id }})"
                                                    onclick="confirm('¿Está seguro de eliminar este enfermero/a? Esta acción no se puede deshacer.') || event.stopImmediatePropagation()">
                                                <i class="fas fa-trash me-2"></i>Eliminar
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-user-nurse fa-3x mb-3"></i>
                                    <h5>No se encontraron enfermeros/as</h5>
                                    <p>Intenta ajustar los filtros de búsqueda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Mostrando {{ $enfermeros->firstItem() }} - {{ $enfermeros->lastItem() }} de {{ $enfermeros->total() }} resultados
                </div>
                <div>
                    {{ $enfermeros->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
