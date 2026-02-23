<div>
    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Baremos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list-ul fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Baremos Activos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Baremos Inactivos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactivos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Con IVA
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['con_iva'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta principal -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Gestión de Baremos</h6>
                            <button wire:click="crear" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo Baremo
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros avanzados -->
                        <div class="row mb-3">
                            <div class="col-md-3 mb-2">
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm" placeholder="🔍 Buscar por código o nombre...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select wire:model.live="filtro_especialidad" class="form-select form-select-sm">
                                    <option value="">Todas las especialidades</option>
                                    @foreach($especialidades as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select wire:model.live="filtro_estado" class="form-select form-select-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select wire:model.live="filtro_iva" class="form-select form-select-sm">
                                    <option value="">Todos (IVA)</option>
                                    <option value="aplica">Aplica IVA</option>
                                    <option value="exento">Exento IVA</option>
                                    <option value="no_aplica">No aplica IVA</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 text-end">
                                <button wire:click="limpiarFiltros" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button wire:click="cambiarVista('tabla')" class="btn btn-outline-primary {{ $vista === 'tabla' ? 'active' : '' }}">
                                        <i class="fas fa-table"></i>
                                    </button>
                                    <button wire:click="cambiarVista('tarjetas')" class="btn btn-outline-primary {{ $vista === 'tarjetas' ? 'active' : '' }}">
                                        <i class="fas fa-th"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Vista de Tabla -->
                        @if($vista === 'tabla')
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Código</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Servicio</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Especialidad</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Costo USD</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Costo Bs</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Duración</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">IVA</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($baremos as $baremo)
                                    <tr>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-dark">{{ $baremo->codigo }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm">{{ $baremo->nombre_servicio }}</h6>
                                                @if($baremo->descripcion)
                                                <p class="text-xs text-secondary mb-0">{{ Str::limit($baremo->descripcion, 50) }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm" style="background-color: {{ $baremo->especialidad->color ?? '#6c757d' }}">
                                                {{ $baremo->especialidad->nombre ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-sm font-weight-bold">${{ number_format($baremo->costo_usd, 2) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-sm text-secondary">Bs {{ number_format($baremo->costo_usd * $tasa_usd, 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs">{{ $baremo->duracion_minutos ?? 30 }} min</span>
                                        </td>
                                        <td class="text-center">
                                            @if($baremo->exento_iva)
                                            <span class="badge badge-sm bg-gradient-warning">Exento</span>
                                            @elseif($baremo->aplica_iva)
                                            <span class="badge badge-sm bg-gradient-success">Aplica</span>
                                            @else
                                            <span class="badge badge-sm bg-gradient-secondary">No aplica</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="toggleEstado({{ $baremo->id }})" class="btn btn-link p-0 m-0">
                                                @if($baremo->activo)
                                                <span class="badge badge-sm bg-gradient-success">Activo</span>
                                                @else
                                                <span class="badge badge-sm bg-gradient-secondary">Inactivo</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="editar({{ $baremo->id }})" class="btn btn-link text-info p-0 m-0 me-2" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="duplicar({{ $baremo->id }})" class="btn btn-link text-warning p-0 m-0 me-2" title="Duplicar">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button onclick="confirmarEliminacion({{ $baremo->id }})" class="btn btn-link text-danger p-0 m-0" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                                            <p class="text-secondary mb-0">No hay baremos registrados</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @else
                        <!-- Vista de Tarjetas -->
                        <div class="row">
                            @forelse($baremos as $baremo)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-gradient-dark">{{ $baremo->codigo }}</span>
                                            <button wire:click="toggleEstado({{ $baremo->id }})" class="btn btn-link p-0 m-0">
                                                @if($baremo->activo)
                                                <span class="badge bg-gradient-success">Activo</span>
                                                @else
                                                <span class="badge bg-gradient-secondary">Inactivo</span>
                                                @endif
                                            </button>
                                        </div>
                                        <h6 class="mb-2">{{ $baremo->nombre_servicio }}</h6>
                                        @if($baremo->descripcion)
                                        <p class="text-sm text-secondary mb-3">{{ Str::limit($baremo->descripcion, 80) }}</p>
                                        @endif
                                        <div class="mb-2">
                                            <span class="badge" style="background-color: {{ $baremo->especialidad->color ?? '#6c757d' }}">
                                                {{ $baremo->especialidad->nombre ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <p class="text-sm mb-0 text-secondary">Costo</p>
                                                <h5 class="mb-0">${{ number_format($baremo->costo_usd, 2) }}</h5>
                                                <small class="text-xs text-secondary">Bs {{ number_format($baremo->costo_usd * $tasa_usd, 2) }}</small>
                                            </div>
                                            <div class="text-end">
                                                <p class="text-sm mb-0 text-secondary">Duración</p>
                                                <h6 class="mb-0">{{ $baremo->duracion_minutos ?? 30 }} min</h6>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            @if($baremo->exento_iva)
                                            <span class="badge badge-sm bg-gradient-warning">Exento IVA</span>
                                            @elseif($baremo->aplica_iva)
                                            <span class="badge badge-sm bg-gradient-success">Aplica IVA</span>
                                            @else
                                            <span class="badge badge-sm bg-gradient-secondary">No aplica IVA</span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button wire:click="editar({{ $baremo->id }})" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="duplicar({{ $baremo->id }})" class="btn btn-sm btn-warning">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button onclick="confirmarEliminacion({{ $baremo->id }})" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                                <p class="text-secondary mb-0">No hay baremos registrados</p>
                            </div>
                            @endforelse
                        </div>
                        @endif

                        <!-- Paginación -->
                        <div class="mt-3">
                            {{ $baremos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($modal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-{{ $baremo_id ? 'edit' : 'plus' }} me-2"></i>
                        {{ $baremo_id ? 'Editar' : 'Nuevo' }} Baremo
                    </h5>
                    <button type="button" wire:click="$set('modal', false)" class="btn-close btn-close-white"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Especialidad <span class="text-danger">*</span></label>
                                <select wire:model="especialidad_id" class="form-select @error('especialidad_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach($especialidades as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('especialidad_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código <span class="text-danger">*</span></label>
                                <input wire:model="codigo" type="text" class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: CONS-001">
                                @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                                <input wire:model="nombre_servicio" type="text" class="form-control @error('nombre_servicio') is-invalid @enderror" placeholder="Ej: Consulta General">
                                @error('nombre_servicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea wire:model="descripcion" class="form-control" rows="2" placeholder="Descripción opcional del servicio"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Costo USD <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input wire:model="costo_usd" type="number" step="0.01" class="form-control @error('costo_usd') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('costo_usd') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @if($costo_usd)
                                <small class="text-muted">≈ Bs {{ number_format($costo_usd * $tasa_usd, 2) }}</small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duración (minutos)</label>
                                <div class="input-group">
                                    <input wire:model="duracion_minutos" type="number" class="form-control @error('duracion_minutos') is-invalid @enderror" placeholder="30">
                                    <span class="input-group-text">min</span>
                                </div>
                                @error('duracion_minutos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input wire:model="aplica_iva" class="form-check-input" type="checkbox" id="aplica_iva">
                                            <label class="form-check-label" for="aplica_iva">Aplica IVA</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input wire:model="exento_iva" class="form-check-input" type="checkbox" id="exento_iva">
                                            <label class="form-check-label" for="exento_iva">Exento IVA</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input wire:model="activo" class="form-check-input" type="checkbox" id="activo">
                                            <label class="form-check-label" for="activo">Activo</label>
                                        </div>
                                    </div>
                                </div>
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
        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro de eliminar este baremo?')) {
                @this.call('eliminar', id);
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const type = event.type || 'info';
                const message = event.message || 'Operación realizada';
                
                // Usar el sistema de notificaciones del template si existe
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
