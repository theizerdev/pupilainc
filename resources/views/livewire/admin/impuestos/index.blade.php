<div>
    @section('title', 'Configuración de Impuestos')

    @push('styles')
    <style>
        .imp-hero { background: linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .imp-hero h2 { color:#fff; margin:0; }
        .imp-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .impuesto-row { transition:background .12s; }
        .impuesto-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Configuración de Impuestos</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="imp-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-percent-line me-2"></i>Configuración de Impuestos</h2>
                <p class="mt-1">Gestión de impuestos (IVA, IGTF, etc.)</p>
            </div>
            @can('create impuestos')
                <button wire:click="crear" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nuevo Impuesto
                </button>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-percent-line"></i></div>
                    <div>
                        <div class="stat-label">Total impuestos</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Impuestos activos</div>
                        <div class="stat-value">{{ $stats['activos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Impuestos inactivos</div>
                        <div class="stat-value">{{ $stats['inactivos'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="filtro_activo">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de impuestos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none text-primary">
                                        Código
                                        @if($sortField === 'codigo')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none text-primary">
                                        Nombre
                                        @if($sortField === 'nombre')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold text-center">
                                    <a wire:click.prevent="sortBy('porcentaje')" href="#" class="text-decoration-none text-primary">
                                        Porcentaje
                                        @if($sortField === 'porcentaje')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Descripción</th>
                                <th class="fw-semibold text-center">Estado</th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($impuestos as $impuesto)
                                <tr class="impuesto-row">
                                    <td>
                                        <span class="badge bg-label-dark">{{ $impuesto->codigo }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $impuesto->nombre }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-info fs-6">{{ number_format($impuesto->porcentaje, 2) }}%</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $impuesto->descripcion ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $impuesto->id }}"
                                                   wire:click="toggleActivo({{ $impuesto->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $impuesto->activo ? 'desactivar' : 'activar' }} este impuesto?"
                                                   {{ $impuesto->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('edit impuestos')
                                                <button class="dropdown-item" wire:click="editar({{ $impuesto->id }})">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </button>
                                                @endcan
                                                @can('delete impuestos')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="eliminar({{ $impuesto->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar este impuesto?">
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
                                        <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron impuestos</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $impuestos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">
                        <i class="ri ri-{{ $editingId ? 'edit' : 'add' }}-line me-2"></i>
                        {{ $editingId ? 'Editar' : 'Nuevo' }} Impuesto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" wire:model="codigo" class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: IVA">
                            @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Porcentaje <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="porcentaje" step="0.01" class="form-control @error('porcentaje') is-invalid @enderror" placeholder="16.00">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('porcentaje') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Impuesto al Valor Agregado">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="activo" class="form-check-input" id="activo">
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardar">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
