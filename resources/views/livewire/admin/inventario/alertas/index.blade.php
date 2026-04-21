<div>
    @section('title', 'Alertas de Inventario')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="ri ri-alarm-warning-line me-2 text-danger"></i>Alertas de Inventario</h1>
            <p class="text-muted">Productos que requieren atención inmediata</p>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        @php
        $tabs = [
            'sin_stock'  => ['label' => 'Sin Stock',     'color' => 'danger',  'icon' => 'ri-close-circle-line'],
            'critico'    => ['label' => 'Stock Crítico',  'color' => 'warning', 'icon' => 'ri-error-warning-line'],
            'stock_bajo' => ['label' => 'Stock Bajo',     'color' => 'info',    'icon' => 'ri-arrow-down-circle-line'],
            'por_vencer' => ['label' => 'Por Vencer',     'color' => 'primary', 'icon' => 'ri-time-line'],
            'vencidos'   => ['label' => 'Vencidos',       'color' => 'dark',    'icon' => 'ri-skull-line'],
        ];
        @endphp

        @foreach($tabs as $key => $tabInfo)
            <div class="col mb-3">
                <div class="card border-left-{{ $tabInfo['color'] }} shadow h-100 py-2 {{ $tab === $key ? 'border-2' : '' }}"
                     style="cursor:pointer;" wire:click="$set('tab', '{{ $key }}')">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-{{ $tabInfo['color'] }} text-uppercase mb-1">
                            {{ $tabInfo['label'] }}
                        </div>
                        <div class="h4 mb-0 font-weight-bold {{ $resumen[$key] > 0 ? 'text-'.$tabInfo['color'] : '' }}">
                            {{ $resumen[$key] }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tabla de alertas -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex align-items-center gap-2">
            <i class="{{ $tabs[$tab]['icon'] }} text-{{ $tabs[$tab]['color'] }}"></i>
            <h6 class="m-0 font-weight-bold text-{{ $tabs[$tab]['color'] }}">{{ $tabs[$tab]['label'] }}</h6>
        </div>
        <div class="card-body">
            @if($alertas[$tab]->isEmpty())
                <div class="text-center py-5">
                    <i class="ri ri-checkbox-circle-line text-success" style="font-size:3rem;"></i>
                    <p class="text-muted mt-2">¡Sin alertas en esta categoría!</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-end">Stock Total</th>
                                <th class="text-end">Mínimo</th>
                                @if(in_array($tab, ['por_vencer', 'vencidos']))
                                    <th>Vencimiento</th>
                                    <th>Días</th>
                                @endif
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertas[$tab] as $producto)
                                <tr>
                                    <td><code>{{ $producto->codigo }}</code></td>
                                    <td>
                                        <div class="fw-semibold">{{ $producto->nombre }}</div>
                                        @if($producto->requiere_receta)
                                            <span class="badge bg-danger" style="font-size:.65rem">Receta</span>
                                        @endif
                                    </td>
                                    <td>{{ $producto->categoria?->nombre ?? '-' }}</td>
                                    <td class="text-end">
                                        <span class="fw-bold text-{{ $tabs[$tab]['color'] }}">
                                            {{ $producto->stockTotal() }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $producto->stock_minimo }}</td>
                                    @if(in_array($tab, ['por_vencer', 'vencidos']))
                                        <td>
                                            <span class="badge bg-{{ $tab === 'vencidos' ? 'danger' : 'warning' }}">
                                                {{ $producto->fecha_vencimiento?->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tab === 'vencidos')
                                                <span class="text-danger fw-bold">Vencido</span>
                                            @else
                                                <span class="text-warning fw-bold">{{ $producto->dias_para_vencer }}d</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <a href="{{ route('admin.inventario.productos.show', $producto) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ri ri-eye-line me-1"></i>Kardex
                                        </a>
                                        @can('create movimientos-inventario')
                                            <a href="{{ route('admin.inventario.movimientos.create') }}"
                                               class="btn btn-sm btn-outline-success">
                                                <i class="ri ri-add-line me-1"></i>Movimiento
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
