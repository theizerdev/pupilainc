<div>
    @section('title', 'Órdenes de Compra')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Órdenes de Compra</h1>
            <p class="text-muted">Gestión de órdenes de compra a proveedores</p>
        </div>
        @can('create ordenes-compra')
            <a href="{{ route('admin.inventario.ordenes-compra.create') }}" class="btn btn-primary">
                <i class="ri ri-add-line me-1"></i> Nueva Orden
            </a>
        @endcan
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Activas</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['activas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Borradores</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['borradores'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o proveedor...">
                </div>
                <div class="col-md-3">
                    <select class="form-control" wire:model.change="estado">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" wire:model.change="proveedor_id">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters">
                        <i class="ri ri-close-line"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Proveedor</th>
                            <th>Almacén</th>
                            <th>Estado</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Esperada</th>
                            <th class="text-end">Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordenes as $orden)
                            @php $estadoInfo = $orden->estado_info; @endphp
                            <tr>
                                <td><span class="fw-bold">{{ $orden->numero }}</span></td>
                                <td>{{ $orden->proveedor->nombre }}</td>
                                <td>{{ $orden->almacen->nombre }}</td>
                                <td>
                                    <span class="badge bg-{{ $estadoInfo['color'] }}">{{ $estadoInfo['label'] }}</span>
                                    @if($orden->generada_automaticamente)
                                        <span class="badge bg-info ms-1" title="Generada automáticamente">Auto</span>
                                    @endif
                                </td>
                                <td>{{ $orden->fecha_emision->format('d/m/Y') }}</td>
                                <td>{{ $orden->fecha_esperada?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-end fw-bold">${{ number_format($orden->total, 2) }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @can('edit ordenes-compra')
                                                @if(!in_array($orden->estado, ['recibida', 'cancelada']))
                                                    <button class="dropdown-item text-success"
                                                            wire:click="recibirCompleta({{ $orden->id }})"
                                                            wire:confirm="¿Marcar como recibida completa? Esto actualizará el stock.">
                                                        <i class="ri ri-checkbox-circle-line me-1"></i> Recibir completa
                                                    </button>
                                                    <button class="dropdown-item text-danger"
                                                            wire:click="cancelar({{ $orden->id }})"
                                                            wire:confirm="¿Cancelar esta orden?">
                                                        <i class="ri ri-close-circle-line me-1"></i> Cancelar
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron órdenes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $ordenes->links('livewire.pagination') }}
        </div>
    </div>
</div>
