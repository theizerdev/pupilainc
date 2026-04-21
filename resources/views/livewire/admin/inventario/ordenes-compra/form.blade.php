<div>
    @section('title', 'Nueva Orden de Compra')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-shopping-cart-line me-2"></i>Nueva Orden de Compra</h2>
        <a href="{{ route('admin.inventario.ordenes-compra.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="store">
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
                                    <select class="form-control @error('proveedor_id') is-invalid @enderror" wire:model="proveedor_id">
                                        <option value="">Seleccionar proveedor</option>
                                        @foreach($proveedores as $prov)
                                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('proveedor_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
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
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Fecha Emisión *</label>
                                    <input type="date" class="form-control" wire:model="fecha_emision">
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="agregarLinea">
                            <i class="ri ri-add-line me-1"></i>Agregar línea
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%">Producto</th>
                                        <th style="width:15%">Cantidad</th>
                                        <th style="width:20%">Precio Unit.</th>
                                        <th style="width:20%" class="text-end">Subtotal</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lineas as $i => $linea)
                                        <tr>
                                            <td>
                                                <select class="form-control form-control-sm @error("lineas.{$i}.producto_id") is-invalid @enderror"
                                                        wire:model.change="lineas.{{ $i }}.producto_id">
                                                    <option value="">Seleccionar...</option>
                                                    @foreach($productos as $p)
                                                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                @error("lineas.{$i}.producto_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                                ${{ number_format($linea['subtotal'] ?? 0, 2) }}
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
                                        <td class="text-end fw-bold text-primary">${{ number_format($this->total, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
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
                            <span class="fw-bold text-primary h5 mb-0">${{ number_format($this->total, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>Crear Orden
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
