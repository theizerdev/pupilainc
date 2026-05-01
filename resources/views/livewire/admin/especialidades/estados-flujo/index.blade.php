<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Estados del Flujo</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-flow-chart me-2"></i>Estados del Flujo</h2>
                <p class="mt-1 text-muted">Administra los estados disponibles en la plantilla de consulta</p>
            </div>
            <button class="btn btn-primary" wire:click="abrirModal">
                <i class="ri ri-add-line me-1"></i> Nuevo Estado
            </button>
        </div>

        {{-- KPI cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-sm-6">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="icon-tile" style="background:#E0E7FF;color:#3B82F6"><i class="ri ri-flow-chart ri-lg"></i></span>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                            <small class="text-muted">Total Estados</small>
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
                            <th>Color</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr wire:key="ef-{{ $item->id }}">
                            <td><code>{{ $item->codigo }}</code></td>
                            <td>
                                <strong>{{ $item->nombre }}</strong>
                            </td>
                            <td>
                                <span class="badge" style="background-color:{{ $item->color }};font-size:.85rem;padding:.4rem .8rem">
                                    {{ $item->color }}
                                </span>
                            </td>
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
                                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminar({{ $item->id }})" wire:confirm="¿Eliminar este estado? Se removerá de todas las plantillas." title="Eliminar">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="ri ri-flow-chart ri-2x mb-3 d-block"></i>
                                No hay estados configurados. Crea el primero con el botón superior.
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
    @php
        $coloresPreset = ['#3B82F6','#6366F1','#8B5CF6','#EC4899','#EF4444','#F59E0B','#10B981','#14B8A6','#0EA5E9','#64748B','#9E9E9E','#FFA726','#42A5F5'];
    @endphp
    <div class="modal fade show d-block custom-modal-backdrop" tabindex="-1"
         wire:click.self="$set('showModal', false)"
         wire:keydown.escape.window="$set('showModal', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#F59E0B,#EF4444);color:#fff;border-bottom:0">
                    <h5 class="modal-title text-white">
                        <i class="ri {{ $editingId ? 'ri-edit-line' : 'ri-add-line' }} me-2"></i>
                        {{ $editingId ? 'Editar Estado' : 'Nuevo Estado' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código (único, minúsculas y guiones bajos)</label>
                        <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                               wire:model="codigo" placeholder="ej: sala_espera">
                        @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                               wire:model="nombre" placeholder="Ej: Sala de Espera">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="color" class="form-control form-control-color" wire:model.live="color" style="width:50px;height:38px">
                            <input type="text" class="form-control" wire:model.live="color" placeholder="#42A5F5" maxlength="7">
                        </div>
                        <div class="color-swatches">
                            @foreach($coloresPreset as $c)
                                <span class="swatch {{ strtoupper($color) === strtoupper($c) ? 'selected' : '' }}"
                                      style="background:{{ $c }}"
                                      wire:click="$set('color', '{{ $c }}')"
                                      title="{{ $c }}"></span>
                            @endforeach
                        </div>
                        @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted d-block mb-2">Vista previa</small>
                        <span class="badge" style="background-color:{{ $color }};font-size:1rem;padding:.6rem 1rem">
                            {{ $nombre ?: 'Estado' }}
                        </span>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" wire:model="activo" id="efActivo">
                        <label class="form-check-label" for="efActivo">Activo</label>
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
