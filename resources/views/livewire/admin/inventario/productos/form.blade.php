<div>
    @section('title', $isEdit ? 'Editar Producto' : 'Nuevo Producto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-box-3-line me-2"></i>{{ $isEdit ? 'Editar' : 'Nuevo' }} Producto</h2>
        <a href="{{ route('admin.inventario.productos.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row">

            {{-- COLUMNA PRINCIPAL --}}
            <div class="col-md-8">

                {{-- Información General --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Información General</h5></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Nombre *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                   wire:model="nombre" placeholder="Nombre del producto">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>
                                        Código
                                        @if(!$isEdit && $codigo_preview)
                                            <small class="text-muted">(próximo: <code>{{ $codigo_preview }}</code>)</small>
                                        @endif
                                    </label>
                                    <input type="text" class="form-control" wire:model="codigo"
                                           placeholder="{{ $codigo_preview ?: 'Auto-generado' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>SKU</label>
                                    <input type="text" class="form-control" wire:model="sku">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Unidad de Medida *</label>
                                    <select class="form-control @error('unidad_medida') is-invalid @enderror"
                                            wire:model="unidad_medida">
                                        @foreach($unidades as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('unidad_medida') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Categoría</label>
                                    <select class="form-control" wire:model="categoria_producto_id">
                                        <option value="">Sin categoría</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Marca</label>
                                    <select class="form-control" wire:model="marca_id">
                                        <option value="">Sin marca</option>
                                        @foreach($marcas as $marca)
                                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Proveedor</label>
                                    <select class="form-control" wire:model="proveedor_id">
                                        <option value="">Sin proveedor</option>
                                        @foreach($proveedores as $prov)
                                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label>Descripción</label>
                            <textarea class="form-control" wire:model="descripcion" rows="2"
                                      placeholder="Descripción opcional del producto"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Precios y Stock --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Precios y Control de Stock</h5></div>
                    <div class="card-body">

                        @php
                            $costo      = (float) $precio_costo;
                            $venta      = (float) $precio_venta;
                            $ganancia   = round($venta - $costo, 2);
                            $margen     = ($costo > 0 && $venta > 0) ? round((($venta - $costo) / $venta) * 100, 1) : 0;
                            $mColor     = $margen < 0 ? 'danger' : ($margen < 10 ? 'warning' : ($margen < 30 ? 'info' : 'success'));
                        @endphp

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Precio Costo *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number"
                                               class="form-control @error('precio_costo') is-invalid @enderror"
                                               wire:model.live="precio_costo" step="0.01" min="0">
                                    </div>
                                    @error('precio_costo') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Precio Venta *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number"
                                               class="form-control @error('precio_venta') is-invalid @enderror"
                                               wire:model.live="precio_venta" step="0.01" min="0">
                                    </div>
                                    @error('precio_venta') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($costo > 0 || $venta > 0)
                                    <div class="card border-{{ $mColor }} mb-0">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block">Ganancia</small>
                                                    <span class="fw-bold text-{{ $mColor }}">
                                                        ${{ number_format($ganancia, 2) }}
                                                    </span>
                                                </div>
                                                <div class="text-center">
                                                    <small class="text-muted d-block">Margen</small>
                                                    <span class="fw-bold fs-5 text-{{ $mColor }}">
                                                        {{ $margen }}%
                                                    </span>
                                                </div>
                                                <div>
                                                    @if($margen < 0)
                                                        <i class="ri ri-arrow-down-circle-fill text-danger fs-4"></i>
                                                    @elseif($margen < 10)
                                                        <i class="ri ri-alert-fill text-warning fs-4"></i>
                                                    @else
                                                        <i class="ri ri-checkbox-circle-fill text-success fs-4"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted small">
                                        <i class="ri ri-information-line me-1"></i>
                                        Ingresa precios para ver el margen de ganancia
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Stock Mínimo *</label>
                                    <input type="number" class="form-control @error('stock_minimo') is-invalid @enderror"
                                           wire:model.live="stock_minimo" min="0">
                                    @error('stock_minimo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Punto de Reorden *</label>
                                    <input type="number" class="form-control @error('punto_reorden') is-invalid @enderror"
                                           wire:model="punto_reorden" min="0">
                                    @error('punto_reorden') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>
                                        Stock Máximo
                                        @if($stock_minimo && $stock_maximo && (int)$stock_maximo < (int)$stock_minimo)
                                            <span class="badge bg-danger ms-1">Debe ser ≥ mínimo</span>
                                        @endif
                                    </label>
                                    <input type="number" class="form-control @error('stock_maximo') is-invalid @enderror"
                                           wire:model.live="stock_maximo" min="0">
                                    @error('stock_maximo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Fecha de Vencimiento</label>
                                    <input type="date"
                                           class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                                           wire:model="fecha_vencimiento">
                                    @error('fecha_vencimiento') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label>Ubicación Física</label>
                                    <input type="text" class="form-control" wire:model="ubicacion_fisica"
                                           placeholder="Ej: Estante A, Fila 3, Cajón 2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Imagen --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class="ri ri-image-line me-2"></i>Imagen del Producto</h5></div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                @if($imagen)
                                    <img src="{{ $imagen->temporaryUrl() }}"
                                         class="img-thumbnail rounded" style="width:120px;height:120px;object-fit:cover;" alt="Preview">
                                    <div class="mt-1"><span class="badge bg-info">Nueva imagen</span></div>
                                @elseif($imagen_actual && !$eliminar_imagen)
                                    <img src="{{ Storage::url($imagen_actual) }}"
                                         class="img-thumbnail rounded" style="width:120px;height:120px;object-fit:cover;" alt="{{ $nombre }}">
                                @else
                                    <div class="border rounded d-flex align-items-center justify-content-center bg-light"
                                         style="width:120px;height:120px;margin:0 auto;">
                                        <i class="ri ri-image-line text-muted" style="font-size:2.5rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <div class="form-group mb-2">
                                    <label>Subir imagen</label>
                                    <input type="file" class="form-control @error('imagen') is-invalid @enderror"
                                           wire:model="imagen" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF — máx. 2MB</small>
                                    @error('imagen') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                @if($imagen_actual && !$imagen)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               wire:model="eliminar_imagen" id="eliminarImagen">
                                        <label class="form-check-label text-danger" for="eliminarImagen">
                                            <i class="ri ri-delete-bin-line me-1"></i>Eliminar imagen actual
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stock Inicial (solo creación) --}}
                @if(!$isEdit)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ri ri-archive-line me-2"></i>Stock Inicial <small class="text-muted fw-normal">(Opcional)</small></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label>Almacén</label>
                                        <select class="form-control" wire:model="almacen_inicial_id">
                                            <option value="">Seleccionar almacén</option>
                                            @foreach($almacenes as $alm)
                                                <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label>Cantidad inicial</label>
                                        <input type="number" class="form-control" wire:model="stock_inicial" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- COLUMNA LATERAL --}}
            <div class="col-md-4">

                {{-- Opciones --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Opciones</h5></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" wire:model="status" id="status">
                            <label class="form-check-label" for="status">Activo</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" wire:model="es_medicamento" id="esMed">
                            <label class="form-check-label" for="esMed">Es medicamento</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="requiere_receta" id="receta">
                            <label class="form-check-label" for="receta">Requiere receta</label>
                        </div>
                    </div>
                </div>

                {{-- Resumen de precios --}}
                @if((float)$precio_costo > 0 || (float)$precio_venta > 0)
                    @php
                        $c2 = (float) $precio_costo;
                        $v2 = (float) $precio_venta;
                        $g2 = round($v2 - $c2, 2);
                        $m2 = ($c2 > 0 && $v2 > 0) ? round((($v2 - $c2) / $v2) * 100, 1) : 0;
                        $mc2 = $m2 < 0 ? 'danger' : ($m2 < 10 ? 'warning' : ($m2 < 30 ? 'info' : 'success'));
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Resumen de Precios</h5></div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Precio costo:</span>
                                <span class="fw-bold">${{ number_format($c2, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Precio venta:</span>
                                <span class="fw-bold">${{ number_format($v2, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Ganancia:</span>
                                <span class="fw-bold text-{{ $mc2 }}">${{ number_format($g2, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Margen:</span>
                                <span class="fw-bold fs-5 text-{{ $mc2 }}">{{ $m2 }}%</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Stock actual por almacén (solo edición) --}}
                @if($isEdit)
                    @php $stockActual = $this->getStockActual(); $stockTotal = $this->getStockTotal(); @endphp
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Stock Actual</h5>
                            <a href="{{ route('admin.inventario.productos.show', $producto) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="ri ri-file-list-3-line me-1"></i>Kardex
                            </a>
                        </div>
                        <div class="card-body p-0">
                            @forelse($stockActual as $stock)
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <small class="fw-semibold">{{ $stock->almacen->nombre }}</small>
                                    <span class="badge bg-{{ $stock->cantidad <= $stock_minimo ? 'danger' : 'success' }} fs-6">
                                        {{ $stock->cantidad }}
                                    </span>
                                </div>
                            @empty
                                <div class="px-3 py-2 text-muted small">Sin stock registrado</div>
                            @endforelse
                            @if($stockTotal > 0)
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                                    <small class="fw-bold text-primary">Total</small>
                                    <span class="badge bg-primary fs-6">{{ $stockTotal }}</span>
                                </div>
                                <div class="px-3 py-2">
                                    <small class="text-muted">
                                        Valorización: <strong>${{ number_format($stockTotal * (float)$precio_costo, 2) }}</strong>
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Botones --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>{{ $isEdit ? 'Actualizar' : 'Guardar' }} Producto
                            </button>
                            <a href="{{ route('admin.inventario.productos.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
