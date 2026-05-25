<div>
    @section('title', 'Kardex — ' . $producto->nombre)

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="ri ri-file-list-3-line me-2"></i>Kardex: {{ $producto->nombre }}</h2>
            <small class="text-muted">Código: <code>{{ $producto->codigo }}</code> · {{ $producto->unidad_medida }}</small>
        </div>
        <div class="d-flex gap-2">
            @can('create movimientos-inventario')
                <a href="{{ route('admin.inventario.movimientos.create') }}" class="btn btn-success btn-sm">
                    <i class="ri ri-add-line me-1"></i>Registrar Movimiento
                </a>
            @endcan
            <a href="{{ route('admin.inventario.productos.index') }}" class="btn btn-secondary btn-sm">
                <i class="ri ri-arrow-left-line me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- Stock por almacén -->
    <div class="row mb-4">
        @forelse($stockPorAlmacen as $stock)
            <div class="col-md-3 mb-3">
                <div class="card border-left-{{ $stock->cantidad <= $producto->stock_minimo ? 'danger' : 'success' }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">{{ $stock->almacen->nombre }}</div>
                        <div class="h4 mb-0 font-weight-bold {{ $stock->cantidad <= $producto->stock_minimo ? 'text-danger' : 'text-success' }}">
                            {{ $stock->cantidad }}
                        </div>
                        <small class="text-muted">Mín: {{ $producto->stock_minimo }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning">Sin stock registrado en ningún almacén.</div>
            </div>
        @endforelse
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Stock Total</div>
                    <div class="h4 mb-0 font-weight-bold text-primary">{{ $producto->stockTotal() }}</div>
                    <small class="text-muted">Valorización: {{ money($producto->valorizacion, 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros kardex -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-md-3">
                    <select class="form-control form-control-sm" wire:model.change="almacen_id">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control form-control-sm" wire:model.change="tipo">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposMovimiento as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" wire:model.change="fecha_desde">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" wire:model.change="fecha_hasta">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" wire:click="resetFiltros">
                        <i class="ri ri-close-line"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla kardex -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historial de Movimientos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Almacén</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Stock Ant.</th>
                            <th class="text-end">Stock Nuevo</th>
                            <th>Referencia</th>
                            <th>Usuario</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                            @php $info = $mov->tipo_info; @endphp
                            <tr>
                                <td><small>{{ $mov->created_at->format('d/m/Y H:i') }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $info['color'] }}">
                                        <i class="{{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                                    </span>
                                </td>
                                <td>{{ $mov->almacen->nombre }}</td>
                                <td class="text-end fw-bold {{ $info['signo'] === '+' ? 'text-success' : 'text-danger' }}">
                                    {{ $info['signo'] }}{{ $mov->cantidad }}
                                </td>
                                <td class="text-end text-muted">{{ $mov->stock_anterior }}</td>
                                <td class="text-end fw-bold">{{ $mov->stock_nuevo }}</td>
                                <td><small>{{ $mov->referencia ?? '-' }}</small></td>
                                <td><small>{{ $mov->user?->name ?? 'Sistema' }}</small></td>
                                <td><small class="text-muted">{{ $mov->observacion ?? '-' }}</small></td>
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
