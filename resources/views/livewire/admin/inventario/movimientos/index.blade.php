<div>
    @section('title', 'Movimientos de Inventario')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Movimientos de Inventario</h1>
            <p class="text-muted">Historial global de entradas, salidas y ajustes</p>
        </div>
        @can('create movimientos-inventario')
            <a href="{{ route('admin.inventario.movimientos.create') }}" class="btn btn-primary">
                <i class="ri ri-add-line me-1"></i> Registrar Movimiento
            </a>
        @endcan
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar producto...">
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="almacen_id">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="tipo">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposMovimiento as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.change="fecha_desde" placeholder="Desde">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.change="fecha_hasta" placeholder="Hasta">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Almacén</th>
                            <th>Tipo</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Stock Ant.</th>
                            <th class="text-end">Stock Nuevo</th>
                            <th>Referencia</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                            @php $info = $mov->tipo_info; @endphp
                            <tr>
                                <td><small>{{ $mov->created_at->format('d/m/Y H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.inventario.productos.show', $mov->producto_id) }}" class="text-decoration-none fw-semibold">
                                        {{ $mov->producto->nombre }}
                                    </a>
                                    <small class="text-muted d-block"><code>{{ $mov->producto->codigo }}</code></small>
                                </td>
                                <td>{{ $mov->almacen->nombre }}</td>
                                <td>
                                    <span class="badge bg-{{ $info['color'] }}">
                                        <i class="{{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold {{ $info['signo'] === '+' ? 'text-success' : 'text-danger' }}">
                                    {{ $info['signo'] }}{{ $mov->cantidad }}
                                </td>
                                <td class="text-end text-muted">{{ $mov->stock_anterior }}</td>
                                <td class="text-end fw-bold">{{ $mov->stock_nuevo }}</td>
                                <td><small>{{ $mov->referencia ?? '-' }}</small></td>
                                <td><small>{{ $mov->user?->name ?? 'Sistema' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Sin movimientos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $movimientos->links('livewire.pagination') }}
        </div>
    </div>
</div>
