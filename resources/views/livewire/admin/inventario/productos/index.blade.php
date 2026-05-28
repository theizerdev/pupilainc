<div class="w-100">
    @section('title', 'Productos')

    @push('styles')
    <style>
        /* Hero Section */
        .productos-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.4rem 1.6rem;
        }
        .productos-hero h2 {
            color: #fff;
            margin: 0;
        }
        .productos-hero p {
            opacity: 0.9;
            margin: 0;
        }

        /* Widget Cards - Materialize Style */
        .card-widget {
            border: 1px solid #e4e6f9;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .card-widget:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .card-widget-1, .card-widget-2, .card-widget-3 {
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .card-widget h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .card-widget p {
            margin-bottom: 0.25rem;
            color: #697a8d;
            font-size: 0.875rem;
        }
        .card-widget .avatar-initial {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            background-color: #f1f0f5;
        }
        .card-widget-separator-wrapper {
            position: relative;
        }

        /* Product Row Hover */
        .producto-row { transition: background 0.15s; }
        .producto-row:hover { background-color: #f8f9fa; }

        /* Clean Table */
        .table-products thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e4e6f9;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697a8d;
            padding: 0.875rem 1rem;
        }
        .table-products tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
    </style>
    @endpush

    {{-- Hero Section --}}
    <div class="productos-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-box-3-line me-2"></i>Productos</h2>
            <p class="mt-1">Gestión completa del inventario de productos</p>
        </div>
        <div class="d-flex gap-2">
            @can('view productos')
                <button type="button" class="btn btn-light btn-sm" wire:click="exportarCsv">
                    <i class="ri ri-file-excel-line me-1"></i>Exportar CSV
                </button>
            @endcan
            @can('create productos')
                <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nuevo Producto
                </a>
            @endcan
        </div>
    </div>

    {{-- Widget Cards Section --}}
    <div class="card mb-4">
        <div class="card-widget-separator-wrapper">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card-widget-1 border-end pb-4 pb-sm-0">
                            <div>
                                <p class="mb-1">Total productos</p>
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <p class="mb-0">
                                    <span class="me-2">{{ $stats['activos'] }} activos</span>
                                </p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded-3 text-heading">
                                    <i class="ri ri-database-2-line ri-28px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card-widget-2 border-end pb-4 pb-sm-0">
                            <div>
                                <p class="mb-1">Activos</p>
                                <h4 class="mb-1">{{ $stats['activos'] }}</h4>
                                <p class="mb-0">
                                    <span class="badge rounded-pill bg-label-success">{{ round(($stats['activos'] / max(1, $stats['total'])) * 100) }}%</span>
                                </p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded-3 text-heading">
                                    <i class="ri ri-checkbox-circle-line ri-28px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card-widget-3 border-end pb-4 pb-sm-0" style="cursor:pointer;" wire:click="$set('alerta', 'sin_stock')">
                            <div>
                                <p class="mb-1">Sin stock</p>
                                <h4 class="mb-1 {{ $stats['sin_stock'] > 0 ? 'text-danger' : '' }}">{{ $stats['sin_stock'] }}</h4>
                                <p class="mb-0">
                                    <span class="me-2">Requiere atención</span>
                                </p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded-3 text-heading">
                                    <i class="ri ri-error-warning-line ri-28px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-start" style="cursor:pointer;" wire:click="$set('alerta', 'por_vencer')">
                            <div>
                                <p class="mb-1">Por vencer</p>
                                <h4 class="mb-1 {{ $stats['por_vencer'] > 0 ? 'text-warning' : '' }}">{{ $stats['por_vencer'] }}</h4>
                                <p class="mb-0">
                                    <span class="me-2">Próximos 30 días</span>
                                </p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded-3 text-heading">
                                    <i class="ri ri-time-line ri-28px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card with Filters and Table --}}
    <div class="card">
        <div class="card-header border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Listado de productos</h5>
                <div class="d-flex gap-2">
                    @can('view productos')
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="exportarCsv">
                            <i class="ri ri-file-excel-line me-1"></i>Exportar CSV
                        </button>
                    @endcan
                    @can('create productos')
                        <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri ri-add-line me-1"></i>Nuevo Producto
                        </a>
                    @endcan
                </div>
            </div>
            <h6 class="mb-3">Filtros</h6>
            <div class="row g-3">
                <div class="col-md-4 product_status">
                    <input type="text" class="form-control" placeholder="Buscar por nombre, código, SKU..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-4 product_category">
                    <select class="form-select" wire:model.change="categoria_id">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 product_stock">
                    <select class="form-select" wire:model.change="alerta">
                        <option value="">Todos los estados</option>
                        <option value="sin_stock">Sin stock</option>
                        <option value="stock_bajo">Stock bajo</option>
                        <option value="vencidos">Vencidos</option>
                        <option value="por_vencer">Por vencer</option>
                    </select>
                </div>
            </div>
            @if($search || $categoria_id || $marca_id || $proveedor_id || $alerta || $status !== '')
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetFilters">
                        <i class="ri ri-close-line me-1"></i>Limpiar filtros
                    </button>
                </div>
            @endif
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        wire:click="$set('selected', {{ json_encode($productos->pluck('id')) }})">
                    Seleccionar página
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('selected', [])">
                    Deseleccionar
                </button>
                @can('delete productos')
                    <button type="button" class="btn btn-danger btn-sm"
                            wire:click="deleteSelected"
                            wire:confirm="¿Eliminar los productos seleccionados permanentemente? Esta acción no se puede deshacer."
                            @if(!count($selected)) disabled @endif>
                        <i class="ri ri-delete-bin-line me-1"></i>Eliminar seleccionados
                        @if(count($selected))
                            <span class="badge bg-white text-danger ms-2">{{ count($selected) }}</span>
                        @endif
                    </button>
                @endcan
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card-datatable table-responsive">
            <table class="datatables-products table table-products">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:40px;"></th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        @php
                            $stockTotal  = $producto->stockTotal();
                            $alertaStock = $producto->alerta_stock;
                            $alertaVenc  = $producto->alerta_vencimiento;
                        @endphp
                        <tr class="producto-row">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input product-checkbox" type="checkbox"
                                           wire:model="selected" value="{{ $producto->id }}">
                                </div>
                            </td>
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
                                <div class="fw-semibold {{ $alertaStock === 'sin_stock' ? 'text-danger' : ($alertaStock === 'critico' ? 'text-warning' : '') }}">
                                    {{ $stockTotal }} {{ $producto->unidad_medida }}
                                </div>
                                @if($alertaStock === 'sin_stock')
                                    <span class="badge rounded-pill bg-label-danger mt-1">Sin stock</span>
                                @elseif($alertaStock === 'critico')
                                    <span class="badge rounded-pill bg-label-warning mt-1">Crítico</span>
                                @elseif($alertaStock === 'bajo')
                                    <span class="badge rounded-pill bg-label-info mt-1">Bajo</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">${{ number_format($producto->precio_venta, 2) }}</div>
                            </td>
                            <td>
                                @if($producto->fecha_vencimiento)
                                    <span class="badge rounded-pill bg-{{ $alertaVenc === 'vencido' ? 'danger' : ($alertaVenc === 'critico' ? 'warning' : ($alertaVenc === 'proximo' ? 'info' : 'success')) }}-label">
                                        {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @can('edit productos')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               wire:click="toggleStatus({{ $producto->id }})"
                                               {{ $producto->status ? 'checked' : '' }} style="cursor:pointer;">
                                    </div>
                                @else
                                    <span class="badge rounded-pill bg-{{ $producto->status ? 'success' : 'secondary' }}-label">
                                        {{ $producto->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </td>
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
                                        @can('edit productos')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.inventario.productos.edit', $producto) }}">
                                                    <i class="ri ri-pencil-line me-2"></i>Editar
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create movimientos-inventario')
                                            <li>
                                                <a class="dropdown-item text-success"
                                                   href="{{ route('admin.inventario.movimientos.create') }}">
                                                    <i class="ri ri-swap-box-line me-2"></i>Movimiento
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete productos')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $producto->id }})"
                                                        wire:confirm="¿Eliminar este producto permanentemente?">
                                                    <i class="ri ri-delete-bin-line me-2"></i>Eliminar
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ri ri-inbox-line ri-3x d-block mb-3"></i>
                                <p class="mb-0">No se encontraron productos</p>
                                @if($search || $categoria_id || $marca_id || $proveedor_id || $alerta || $status !== '')
                                    <small class="text-muted">Intenta ajustar los filtros de búsqueda</small>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($productos->hasPages())
            <div class="card-footer bg-white border-0 pt-3 pb-3">
                {{ $productos->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
