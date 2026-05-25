<div>
    @section('title', $ordenId ? 'Editar Orden de Compra' : 'Nueva Orden de Compra')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-shopping-cart-line me-2"></i>{{ $ordenId ? 'Editar Orden de Compra' : 'Nueva Orden de Compra' }}</h2>
        <a href="{{ route('admin.inventario.ordenes-compra.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="{{ $ordenId ? 'update' : 'store' }}">
        <div class="row">
            <div class="col-md-8">
                <!-- Cabecera -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Información General</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Proveedor *</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control @error('proveedor_id') is-invalid @enderror"
                                               wire:model.live="proveedor_search"
                                               placeholder="Buscar proveedor..."
                                               autocomplete="off"
                                               @if($proveedor_seleccionado) value="{{ $proveedor_seleccionado->nombre }}" @endif>

                                        @if(strlen($proveedor_search) > 0 && count($proveedores_filtrados) > 0 && !$proveedor_seleccionado)
                                        <div class="position-absolute w-100" style="z-index: 1000;">
                                            <div class="list-group shadow-sm" style="max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 1px solid #ced4da; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;">
                                                @foreach($proveedores_filtrados as $prov)
                                                <button type="button" class="list-group-item list-group-item-action"
                                                        wire:click="seleccionarProveedor({{ $prov->id }})"
                                                        style="background-color: #ffffff; border-left: none; border-right: none; border-bottom: 1px solid #f8f9fa; padding: 0.75rem 1rem; transition: background-color 0.2s;">
                                                    <strong style="color: #212529;">{{ $prov->nombre }}</strong><br>
                                                    <small class="text-muted" style="color: #6c757d;">
                                                        @if($prov->rif) RIF: {{ $prov->rif }} | @endif
                                                        @if($prov->telefono) Tel: {{ $prov->telefono }} @endif
                                                    </small>
                                                </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        @if($proveedor_seleccionado)
                                        <div class="mt-2">
                                            <span class="badge bg-success">
                                                <i class="ri ri-check-line"></i> {{ $proveedor_seleccionado->nombre }}
                                            </span>
                                        </div>
                                        @endif
                                        @error('proveedor_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Almacén destino *</label>
                                    <select class="form-control @error('almacen_id') is-invalid @enderror" wire:model="almacen_id">
                                        <option value="">Seleccionar almacén</option>
                                        @foreach($almacenes as $alm)
                                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('almacen_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Fecha Emisión *</label>
                                    <input type="date" class="form-control" wire:model="fecha_emision">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Fecha Esperada</label>
                                    <input type="date" class="form-control" wire:model="fecha_esperada">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Observaciones</label>
                            <textarea class="form-control" wire:model="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Líneas de productos -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <!-- Buscador de productos tipo carrito -->
                        <div class="mb-3">
                            <label>Agregar Producto</label>
                            <div class="position-relative">
                                <input type="text" class="form-control"
                                       wire:model.live="producto_buscar"
                                       placeholder="Buscar producto para agregar..."
                                       autocomplete="off">

                                @if(strlen($producto_buscar) > 0 && count($productos_filtrados) > 0)
                                <div class="position-absolute w-100" style="z-index: 1000;">
                                    <div class="list-group shadow-sm" style="max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 1px solid #ced4da; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;">
                                        @foreach($productos_filtrados as $p)
                                        <button type="button" class="list-group-item list-group-item-action"
                                                wire:click="agregarProductoAlCarrito({{ $p->id }})"
                                                style="background-color: #ffffff; border-left: none; border-right: none; border-bottom: 1px solid #f8f9fa; padding: 0.75rem 1rem; transition: background-color 0.2s;">
                                            <strong style="color: #212529;">{{ $p->nombre }}</strong><br>
                                            <small class="text-muted" style="color: #6c757d;">
                                                Código: {{ $p->codigo }}
                                                @if($p->sku) | SKU: {{ $p->sku }} @endif
                                                | Precio: {{ money($p->precio_costo) }}
                                            </small>
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tabla de líneas del carrito -->
                        @if(count($lineas) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:35%">Producto</th>
                                        <th style="width:15%">Cantidad</th>
                                        <th style="width:20%">Precio Unit.</th>
                                        <th style="width:20%" class="text-end">Subtotal</th>
                                        <th style="width:10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lineas as $i => $linea)
                                        @php
                                            $producto = \App\Models\Producto::find($linea['producto_id']);
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($producto)
                                                    <strong>{{ $producto->nombre }}</strong><br>
                                                    <small class="text-muted">{{ $producto->codigo }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                       wire:model.change="lineas.{{ $i }}.cantidad" min="1">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                       wire:model.change="lineas.{{ $i }}.precio_unitario" step="0.01" min="0">
                                            </td>
                                            <td class="text-end align-middle fw-bold">
                                                {{ money($linea['subtotal'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-0 px-1"
                                                        wire:click="quitarLinea({{ $i }})">
                                                    <i class="ri ri-close-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold text-primary">{{ money($this->total, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-4">
                            <i class="ri ri-shopping-cart-line" style="font-size: 3rem;"></i>
                            <p class="mt-2">No hay productos agregados. Usa el buscador para agregar productos.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Resumen</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Líneas:</span>
                            <span class="fw-bold">{{ count($lineas) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total:</span>
                            <span class="fw-bold text-primary h5 mb-0">{{ money($this->total, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>{{ $ordenId ? 'Actualizar Orden' : 'Crear Orden' }}
                            </button>
                            <a href="{{ route('admin.inventario.ordenes-compra.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
