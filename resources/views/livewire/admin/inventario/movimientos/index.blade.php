<div class="w-100">
    @section('title', 'Movimientos de Inventario')

    @push('styles')
    <style>
        /* Hero Section */
        .movimientos-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .movimientos-hero h2 {
            color: #fff;
            margin: 0;
        }
        .movimientos-hero p {
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
        .table-movimientos thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-movimientos tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .movimiento-row { transition: background 0.15s; }
        .movimiento-row:hover { background-color: #f8f9fa; }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="movimientos-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-swap-box-line me-2"></i>Movimientos de Inventario</h2>
            <p class="mt-1">Historial global de entradas, salidas y ajustes</p>
        </div>
        @can('create movimientos-inventario')
            <a href="{{ route('admin.inventario.movimientos.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Registrar Movimiento
            </a>
        @endcan
    </div>

    {{-- Stat Cards --}}
    @php
        $totalMovimientos = $movimientos->total();
        $entradas = \App\Models\InventarioMovimiento::forUser()->entrada()->count();
        $salidas = \App\Models\InventarioMovimiento::forUser()->salida()->count();
        $ajustes = \App\Models\InventarioMovimiento::forUser()->ajuste()->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-database-2-line"></i></div>
                <div>
                    <div class="stat-label">Total movimientos</div>
                    <div class="stat-value">{{ $totalMovimientos }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-arrow-down-circle-line"></i></div>
                <div>
                    <div class="stat-label">Entradas</div>
                    <div class="stat-value text-success">{{ $entradas }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="ri ri-arrow-up-circle-line"></i></div>
                <div>
                    <div class="stat-label">Salidas</div>
                    <div class="stat-value text-danger">{{ $salidas }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-settings-3-line"></i></div>
                <div>
                    <div class="stat-label">Ajustes</div>
                    <div class="stat-value text-warning">{{ $ajustes }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-3">Filtros</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar producto o código...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="almacen_id">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.change="tipo">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposMovimiento as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.change="fecha_desde">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.change="fecha_hasta">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-movimientos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Almacén</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock Ant.</th>
                        <th>Stock Nuevo</th>
                        <th>Referencia</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        @php $info = $mov->tipo_info; @endphp
                        <tr class="movimiento-row">
                            <td><small>{{ $mov->created_at->format('d/m/Y H:i') }}</small></td>
                            <td>
                                <div>
                                    <a href="{{ route('admin.inventario.productos.show', $mov->producto_id) }}" class="text-decoration-none fw-semibold">
                                        {{ $mov->producto->nombre }}
                                    </a>
                                    <small class="text-muted d-block"><code>{{ $mov->producto->codigo }}</code></small>
                                </div>
                            </td>
                            <td>{{ $mov->almacen->nombre }}</td>
                            <td>
                                <span class="badge rounded-pill bg-label-{{ $info['color'] }}">
                                    <i class="{{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                                </span>
                            </td>
                            <td class="fw-bold {{ $info['signo'] === '+' ? 'text-success' : 'text-danger' }}">
                                {{ $info['signo'] }}{{ $mov->cantidad }}
                            </td>
                            <td class="text-muted">{{ $mov->stock_anterior }}</td>
                            <td class="fw-bold">{{ $mov->stock_nuevo }}</td>
                            <td><small>{{ $mov->referencia ?? '-' }}</small></td>
                            <td><small>{{ $mov->user?->name ?? 'Sistema' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">Sin movimientos registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($movimientos->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $movimientos->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
