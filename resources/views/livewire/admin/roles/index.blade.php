<div>
    @section('title', 'Roles')

    @push('styles')
    <style>
        .role-hero { background: linear-gradient(135deg, #EC4899 0%, #F43F5E 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .role-hero h2 { color:#fff; margin:0; }
        .role-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .role-row { transition:background .12s; }
        .role-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Roles</li>
        </ol>
    </nav>

    {{-- Hero Section --}}
    <div class="role-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-user-star-line me-2"></i>Roles</h2>
            <p class="mt-1">Gestión de roles y permisos del sistema</p>
        </div>
        @can('create roles')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nuevo Rol
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-user-star-line"></i></div>
                <div>
                    <div class="stat-label">Total roles</div>
                    <div class="stat-value">{{ $totalRoles }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-shield-check-line"></i></div>
                <div>
                    <div class="stat-label">Con permisos</div>
                    <div class="stat-value">{{ $rolesWithPermissions }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-shield-line"></i></div>
                <div>
                    <div class="stat-label">Sin permisos</div>
                    <div class="stat-value">{{ $rolesWithoutPermissions }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-shield-keyhole-line"></i></div>
                <div>
                    <div class="stat-label">Total permisos</div>
                    <div class="stat-value">{{ $totalPermissions }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Compact Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, guard...">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold"><i class="ri ri-shield-line me-1"></i>Guard</label>
                    <select class="form-select form-select-sm" wire:model.live="guard">
                        <option value="">Todos</option>
                        @foreach($guards as $guard)
                            <option value="{{ $guard }}">{{ $guard }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold"><i class="ri ri-list-check me-1"></i>Mostrar</label>
                    <select class="form-select form-select-sm" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                        <i class="ri ri-eraser-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <h6 class="mb-0"><i class="ri ri-user-star-line me-2 text-primary"></i>Listado de roles</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nombre @if($sortBy === 'name') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                            </th>
                            <th wire:click="sortBy('guard_name')" style="cursor: pointer;">
                                Guard @if($sortBy === 'guard_name') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                            </th>
                            <th>Permisos Asignados</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr class="role-row">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($role->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $role->name }}</h6>
                                            <small class="text-muted">{{ Str::limit($role->name, 30) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $role->guard_name }}</span>
                                </td>
                                <td>
                                    @if($role->permissions->count() > 0)
                                        <span class="badge bg-success">{{ $role->permissions->count() }} permisos</span>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                {{ $role->permissions->pluck('name')->take(3)->implode(', ') }}
                                                @if($role->permissions->count() > 3)
                                                    <span class="text-muted">+{{ $role->permissions->count() - 3 }} más</span>
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">Sin permisos</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $role->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @can('view roles')
                                            <a class="dropdown-item" href="{{ route('admin.roles.show', $role) }}">
                                                <i class="ri ri-eye-line me-1 text-info"></i> Ver
                                            </a>
                                            @endcan
                                            @can('edit roles')
                                            <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                                <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                            </a>
                                            @endcan
                                            @can('delete roles')
                                            <button type="button" class="dropdown-item text-danger"
                                                    wire:click="deleteRole({{ $role->id }})"
                                                    wire:confirm="¿Estás seguro de eliminar este rol?">
                                                <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="ri ri-user-star-line" style="font-size:1.5rem;opacity:.3;"></i>
                                    <p class="mb-0 mt-1 small">No se encontraron roles</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $roles->links('livewire.pagination') }}
            </div>
        </div>
    </div>
</div>
