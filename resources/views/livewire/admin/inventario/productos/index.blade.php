<div>
    @section('title', 'Productos')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Productos</h1>
            <p class="text-muted">Gestión de inventario de productos</p>
        </div>
        <div class="d-flex gap-2">
            @can('view productos')
                <button class="btn btn-outline-success btn-sm" wire:click="exportarCsv">
                    <i class="ri ri-file-excel-line me-1"></i>Exportar CSV
                </button>
            @endcan
            @can('create productos')
                <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-primary">
                    <i class="ri ri-add-line me-1"></i> Nuevo Producto
                </a>
            @endcan
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col mb-3">
            <div class="card border-left-primary shadow h-100 py-2" style="cursor:pointer;" wire:click="$set('alerta', '')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['activos'] }}</div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-left-danger shadow h-100 py-2" style="cursor:pointer;"
                 wire:click="$set('alerta', 'sin_stock')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sin Stock</div>
                    <div class="h5 mb-0 font-weight-bold {{ $stats['sin_stock'] > 0 ? 'text-danger' : '' }}">
                        {{ $stats['sin_stock'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-left-warning shadow h-100 py-2" style="cursor:pointer;"
                 wire:click="$set('alerta', 'por_vencer')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Por Vencer</div>
                    <div class="h5 mb-0 font-weight-bold {{ $stats['por_vencer'] > 0 ? 'text-warning' : '' }}">
                        {{ $stats['por_vencer'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-left-dark shadow h-100 py-2" style="cursor:pointer;"
                 wire:click="$set('alerta', 'vencidos')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Vencidos</div>
                    <div class="h5 mb-0 font-weight-bold {{ $stats['vencidos'] > 0 ? 'text-danger' : '' }}">
                        {{ $stats['vencidos'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                           placeholder="Buscar por nombre, código, SKU...">
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="categoria_id">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="marca_id">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model.change="proveedor_id">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-control" wire:model.change="alerta">
                        <option value="">Alertas</option>
                        <option value="sin_stock">Sin stock</option>
                        <option value="stock_bajo">Stock bajo</option>
                        <option value="vencidos">Vencidos</option>
                        <option value="por_vencer">Por vencer</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-control" wire:model.change="status">
                        <option value="">Estado</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100" wire:click="resetFilters" title="Limpiar filtros">
                        <i class="ri ri-close-line"></i>
                    </button>
                </div>
            </div>

            {{-- Indicador de filtros activos --}}
            @if($search || $categoria_id || $marca_id || $proveedor_id || $alerta || $status !== '')
                <div class="mt-2 d-flex gap-1 flex-wrap">
                    @if($search)
                        <span class="badge bg-secondary">Búsqueda: "{{ $search }}"</span>
                    @endif
                    @if($alerta)
                        <span class="badge bg-warning text-dark">Alerta: {{ $alerta }}</span>
                    @endif
                    @if($proveedor_id)
                        <span class="badge bg-info">Proveedor filtrado</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><a wire:click.prevent="sortBy('codigo')" href="#" class="text-decoration-none">
                                Código @if($sortField==='codigo')<i class="fas fa-sort-{{ $sortDirection==='asc'?'up':'down' }}"></i>@else<i class="fas fa-sort"></i>@endif
                            </a></th>
                            <th><a wire:click.prevent="sortBy('nombre')" href="#" class="text-decoration-none">
                                Nombre @if($sortField==='nombre')<i class="fas fa-sort-{{ $sortDirection==='asc'?'up':'down' }}"></i>@else<i class="fas fa-sort"></i>@endif
                            </a></th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th class="text-end"><a wire:click.prevent="sortBy('precio_venta')" href="#" class="text-decoration-none">
                                P. Venta @if($sortField==='precio_venta')<i class="fas fa-sort-{{ $sortDirection==='asc'?'up':'down' }}"></i>@else<i class="fas fa-sort"></i>@endif
                            </a></th>
                            <th>Stock</th>
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
                            <tr class="{{ $alertaVenc === 'vencido' ? 'table-danger' : ($alertaStock === 'sin_stock' ? 'table-warning' : '') }}">
                                <td><code>{{ $producto->codigo }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($producto->imagen)
                                            <img src="{{ Storage::url($producto->imagen) }}"
                                                 class="rounded" style="width:32px;height:32px;object-fit:cover;"
                                                 alt="{{ $producto->nombre }}">
                                        @else
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                 style="width:32px;height:32px;min-width:32px;">
                                                <i class="ri ri-box-3-line text-muted" style="font-size:.9rem;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $producto->nombre }}</div>
                                            <div class="d-flex gap-1">
                                                @if($producto->requiere_receta)
                                                    <span class="badge bg-danger" style="font-size:.6rem">Receta</span>
                                                @endif
                                                @if($producto->es_medicamento)
                                                    <span class="badge bg-info" style="font-size:.6rem">Medicamento</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($producto->categoria)
                                        <span class="badge" style="background:{{ $producto->categoria->color ?? '#6c757d' }}">
                                            {{ $producto->categoria->nombre }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $producto->marca?->nombre ?? '-' }}</td>
                                <td class="text-end">
                                    <span class="fw-bold">${{ number_format($producto->precio_venta, 2) }}</span>
                                    <small class="text-muted d-block">costo: ${{ number_format($producto->precio_costo, 2) }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $alertaStock === 'sin_stock' ? 'text-danger' : ($alertaStock === 'critico' ? 'text-warning' : 'text-success') }}">
                                        {{ $stockTotal }}
                                    </span>
                                    <small class="text-muted"> {{ $producto->unidad_medida }}</small>
                                    @if($alertaStock === 'sin_stock')
                                        <span class="badge bg-danger d-block mt-1" style="font-size:.65rem">Sin stock</span>
                                    @elseif($alertaStock === 'critico')
                                        <span class="badge bg-warning d-block mt-1" style="font-size:.65rem">Crítico</span>
                                    @elseif($alertaStock === 'bajo')
                                        <span class="badge bg-info d-block mt-1" style="font-size:.65rem">Bajo</span>
                                    @endif
                                </td>
                                <td>
                                    @if($producto->fecha_vencimiento)
                                        <span class="badge bg-{{ $alertaVenc === 'vencido' ? 'danger' : ($alertaVenc === 'critico' ? 'warning' : ($alertaVenc === 'proximo' ? 'info' : 'success')) }}">
                                            {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                                        </span>
                                        @if($alertaVenc && $alertaVenc !== 'vencido')
                                            <small class="text-muted d-block">{{ $producto->dias_para_vencer }}d</small>
                                        @endif
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
                                        <span class="badge bg-{{ $producto->status ? 'success' : 'secondary' }}">
                                            {{ $producto->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endcan
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.inventario.productos.show', $producto) }}">
                                                <i class="ri ri-file-list-3-line me-1"></i> Kardex
                                            </a>
                                            @can('edit productos')
                                                <a class="dropdown-item" href="{{ route('admin.inventario.productos.edit', $producto) }}">
                                                    <i class="ri ri-pencil-line me-1"></i> Editar
                                                </a>
                                            @endcan
                                            @can('create movimientos-inventario')
                                                <a class="dropdown-item text-success"
                                                   href="{{ route('admin.inventario.movimientos.create') }}">
                                                    <i class="ri ri-swap-box-line me-1"></i> Movimiento
                                                </a>
                                            @endcan
                                            @can('delete productos')
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $producto->id }})"
                                                        wire:confirm="¿Eliminar este producto?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="ri ri-box-3-line" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No se encontraron productos</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $productos->links('livewire.pagination') }}
        </div>
    </div>
</div>
