<div>
    @section('title', 'Permisos')

    @push('styles')
    <style>
        .permission-hero { background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .permission-hero h2 { color:#fff; margin:0; }
        .permission-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .permission-row { transition:background .12s; }
        .permission-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permisos</li>
        </ol>
    </nav>

    {{-- Hero Section --}}
    <div class="permission-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-shield-keyhole-line me-2"></i>Permisos</h2>
            <p class="mt-1">Gestión de permisos del sistema organizados por módulos</p>
        </div>
        @can('create permissions')
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nuevo Permiso
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-shield-keyhole-line"></i></div>
                <div>
                    <div class="stat-label">Total permisos</div>
                    <div class="stat-value">{{ $totalPermissions }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-user-settings-line"></i></div>
                <div>
                    <div class="stat-label">Con roles</div>
                    <div class="stat-value">{{ $permissionsWithRoles }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-shield-line"></i></div>
                <div>
                    <div class="stat-label">Sin roles</div>
                    <div class="stat-value">{{ $permissionsWithoutRoles }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-folder-line"></i></div>
                <div>
                    <div class="stat-label">Módulos únicos</div>
                    <div class="stat-value">{{ $uniqueModules }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Compact Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nombre, módulo...">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold"><i class="ri ri-folder-line me-1"></i>Módulo</label>
                    <select class="form-select form-select-sm" wire:model.live="module">
                        <option value="">Todos</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
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
            <h6 class="mb-0"><i class="ri ri-shield-keyhole-line me-2 text-primary"></i>Listado de permisos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nombre @if($sortBy === 'name') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                            </th>
                            <th wire:click="sortBy('module')" style="cursor: pointer;">
                                Módulo @if($sortBy === 'module') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                            </th>
                            <th wire:click="sortBy('guard_name')" style="cursor: pointer;">
                                Guard @if($sortBy === 'guard_name') <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-line"></i> @endif
                            </th>
                            <th>Roles Asignados</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr class="permission-row">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($permission->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $permission->name }}</h6>
                                            <small class="text-muted">{{ Str::limit($permission->name, 30) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($permission->module) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $permission->guard_name }}</span>
                                </td>
                                <td>
                                    @if($permission->roles->count() > 0)
                                        <span class="badge bg-success">{{ $permission->roles->count() }} roles</span>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                {{ $permission->roles->pluck('name')->take(3)->implode(', ') }}
                                                @if($permission->roles->count() > 3)
                                                    <span class="text-muted">+{{ $permission->roles->count() - 3 }} más</span>
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">Sin roles</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $permission->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @can('edit permissions')
                                            <a class="dropdown-item" href="{{ route('admin.permissions.edit', $permission) }}">
                                                <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                            </a>
                                            @endcan
                                            @can('delete permissions')
                                            <button type="button" class="dropdown-item text-danger"
                                                    wire:click="deletePermission({{ $permission->id }})"
                                                    wire:confirm="¿Estás seguro de eliminar este permiso?">
                                                <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="ri ri-shield-keyhole-line" style="font-size:1.5rem;opacity:.3;"></i>
                                    <p class="mb-0 mt-1 small">No se encontraron permisos</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $permissions->links('livewire.pagination') }}
            </div>
        </div>
    </div>
</div>
