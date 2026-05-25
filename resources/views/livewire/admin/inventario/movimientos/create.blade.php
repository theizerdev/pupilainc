<div>
    @section('title', 'Registrar Movimiento')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-swap-box-line me-2"></i>Registrar Movimiento</h2>
        <a href="{{ route('admin.inventario.movimientos.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="store">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Datos del Movimiento</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Producto *</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control @error('producto_id') is-invalid @enderror"
                                               wire:model.live="producto_search"
                                               placeholder="Buscar producto..."
                                               autocomplete="off"
                                               @if($producto_seleccionado) value="{{ $producto_seleccionado->nombre }} ({{ $producto_seleccionado->codigo }})" @endif>

                                        @if(strlen($producto_search) > 0 && count($productos_filtrados) > 0)
                                        <div class="position-absolute w-100" style="z-index: 1000;">
                                            <div class="list-group shadow-sm" style="max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 1px solid #ced4da; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;">
                                                @foreach($productos_filtrados as $prod)
                                                <button type="button" class="list-group-item list-group-item-action"
                                                        wire:click="seleccionarProducto({{ $prod->id }})"
                                                        style="background-color: #ffffff; border-left: none; border-right: none; border-bottom: 1px solid #f8f9fa; padding: 0.75rem 1rem; transition: background-color 0.2s;">
                                                    <strong style="color: #212529;">{{ $prod->nombre }}</strong><br>
                                                    <small class="text-muted" style="color: #6c757d;">
                                                        Código: {{ $prod->codigo }}
                                                        @if($prod->sku) | SKU: {{ $prod->sku }} @endif
                                                    </small>
                                                </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        @if($producto_seleccionado)
                                        <div class="mt-2">
                                            <span class="badge bg-success">
                                                <i class="ri ri-check-line"></i> {{ $producto_seleccionado->nombre }} ({{ $producto_seleccionado->codigo }})
                                            </span>
                                        </div>
                                        @endif
                                        @error('producto_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Almacén *</label>
                                    <select class="form-control @error('almacen_id') is-invalid @enderror" wire:model.change="almacen_id">
                                        <option value="">Seleccionar almacén</option>
                                        @foreach($almacenes as $alm)
                                            <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('almacen_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if($producto_id && $almacen_id)
                            <div class="alert alert-info py-2 mb-3">
                                <i class="ri ri-information-line me-1"></i>
                                Stock actual en almacén seleccionado: <strong>{{ $stockActual }}</strong>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Tipo de Movimiento *</label>
                                    <select class="form-control @error('tipo') is-invalid @enderror" wire:model="tipo">
                                        @foreach($tiposMovimiento as $key => $info)
                                            <option value="{{ $key }}">{{ $info['signo'] }} {{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Cantidad *</label>
                                    <input type="number" class="form-control @error('cantidad') is-invalid @enderror"
                                           wire:model="cantidad" min="1">
                                    @error('cantidad') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Costo Unitario</label>
                                    <input type="number" class="form-control" wire:model="costo_unitario" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Referencia</label>
                            <input type="text" class="form-control" wire:model="referencia" placeholder="Ej: Factura #001, Consulta #123">
                        </div>
                        <div class="form-group mb-3">
                            <label>Observación</label>
                            <textarea class="form-control" wire:model="observacion" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Tipos de Movimiento</h5></div>
                    <div class="card-body p-2">
                        @foreach($tiposMovimiento as $key => $info)
                            <div class="d-flex align-items-center gap-2 mb-1 p-1 rounded {{ $tipo === $key ? 'bg-light' : '' }}">
                                <span class="badge bg-{{ $info['color'] }}" style="width:24px;text-align:center;">{{ $info['signo'] }}</span>
                                <small>{{ $info['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>Registrar Movimiento
                            </button>
                            <a href="{{ route('admin.inventario.movimientos.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
