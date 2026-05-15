<div class="w-100">
    @section('title', 'Alertas de Inventario')

    @push('styles')
    <style>
        /* Hero Section */
        .alertas-hero {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .alertas-hero h2 {
            color: #fff;
            margin: 0;
        }
        .alertas-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Alert Cards - Compact Style */
        .alert-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: .9rem 1rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            background: #fff;
            cursor: pointer;
        }
        .alert-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }
        .alert-card.active {
            border: 2px solid;
            box-shadow: 0 6px 20px rgba(0,0,0,.12);
        }
        .alert-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .alert-card .stat-value {
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1;
        }
        .alert-card .stat-label {
            font-size: .72rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-alerts thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-alerts tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .alert-row { transition: background 0.15s; }
        .alert-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="alertas-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-alarm-warning-line me-2"></i>Alertas de Inventario</h2>
            <p class="mt-1">Productos que requieren atención inmediata</p>
        </div>
    </div>

    {{-- Alert Summary Cards --}}
    <div class="row g-3 mb-4">
        @php
        $tabs = [
            'sin_stock'  => ['label' => 'Sin Stock',     'color' => 'danger',  'icon' => 'ri-close-circle-line', 'bg' => '#fee2e2'],
            'critico'    => ['label' => 'Stock Crítico',  'color' => 'warning', 'icon' => 'ri-error-warning-line', 'bg' => '#fef3c7'],
            'stock_bajo' => ['label' => 'Stock Bajo',     'color' => 'info',    'icon' => 'ri-arrow-down-circle-line', 'bg' => '#dbeafe'],
            'por_vencer' => ['label' => 'Por Vencer',     'color' => 'primary', 'icon' => 'ri-time-line', 'bg' => '#e0e7ff'],
            'vencidos'   => ['label' => 'Vencidos',       'color' => 'dark',    'icon' => 'ri-skull-line', 'bg' => '#f3f4f6'],
        ];
        @endphp

        @foreach($tabs as $key => $tabInfo)
            <div class="col-sm-6 col-xl">
                <div class="alert-card {{ $tab === $key ? 'active border-'.$tabInfo['color'] : '' }}"
                     wire:click="$set('tab', '{{ $key }}')">
                    <div class="stat-icon" style="background:{{ $tabInfo['bg'] }};color:var(--bs-{{ $tabInfo['color'] }});">
                        <i class="ri {{ $tabInfo['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="stat-label">{{ $tabInfo['label'] }}</div>
                        <div class="stat-value {{ $resumen[$key] > 0 ? 'text-'.$tabInfo['color'] : '' }}">{{ $resumen[$key] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Alerts Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="{{ $tabs[$tab]['icon'] }} ri-lg text-{{ $tabs[$tab]['color'] }}"></i>
                <h5 class="card-title mb-0">{{ $tabs[$tab]['label'] }}</h5>
                <span class="badge rounded-pill bg-label-{{ $tabs[$tab]['color'] }} ms-2">{{ $alertas[$tab]->count() }} productos</span>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            @if($alertas[$tab]->isEmpty())
                <div class="text-center py-5">
                    <i class="ri ri-checkbox-circle-line text-success" style="font-size:3rem;"></i>
                    <h5 class="mt-3">¡Sin alertas en esta categoría!</h5>
                    <p class="text-muted">Todos los productos están en orden</p>
                </div>
            @else
                <table class="datatables-products table table-alerts">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Total</th>
                            <th>Mínimo</th>
                            @if(in_array($tab, ['por_vencer', 'vencidos']))
                                <th>Vencimiento</th>
                                <th>Días</th>
                            @endif
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alertas[$tab] as $producto)
                            <tr class="alert-row">
                                <td></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($producto->imagen)
                                            <img src="{{ Storage::url($producto->imagen) }}"
                                                 class="rounded" style="width:40px;height:40px;object-fit:cover;margin-right:0.75rem;"
                                                 alt="{{ $producto->nombre }}">
                                        @else
                                            <div class="rounded bg-lighter d-flex align-items-center justify-content-center"
                                                 style="width:40px;height:40px;min-width:40px;margin-right:0.75rem;">
                                                <i class="ri ri-box-3-line text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $producto->nombre }}</div>
                                            <small class="text-muted">{{ $producto->codigo }}</small>
                                            @if($producto->requiere_receta)
                                                <span class="badge rounded-pill bg-label-danger mt-1" style="font-size:.65rem">Receta</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($producto->categoria)
                                        <span class="badge bg-label-secondary">{{ $producto->categoria->nombre }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-{{ $tabs[$tab]['color'] }}">
                                        {{ $producto->stockTotal() }} {{ $producto->unidad_medida }}
                                    </div>
                                </td>
                                <td>{{ $producto->stock_minimo }}</td>
                                @if(in_array($tab, ['por_vencer', 'vencidos']))
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $tab === 'vencidos' ? 'danger' : 'warning' }}-label">
                                            {{ $producto->fecha_vencimiento?->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($tab === 'vencidos')
                                            <span class="badge rounded-pill bg-label-danger">Vencido</span>
                                        @else
                                            <span class="badge rounded-pill bg-label-warning">{{ $producto->dias_para_vencer }} días</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.inventario.productos.show', $producto) }}">
                                                    <i class="ri ri-file-list-3-line me-2"></i>Kardex
                                                </a>
                                            </li>
                                            @can('create movimientos-inventario')
                                                <li>
                                                    <a class="dropdown-item text-success"
                                                       href="{{ route('admin.inventario.movimientos.create') }}">
                                                        <i class="ri ri-swap-box-line me-2"></i>Movimiento
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
