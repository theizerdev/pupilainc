<div>
    @section('title', 'Usuarios')

    @push('styles')
    <style>
        .usuario-hero { background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .usuario-hero h2 { color:#fff; margin:0; }
        .usuario-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .usuario-row { transition:background .12s; }
        .usuario-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Usuarios</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="usuario-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-group-line me-2"></i>Usuarios</h2>
                <p class="mt-1">Gestión de usuarios y acceso al sistema</p>
            </div>
            @can('create users')
                <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nuevo Usuario
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-group-line"></i></div>
                    <div>
                        <div class="stat-label">Total usuarios</div>
                        <div class="stat-value">{{ $totalUsers }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Usuarios activos</div>
                        <div class="stat-value">{{ $activeUsers }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-time-line"></i></div>
                    <div>
                        <div class="stat-label">Usuarios pendientes</div>
                        <div class="stat-value">{{ $pendingUsers }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;color:#ef4444;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Usuarios inactivos</div>
                        <div class="stat-value">{{ $inactiveUsers }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, email, username...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Empresa</label>
                        <select class="form-select form-select-sm" wire:model.live="empresa_id">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sucursal</label>
                        <select class="form-select form-select-sm" wire:model.live="sucursal_id">
                            <option value="">Todas</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="clearFilters">
                                <i class="ri ri-eraser-line"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-label-success" wire:click="export">
                                <i class="ri ri-file-excel-line"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-group-line me-2 text-primary"></i>Listado de usuarios</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
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
                                <tr class="usuario-row">
                                    <td>
                                        {{ $user->id }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 d-flex align-items-center justify-content-center"
                                                 style="width: 42px; height: 42px; border-radius: 8px; overflow: hidden; background-color: {{ $user->profile_photo_path ? '#f3f4f6' : '#dbeafe' }};">
                                                @if($user->profile_photo_path)
                                                    <img src="{{ asset($user->profile_photo_path) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <span class="text-primary fw-bold" style="font-size: 1.1rem;">{{ substr($user->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $user->name }}</div>
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
                                            <span class="badge bg-success">
                                                <i class="ri ri-check-line me-1"></i>Verificado
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="ri ri-time-line me-1"></i>Pendiente
                                            </span>
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
                                            <span class="text-muted">{{ $user->sucursal->nombre }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check form-switch form-switch-sm">
                                                <input class="form-check-input" type="checkbox"
                                                       id="statusSwitch{{ $user->id }}"
                                                       wire:click="toggleStatus({{ $user->id }})"
                                                       wire:confirm="¿Estás seguro de {{ $user->status ? 'desactivar' : 'activar' }} este usuario?"
                                                       {{ $user->status ? 'checked' : '' }}
                                                       style="cursor: pointer;">
                                            </div>
                                            @if($user->locked_until && $user->locked_until->isFuture())
                                                <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="unlockUser({{ $user->id }})"
                                                        title="Bloqueado hasta {{ $user->locked_until->format('d/m/Y H:i') }}">
                                                    <i class="ri ri-lock-2-fill"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">

                                                @can('edit users')
                                                <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
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
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="ri ri-group-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron usuarios</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
