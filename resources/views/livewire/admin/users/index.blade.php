<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Usuarios</h6>
                            <h2 class="mb-0">{{ $totalUsers }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="ri ri-group-line text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-success fw-semibold">
                            <i class="ri ri-arrow-up-line"></i> {{ round(($activeUsers/$totalUsers)*100) }}% Activos
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Usuarios Activos</h6>
                            <h2 class="mb-0">{{ $activeUsers }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="ri ri-checkbox-circle-line text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Usuarios Pendientes</h6>
                            <h2 class="mb-0">{{ $pendingUsers }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="ri ri-time-line text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Usuarios Inactivos</h6>
                            <h2 class="mb-0">{{ $inactiveUsers }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="ri ri-close-circle-line text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Lista de Usuarios</h5>
                            <p class="mb-0">Administra los usuarios registrados en el sistema</p>
                        </div>
                        @can('create users')
                        <div>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="ri ri-add-line"></i> Nuevo Usuario
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card-header border-bottom">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" placeholder="Nombre, email, username..."
                                   wire:model.live.debounce.300ms="search">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Empresa</label>
                            <select class="form-select" wire:model.live="empresa_id">
                                <option value="">Todas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Sucursal</label>
                            <select class="form-select" wire:model.live="sucursal_id">
                                <option value="">Todas</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" wire:model.live="status">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-label-secondary" wire:click="clearFilters">
                                <i class="ri ri-eraser-line"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-label-success" wire:click="export">
                                <i class="mdi mdi-file-excel"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-datatable table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('id')" style="cursor: pointer;">
                                    # @if($sortBy === 'id') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('name')" style="cursor: pointer;">
                                    Nombre @if($sortBy === 'name') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('email')" style="cursor: pointer;">
                                    Email @if($sortBy === 'email') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('email_verified_at')" style="cursor: pointer;">
                                    Verificado @if($sortBy === 'email_verified_at') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('empresa.razon_social')" style="cursor: pointer;">
                                    Empresa @if($sortBy === 'empresa.razon_social') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th>Sucursal</th>
                                <th wire:click="sortBy('status')" style="cursor: pointer;">
                                    Estado @if($sortBy === 'status') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                    Registro @if($sortBy === 'created_at') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($user->profile_photo_path)
                                                <img src="{{ asset($user->profile_photo_path) }}" alt="{{ $user->name }}" class="rounded-circle me-2" width="32" height="32">
                                            @else
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div style="max-width: 200px;">
                                                <h6 class="mb-0 text-truncate" title="{{ $user->name }}">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $user->email }}">
                                            {{ $user->email }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verificado</span>
                                        @else
                                            <span class="badge bg-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->empresa)
                                            <span class="badge bg-primary">{{ $user->empresa->razon_social }}</span>
                                        @else
                                            <span class="badge bg-secondary">Sin empresa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->sucursal)
                                            {{ $user->sucursal->nombre }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $user->id }}"
                                                   {{ $user->status ? 'checked' : '' }}
                                                   @can('edit users') wire:click="toggleStatus({{ $user->id }})" @endcan>
                                            <label class="form-check-label" for="statusSwitch{{ $user->id }}">
                                                {{ $user->status ? 'Activo' : 'Inactivo' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                               
                                                @can('edit users')
                                                <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete users')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $user->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este usuario?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No se encontraron usuarios</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                   {{ $users->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
