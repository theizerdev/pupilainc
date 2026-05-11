<div>
    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pasos del Proceso</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-list-check me-2"></i>Pasos del Proceso</h2>
                <p class="mt-1 text-muted">Administra los pasos disponibles en la plantilla de consulta</p>
            </div>
            <button class="btn btn-primary" wire:click="abrirModal">
                <i class="ri ri-add-line me-1"></i> Nuevo Paso
            </button>
        </div>

        {{-- KPI cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-sm-6">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="icon-tile" style="background:#E0E7FF;color:#3B82F6"><i class="ri ri-list-check ri-lg"></i></span>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                            <small class="text-muted">Total Pasos</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="icon-tile" style="background:#D1FAE5;color:#10B981"><i class="ri ri-checkbox-circle-line ri-lg"></i></span>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['activos'] }}</h4>
                            <small class="text-muted">Activos</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="icon-tile" style="background:#FEE2E2;color:#EF4444"><i class="ri ri-close-circle-line ri-lg"></i></span>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['inactivos'] }}</h4>
                            <small class="text-muted">Inactivos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search"
                               placeholder="Buscar por nombre o código...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th wire:click="sortBy('codigo')" style="cursor:pointer">
                                Código @if($sortField==='codigo')<i class="ri-arrow-{{$sortDirection==='asc'?'up':'down'}}-s-line"></i>@endif
                            </th>
                            <th wire:click="sortBy('nombre')" style="cursor:pointer">
                                Nombre @if($sortField==='nombre')<i class="ri-arrow-{{$sortDirection==='asc'?'up':'down'}}-s-line"></i>@endif
                            </th>
                            <th>Ícono</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr wire:key="pp-{{ $item->id }}">
                            <td><code>{{ $item->codigo }}</code></td>
                            <td>
                                <strong>{{ $item->nombre }}</strong>
                            </td>
                            <td><i class="ri {{ $item->icono }} ri-lg text-primary"></i></td>
                            <td>
                                <span class="badge bg-label-{{ $item->activo ? 'success' : 'danger' }}">
                                    {{ $item->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="toggleActivo({{ $item->id }})" title="{{ $item->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="ri {{ $item->activo ? 'ri-close-circle-line' : 'ri-check-circle-line' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="abrirModal({{ $item->id }})" title="Editar">
                                        <i class="ri ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminar({{ $item->id }})" wire:confirm="¿Eliminar este paso? Se removerá de todas las plantillas." title="Eliminar">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="ri ri-list-check ri-2x mb-3 d-block"></i>
                                No hay pasos configurados. Crea el primero con el botón superior.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $items->links('livewire.pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('showModal', false)"
         wire:keydown.escape.window="$set('showModal', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#3B82F6,#6366F1);color:#fff;border-bottom:0">
                    <h5 class="modal-title text-white">
                        <i class="ri {{ $editingId ? 'ri-edit-line' : 'ri-add-line' }} me-2"></i>
                        {{ $editingId ? 'Editar Paso' : 'Nuevo Paso' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código (único, minúsculas y guiones bajos)</label>
                        <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                               wire:model="codigo" placeholder="ej: signos_vitales">
                        @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                               wire:model="nombre" placeholder="Ej: Signos Vitales">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ícono (clase Remix)</label>
                        <input type="text" class="form-control @error('icono') is-invalid @enderror"
                               wire:model="icono" placeholder="ri-checkbox-circle-line">
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <small class="text-muted">Vista previa:</small>
                            <span class="badge bg-primary"><i class="ri {{ $icono }} me-1"></i>{{ $nombre ?: 'Paso' }}</span>
                        </div>
                        @error('icono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="activo" id="ppActivo">
                        <label class="form-check-label" for="ppActivo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar"><i class="ri ri-save-line me-1"></i>Guardar</span>
                        <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm me-1"></span>Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
