<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Configuración de Impuestos</h6>
                            @can('access impuestos')
                            <button wire:click="crear" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo Impuesto
                            </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm" placeholder="🔍 Buscar por código o nombre...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select wire:model.live="filtro_activo" class="form-select form-select-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Código</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Porcentaje</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Descripción</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($impuestos as $impuesto)
                                    <tr>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-dark">{{ $impuesto->codigo }}</span>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $impuesto->nombre }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-sm bg-gradient-info">{{ number_format($impuesto->porcentaje, 2) }}%</span>
                                        </td>
                                        <td>
                                            <p class="text-xs text-secondary mb-0">{{ $impuesto->descripcion ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            @can('access impuestos')
                                            <button wire:click="toggleActivo({{ $impuesto->id }})" class="btn btn-link p-0 m-0">
                                                @if($impuesto->activo)
                                                <span class="badge badge-sm bg-gradient-success">Activo</span>
                                                @else
                                                <span class="badge badge-sm bg-gradient-secondary">Inactivo</span>
                                                @endif
                                            </button>
                                            @else
                                            @if($impuesto->activo)
                                            <span class="badge badge-sm bg-gradient-success">Activo</span>
                                            @else
                                            <span class="badge badge-sm bg-gradient-secondary">Inactivo</span>
                                            @endif
                                            @endcan
                                        </td>
                                        <td class="text-center">
                                            @can('access impuestos')
                                            <div class="dropdown">
                                                <button class="btn btn-link text-secondary mb-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" wire:click.prevent="editar({{ $impuesto->id }})">
                                                        <i class="fas fa-edit me-2"></i> Editar
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="if(confirm('¿Eliminar este impuesto?')) @this.call('eliminar', {{ $impuesto->id }})">
                                                        <i class="fas fa-trash me-2"></i> Eliminar
                                                    </a></li>
                                                </ul>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                                            <p class="text-secondary mb-0">No hay impuestos registrados</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $impuestos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($modal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-{{ $impuesto_id ? 'edit' : 'plus' }} me-2"></i>
                        {{ $impuesto_id ? 'Editar' : 'Nuevo' }} Impuesto
                    </h5>
                    <button type="button" wire:click="$set('modal', false)" class="btn-close btn-close-white"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input wire:model="codigo" type="text" class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: IVA">
                            @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Impuesto al Valor Agregado">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Porcentaje <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input wire:model="porcentaje" type="number" step="0.01" class="form-control @error('porcentaje') is-invalid @enderror" placeholder="16.00">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('porcentaje') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input wire:model="activo" class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="$set('modal', false)" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const type = event.type || 'info';
                const message = event.message || 'Operación realizada';
                
                if (typeof showNotification === 'function') {
                    showNotification(message, type);
                } else {
                    alert(message);
                }
            });
        });
    </script>
    @endpush
</div>
