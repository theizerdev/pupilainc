<div class="w-100">
    @section('title', 'Almacenes')

    @push('styles')
    <style>
        /* Hero Section */
        .almacenes-hero {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .almacenes-hero h2 {
            color: #fff;
            margin: 0;
        }
        .almacenes-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Cards - Compact Style */
        .stat-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
        }
        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .stat-card .stat-value {
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-almacenes thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-almacenes tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .almacen-row { transition: background 0.15s; }
        .almacen-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="almacenes-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-building-line me-2"></i>Almacenes</h2>
            <p class="mt-1">Gestión de almacenes y ubicaciones de inventario</p>
        </div>
        @can('create almacenes')
            <button class="btn btn-light btn-sm" wire:click="openModal()">
                <i class="ri ri-add-line me-1"></i>Nuevo Almacén
            </button>
        @endcan
    </div>

    {{-- Stat Cards --}}
    @php
        $totalAlmacenes = \App\Models\Almacen::forUser()->count();
        $almacenesActivos = \App\Models\Almacen::forUser()->where('status', true)->count();
        $almacenesInactivos = \App\Models\Almacen::forUser()->where('status', false)->count();
        $almacenesPrincipales = \App\Models\Almacen::forUser()->where('es_principal', true)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total almacenes</div>
                    <div class="stat-value">{{ $totalAlmacenes }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="stat-label">Activos</div>
                    <div class="stat-value text-success">{{ $almacenesActivos }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-pause-circle-line"></i></div>
                <div>
                    <div class="stat-label">Inactivos</div>
                    <div class="stat-value text-warning">{{ $almacenesInactivos }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="ri ri-star-line"></i></div>
                <div>
                    <div class="stat-label">Principales</div>
                    <div class="stat-value text-primary">{{ $almacenesPrincipales }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar almacén...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.change="status">
                        <option value="">Todos los estados</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        wire:click="$set('selected', {{ json_encode($almacenes->pluck('id')) }})">
                    Seleccionar página
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('selected', [])">
                    Deseleccionar
                </button>
                @can('delete almacenes')
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="confirm('¿Eliminar los almacenes seleccionados?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected" @if(!count($selected)) disabled @endif>
                        <i class="ri ri-delete-bin-line me-1"></i>Eliminar seleccionados
                        @if(count($selected))
                            <span class="badge bg-white text-danger ms-2">{{ count($selected) }}</span>
                        @endif
                    </button>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-almacenes">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Principal</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($almacenes as $almacen)
                        <tr class="almacen-row">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="selected" value="{{ $almacen->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $almacen->nombre }}</div>
                                @if($almacen->descripcion)
                                    <small class="text-muted">{{ $almacen->descripcion }}</small>
                                @endif
                            </td>
                            <td>{{ $almacen->ubicacion ?? '-' }}</td>
                            <td>
                                @if($almacen->es_principal)
                                    <span class="badge rounded-pill bg-primary-label"><i class="ri ri-star-line me-1"></i>Principal</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $almacen->status ? 'success' : 'secondary' }}-label">
                                    {{ $almacen->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('edit almacenes')
                                            <li>
                                                <button class="dropdown-item" wire:click="openModal({{ $almacen->id }})">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </button>
                                            </li>
                                        @endcan
                                        @can('delete almacenes')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $almacen->id }})"
                                                        wire:confirm="¿Eliminar este almacén?">
                                                    <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">No se encontraron almacenes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($almacenes->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $almacenes->links('livewire.pagination') }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Editar' : 'Nuevo' }} Almacén</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Nombre *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre">
                        @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label>Descripción</label>
                        <input type="text" class="form-control" wire:model="descripcion">
                    </div>
                    <div class="form-group mb-3">
                        <label>Ubicación física</label>
                        <input type="text" class="form-control" wire:model="ubicacion" placeholder="Ej: Piso 2, Sala B">
                    </div>
                    <div class="d-flex gap-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="es_principal" id="esPrincipal">
                            <label class="form-check-label" for="esPrincipal">Almacén principal</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="modalStatus" id="modalStatus">
                            <label class="form-check-label" for="modalStatus">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showModal', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="save">
                        <i class="ri ri-save-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
