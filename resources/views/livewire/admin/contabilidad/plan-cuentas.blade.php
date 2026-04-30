<div>
    @section('title', 'Plan de Cuentas')

    @push('styles')
    <style>
        .pc-hero { background: linear-gradient(135deg, #3B82F6 0%, #6366F1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .pc-hero h2 { color:#fff; margin:0; }
        .pc-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .cuenta-row { transition:background .12s; }
        .cuenta-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Plan de Cuentas</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="pc-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-file-list-3-line me-2"></i>Plan de Cuentas</h2>
                <p class="mt-1">Gestión del plan de cuentas contables</p>
            </div>
            @can('create contabilidad')
                <button wire:click="create" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Cuenta
                </button>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-file-list-3-line"></i></div>
                    <div>
                        <div class="stat-label">Total cuentas</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Cuentas activas</div>
                        <div class="stat-value">{{ $stats['activas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Cuentas inactivas</div>
                        <div class="stat-value">{{ $stats['inactivas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-exchange-line"></i></div>
                    <div>
                        <div class="stat-label">Con movimientos</div>
                        <div class="stat-value">{{ $stats['con_movimientos'] }}</div>
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
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="pasivo">Pasivo</option>
                            <option value="patrimonio">Patrimonio</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                            <option value="costo">Costo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Naturaleza</label>
                        <select class="form-select form-select-sm" wire:model.live="naturaleza">
                            <option value="">Todas</option>
                            <option value="deudora">Deudora</option>
                            <option value="acreedora">Acreedora</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Nivel</label>
                        <select class="form-select form-select-sm" wire:model.live="nivel">
                            <option value="">Todos</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de cuentas contables</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none text-white">
                                        Código
                                        @if($sortField === 'codigo')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none text-white">
                                        Nombre
                                        @if($sortField === 'nombre')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('tipo')" href="#" class="text-decoration-none text-white">
                                        Tipo
                                        @if($sortField === 'tipo')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('naturaleza')" href="#" class="text-decoration-none text-white">
                                        Naturaleza
                                        @if($sortField === 'naturaleza')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('nivel')" href="#" class="text-decoration-none text-white">
                                        Nivel
                                        @if($sortField === 'nivel')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Movimientos</th>
                                <th class="fw-semibold">Estado</th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $cuenta)
                                @php
                                    $tipoBadge = match($cuenta->tipo) {
                                        'activo'    => 'bg-label-success',
                                        'pasivo'    => 'bg-label-danger',
                                        'patrimonio'=> 'bg-label-info',
                                        'ingreso'   => 'bg-label-primary',
                                        default     => 'bg-label-warning',
                                    };
                                @endphp
                                <tr class="cuenta-row">
                                    <td class="fw-semibold">{{ $cuenta->codigo }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $cuenta->nombre }}</div>
                                        @if($cuenta->descripcion)
                                            <small class="text-muted">{{ Str::limit($cuenta->descripcion, 50) }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $tipoBadge }}">{{ ucfirst($cuenta->tipo) }}</span></td>
                                    <td>{{ ucfirst($cuenta->naturaleza) }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $cuenta->nivel }}</span></td>
                                    <td>
                                        @if($cuenta->acepta_movimientos)
                                            <span class="text-success"><i class="ri ri-check-line me-1"></i>Sí</span>
                                        @else
                                            <span class="text-danger"><i class="ri ri-close-line me-1"></i>No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $cuenta->id }}"
                                                   wire:click="toggleStatus({{ $cuenta->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $cuenta->activo ? 'desactivar' : 'activar' }} esta cuenta?"
                                                   {{ $cuenta->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('edit contabilidad')
                                                <button class="dropdown-item" wire:click="edit({{ $cuenta->id }})">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </button>
                                                @endcan
                                                @can('delete contabilidad')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $cuenta->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta cuenta?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron cuentas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $cuentas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Editar' : 'Nueva' }} Cuenta Contable</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código *</label>
                            <input type="text" wire:model="codigo" class="form-control @error('codigo') is-invalid @enderror">
                            @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel *</label>
                            <input type="number" wire:model="nivel_cuenta" class="form-control @error('nivel_cuenta') is-invalid @enderror" min="1" max="10">
                            @error('nivel_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombre *</label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo *</label>
                            <select wire:model="tipo_cuenta" class="form-select @error('tipo_cuenta') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                <option value="activo">Activo</option>
                                <option value="pasivo">Pasivo</option>
                                <option value="patrimonio">Patrimonio</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="costo">Costo</option>
                            </select>
                            @error('tipo_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Naturaleza *</label>
                            <select wire:model="naturaleza_cuenta" class="form-select @error('naturaleza_cuenta') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                <option value="deudora">Deudora</option>
                                <option value="acreedora">Acreedora</option>
                            </select>
                            @error('naturaleza_cuenta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cuenta Padre</label>
                            <select wire:model="cuenta_padre_id" class="form-select">
                                <option value="">Sin cuenta padre</option>
                                @foreach($cuentasPadre as $padre)
                                    <option value="{{ $padre->id }}">{{ $padre->codigo }} — {{ $padre->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opciones</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="acepta_movimientos" class="form-check-input" id="acepta_movimientos">
                                <label class="form-check-label" for="acepta_movimientos">Acepta movimientos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="activo" class="form-check-input" id="activo">
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
