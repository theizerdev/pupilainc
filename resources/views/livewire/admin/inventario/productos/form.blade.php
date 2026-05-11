<div>
    @section('title', $isEdit ? 'Editar Producto' : 'Nuevo Producto')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-box-3-line me-2"></i>{{ $isEdit ? 'Editar' : 'Nuevo' }} Producto</h2>
        <a href="{{ route('admin.inventario.productos.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="save">
        {{-- Navegación por Tabs --}}
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                        wire:click="setTab('general')">
                    <i class="ri ri-information-line me-1"></i>Información General
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'precios' ? 'active' : '' }}"
                        wire:click="setTab('precios')">
                    <i class="ri ri-price-tag-3-line me-1"></i>Precios y Stock
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'imagenes' ? 'active' : '' }}"
                        wire:click="setTab('imagenes')">
                    <i class="ri ri-image-line me-1"></i>Imágenes
                    @if(count($imagenes_existentes) + count($imagenes_nuevas) > 0)
                        <span class="badge bg-primary ms-1">{{ count($imagenes_existentes) + count($imagenes_nuevas) }}</span>
                    @endif
                </button>
            </li>
            {{-- Pestaña Variantes OCULTA --}}
            {{-- <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'variantes' ? 'active' : '' }}"
                        wire:click="setTab('variantes')">
                    <i class="ri ri-stack-line me-1"></i>Variantes
                    @if(count($variantes) > 0)
                        <span class="badge bg-info ms-1">{{ count($variantes) }}</span>
                    @endif
                </button>
            </li> --}}
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $activeTab === 'opciones' ? 'active' : '' }}"
                        wire:click="setTab('opciones')">
                    <i class="ri ri-settings-3-line me-1"></i>Opciones
                </button>
            </li>
        </ul>

        <div class="row">
            <div class="col-lg-9">

                {{-- TAB: INFORMACIÓN GENERAL --}}
                @if($activeTab === 'general')
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Información General</h5></div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="fw-semibold">Nombre del Producto *</label>
                                <input type="text" class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                       wire:model="nombre" placeholder="Ej: Paracetamol 500mg">
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Código</label>
                                        <input type="text" class="form-control" wire:model="codigo"
                                               placeholder="{{ $codigo_preview ?: 'Auto-generado' }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>SKU</label>
                                        <input type="text" class="form-control" wire:model="sku" placeholder="SKU interno">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Código de Barras</label>
                                        <input type="text" class="form-control" wire:model="codigo_barras" placeholder="EAN / UPC">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Presentación *</label>
                                        <select class="form-control @error('unidad_medida') is-invalid @enderror" wire:model="unidad_medida">
                                            <option value="">Seleccionar...</option>
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
                                        <select class="form-control  @error('categoria_producto_id') is-invalid @enderror" wire:model="categoria_producto_id">
                                            <option value="">Sin categoría</option>
                                            @foreach($categorias as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('categoria_producto_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label>Marca</label>
                                        <select class="form-control  @error('marca_id') is-invalid @enderror" wire:model="marca_id">
                                            <option value="">Sin marca</option>
                                            @foreach($marcas as $marca)
                                                <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('marca_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label>Proveedor</label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control @error('proveedor_id') is-invalid @enderror"
                                                   wire:model.live="proveedor_search"
                                                   placeholder="Buscar proveedor..."
                                                   autocomplete="off"
                                                   @if($proveedor_seleccionado) value="{{ $proveedor_seleccionado->nombre }}" @endif>

                                                    @if(strlen($proveedor_search) > 0 && count($proveedores_filtrados) > 0)
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
                            </div>

                            <div class="form-group mb-0">
                                <label>Descripción</label>
                                <textarea class="form-control" wire:model="descripcion" rows="3"
                                          placeholder="Descripción del producto, indicaciones, usos..."></textarea>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- TAB: PRECIOS Y STOCK --}}
                @if($activeTab === 'precios')
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Precios y Margen</h5></div>
                        <div class="card-body">
                            @php
                                $costo = (float) $precio_costo;
                                $venta = (float) $precio_venta;
                                $ganancia = round($venta - $costo, 2);
                                $margen = ($costo > 0 && $venta > 0) ? round((($venta - $costo) / $venta) * 100, 1) : 0;
                                $mColor = $margen < 0 ? 'danger' : ($margen < 10 ? 'warning' : ($margen < 30 ? 'info' : 'success'));
                            @endphp
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Precio Costo *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control @error('precio_costo') is-invalid @enderror"
                                                   wire:model.live="precio_costo" step="0.01" min="0">
                                        </div>
                                        @error('precio_costo') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Precio Venta *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control @error('precio_venta') is-invalid @enderror"
                                                   wire:model.live="precio_venta" step="0.01" min="0">
                                        </div>
                                        @error('precio_venta') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if($costo > 0 || $venta > 0)
                                        <div class="alert alert-{{ $mColor }} py-2 mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div><small class="d-block opacity-75">Ganancia</small><strong>${{ number_format($ganancia, 2) }}</strong></div>
                                                <div class="text-center"><small class="d-block opacity-75">Margen</small><strong class="fs-5">{{ $margen }}%</strong></div>
                                                <div>
                                                    @if($margen < 0)<i class="ri-arrow-down-circle-fill fs-3"></i>
                                                    @elseif($margen < 10)<i class="ri-alert-fill fs-3"></i>
                                                    @else<i class="ri-checkbox-circle-fill fs-3"></i>@endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted small mb-3"><i class="ri-information-line me-1"></i>Ingresa precios para ver el margen.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Control de Stock</h5></div>
                        <div class="card-body">
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
                                        <label>Stock Máximo</label>
                                        <input type="number" class="form-control @error('stock_maximo') is-invalid @enderror"
                                               wire:model.live="stock_maximo" min="0">
                                        @error('stock_maximo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   @if(auth()->user()->empresa->pais->nombre === 'Venezuela')
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Configuración Fiscal (IVA)</h5></div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Estado IVA</label>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model.live="aplica_iva" id="aplica_iva">
                                            <label class="form-check-label" for="aplica_iva">Aplica IVA</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model.live="exento_iva" id="exento_iva">
                                            <label class="form-check-label" for="exento_iva">Exento de IVA</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Alícuota IVA</label>
                                    <select class="form-select @error('iva_alicuota') is-invalid @enderror"
                                            wire:model="iva_alicuota"
                                            @if(!$aplica_iva || $exento_iva) disabled @endif>
                                        <option value="0">0% - No aplica / Exento</option>
                                        <option value="8">8% - Alícuota reducida</option>
                                        <option value="16">16% - Alícuota general</option>
                                    </select>
                                    @error('iva_alicuota')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-4">
                                    @if($exento_iva || !$aplica_iva)
                                        <div class="alert alert-warning py-2 mb-0">
                                            <i class="ri-information-line me-1"></i>
                                            <strong>Exento:</strong> Este producto no genera IVA en la factura.
                                        </div>
                                    @elseif($aplica_iva)
                                        <div class="alert alert-success py-2 mb-0">
                                            <i class="ri-percent-line me-1"></i>
                                            <strong>Gravado al {{ $iva_alicuota }}%:</strong>
                                            Precio con IVA: <strong>${{ number_format((float)$precio_venta * (1 + $iva_alicuota / 100), 2) }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                   @endif
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Detalles Adicionales</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3" @if(!$es_medicamento) hidden @endif>
                                    <div class="form-group mb-3">
                                        <label>Fecha Vencimiento @if($es_medicamento)<span class="text-danger">*</span>@endif</label>
                                        <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                                               wire:model="fecha_vencimiento">
                                        @error('fecha_vencimiento') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-{{ $es_medicamento ? '6' : '9' }}">
                                    <div class="form-group mb-3">
                                        <label>Ubicación Física</label>
                                        <input type="text" class="form-control" wire:model="ubicacion_fisica"
                                               placeholder="Ej: Estante A, Fila 3">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label>Peso (kg)</label>
                                        <input type="number" step="0.001" class="form-control" wire:model="peso" placeholder="0.500">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Dimensiones</label>
                                        <input type="text" class="form-control" wire:model="dimensiones" placeholder="Ej: 10x5x3 cm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$isEdit)
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Stock Inicial <small class="text-muted">(Opcional)</small></h5></div>
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
                @endif

                {{-- TAB: IMÁGENES --}}
                @if($activeTab === 'imagenes')
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Imagen Principal</h5></div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    @if($imagen)
                                        <img src="{{ $imagen->temporaryUrl() }}" class="img-thumbnail rounded" style="width:140px;height:140px;object-fit:cover;">
                                        <div class="mt-1"><span class="badge bg-info">Nueva</span></div>
                                    @elseif($imagen_actual && !$eliminar_imagen)
                                        <img src="{{ Storage::url($imagen_actual) }}" class="img-thumbnail rounded" style="width:140px;height:140px;object-fit:cover;">
                                    @else
                                        <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width:140px;height:140px;margin:0 auto;">
                                            <i class="ri ri-image-line text-muted" style="font-size:3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group mb-2">
                                        <label>Subir imagen principal</label>
                                        <input type="file" class="form-control @error('imagen') is-invalid @enderror"
                                               wire:model="imagen" accept="image/*">
                                        <small class="text-muted">JPG, PNG, GIF — máx. 2MB</small>
                                        @error('imagen') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    @if($imagen_actual && !$imagen)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="eliminar_imagen" id="eliminarImagen">
                                            <label class="form-check-label text-danger" for="eliminarImagen">
                                                <i class="ri ri-delete-bin-line me-1"></i>Eliminar imagen actual
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Galería de Imágenes</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addImagenGaleria">
                                <i class="ri ri-add-line me-1"></i>Agregar Imagen
                            </button>
                        </div>
                        <div class="card-body">
                            @if(empty($imagenes_existentes) && empty($imagenes_nuevas))
                                <div class="text-center py-5 text-muted">
                                    <i class="ri ri-gallery-line fs-1 mb-2 d-block"></i>
                                    <p class="mb-0">No hay imágenes en la galería.</p>
                                    <small>Agrega fotos adicionales del producto.</small>
                                </div>
                            @else
                                <div class="row g-3">
                                    @foreach($imagenes_existentes as $img)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="position-relative">
                                                <img src="{{ $img['url'] }}" class="img-thumbnail w-100" style="height:140px;object-fit:cover;">
                                                @if($img['principal'] || $imagen_principal_id == $img['id'])
                                                    <span class="position-absolute top-0 start-0 badge bg-primary m-1">Principal</span>
                                                @endif
                                                <div class="position-absolute top-0 end-0 m-1">
                                                    <button type="button" class="btn btn-sm btn-danger" wire:click="removeImagenExistente({{ $img['id'] }})">
                                                        <i class="ri ri-close-line"></i>
                                                    </button>
                                                </div>
                                                <div class="position-absolute bottom-0 start-0 end-0 p-1 bg-dark bg-opacity-50 text-center">
                                                    <button type="button" class="btn btn-xs btn-light btn-sm" wire:click="setImagenPrincipal({{ $img['id'] }})">
                                                        <i class="ri ri-star-line me-1"></i>Principal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach($imagenes_nuevas as $idx => $file)
                                        @if($file)
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="position-relative">
                                                    <img src="{{ $file->temporaryUrl() }}" class="img-thumbnail w-100" style="height:140px;object-fit:cover;">
                                                    <span class="position-absolute top-0 start-0 badge bg-info m-1">Nueva</span>
                                                    <div class="position-absolute top-0 end-0 m-1">
                                                        <button type="button" class="btn btn-sm btn-danger" wire:click="removeImagenNueva({{ $idx }})">
                                                            <i class="ri ri-close-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            @foreach($imagenes_nuevas as $idx => $file)
                                @if(!$file)
                                    <div class="mt-3">
                                        <input type="file" class="form-control" wire:model="imagenes_nuevas.{{ $idx }}" accept="image/*">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                {{-- TAB: VARIANTES OCULTO --}}
                {{-- @if($activeTab === 'variantes')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Variantes del Producto</h5>
                        <button type="button" class="btn btn-primary" wire:click="addVarianteManual">
                            <i class="ri ri-add-line me-1"></i>Agregar Variante
                        </button>
                    </div>

                    @if(empty($variantes))
                        <div class="card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="ri ri-stack-line fs-1 mb-2 d-block"></i>
                                <p class="mb-0">No hay variantes configuradas.</p>
                                <small>Agrega variantes para este producto.</small>
                            </div>
                        </div>
                    @else
                        @foreach($variantes as $index => $variante)
                            @php
                                $tipoSeleccionado = collect($tiposVariantes)->firstWhere('id', $variante['tipo_variante_id'] ?? null);
                                $valoresPredefinidos = $tipoSeleccionado ? $tipoSeleccionado->valores : collect();
                                $valorSeleccionadoId = $variante['valor_variante_id'] ?? null;
                            @endphp
                            <div class="card mb-3 border shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <span class="fw-semibold text-secondary">Variante #{{ $index + 1 }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeVariante({{ $index }})" title="Eliminar variante">
                                        <i class="ri ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6 mt-3">
                                            <label class="small text-muted fw-semibold mb-1">Nombre de la Variante</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="ri {{ $tipoSeleccionado && $tipoSeleccionado->icono ? $tipoSeleccionado->icono : 'ri-price-tag-3-line' }}"></i>
                                                </span>
                                                <select class="form-select" wire:model.live="variantes.{{ $index }}.tipo_variante_id">
                                                    <option value="">Seleccionar tipo...</option>
                                                    @foreach($tiposVariantes as $tv)
                                                        <option value="{{ $tv->id }}">{{ $tv->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="small text-muted fw-semibold mb-1">SKU</label>
                                            <input type="text" class="form-control" wire:model="variantes.{{ $index }}.sku_variante" placeholder="SKU de la variante">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Precio Costo</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control" wire:model="variantes.{{ $index }}.precio_costo" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Precio Venta</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control" wire:model="variantes.{{ $index }}.precio_venta" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Cantidad</label>
                                            <input type="number" class="form-control" wire:model="variantes.{{ $index }}.stock" min="0" placeholder="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Código Barras</label>
                                            <input type="text" class="form-control" wire:model="variantes.{{ $index }}.codigo_barras" placeholder="EAN / UPC">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Imagen de la Variante</label>
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($variante['imagen_existente']))
                                                    <img src="{{ Storage::url($variante['imagen_existente']) }}" class="rounded border" style="width:44px;height:44px;object-fit:cover;">
                                                @else
                                                    <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width:44px;height:44px;">
                                                        <i class="ri ri-image-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control form-control-sm" wire:model="variantes_imagenes.{{ $index }}" accept="image/*">
                                            </div>
                                            <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Texto Alternativo (Alt)</label>
                                            <input type="text" class="form-control" wire:model="variantes.{{ $index }}.alt" placeholder="Descripción para accesibilidad">
                                        </div>
                                    </div>
                                    @if($valoresPredefinidos->count())
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="small text-muted fw-semibold mb-0">Valores disponibles para {{ $tipoSeleccionado->nombre }}</label>
                                                @if($valorSeleccionadoId)
                                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="clearValorPredefinido({{ $index }})">
                                                        <i class="ri ri-close-circle-line me-1"></i>Liberar selección
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($valoresPredefinidos as $vv)
                                                    @php $isSelected = ($valorSeleccionadoId == $vv->id); @endphp
                                                    <button type="button" class="btn btn-sm {{ $isSelected ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}" wire:click="selectValorPredefinido({{ $index }}, {{ $vv->id }})">
                                                        @if($vv->color_hex)
                                                            <span class="d-inline-block rounded-circle border me-1" style="width:12px;height:12px;background:{{ $vv->color_hex }};vertical-align:middle;"></span>
                                                        @endif
                                                        {{ $vv->valor }}
                                                        @if($vv->codigo)<small class="opacity-75">({{ $vv->codigo }})</small>@endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($variante['tipo_variante_id'])
                                        <div class="alert alert-light border small py-2 mb-3">
                                            <i class="ri ri-information-line me-1"></i>No hay valores predefinidos para este tipo. Usa el campo de valor personalizado.
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Valor Personalizado</label>
                                            <input type="text" class="form-control" wire:model="variantes.{{ $index }}.valor" placeholder="Ej: Medio bulto, Especial, Único..." {{ $valorSeleccionadoId ? 'disabled' : '' }}>
                                            @if($valorSeleccionadoId)
                                                <small class="text-muted">Deshabilitado porque se seleccionó un valor predefinido arriba.</small>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="small text-muted fw-semibold mb-1">Activo</label>
                                                    <div class="form-check form-switch mt-1">
                                                        <input class="form-check-input" type="checkbox" wire:model="variantes.{{ $index }}.status">
                                                        <label class="form-check-label small">Variante activa</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small text-muted fw-semibold mb-1">Tamaño</label>
                                                    <input type="text" class="form-control form-control-sm" wire:model="variantes.{{ $index }}.tamano" placeholder="S, M, L">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small text-muted fw-semibold mb-1">Peso</label>
                                                    <input type="number" step="0.001" class="form-control form-control-sm" wire:model="variantes.{{ $index }}.peso" placeholder="kg">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Presentación</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="variantes.{{ $index }}.presentacion" placeholder="Tableta, Jarabe...">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted fw-semibold mb-1">Unidad Medida</label>
                                            <select class="form-select form-select-sm" wire:model="variantes.{{ $index }}.unidad_medida">
                                                @foreach($unidades as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Nota interna</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="variantes.{{ $index }}.atributo" placeholder="Nota interna (no se muestra al cliente)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endif --}}

                {{-- TAB: OPCIONES --}}
                @if($activeTab === 'opciones')
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Configuración del Producto</h5></div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" wire:model="status" id="opt_status">
                                <label class="form-check-label fw-semibold" for="opt_status">Producto activo</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" wire:model.live="es_medicamento" id="opt_med">
                                <label class="form-check-label fw-semibold" for="opt_med">Es medicamento</label>
                            </div>
                            @if($es_medicamento)
                                <div class="alert alert-warning py-2 small">
                                    <i class="ri ri-information-line me-1"></i>
                                    La <strong>fecha de vencimiento</strong> será obligatoria en la pestaña "Precios y Stock".
                                </div>
                            @endif
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="requiere_receta" id="opt_receta">
                                <label class="form-check-label fw-semibold" for="opt_receta">Requiere receta médica</label>
                            </div>
                        </div>
                    </div>

                    @if(!$isEdit)
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Stock Inicial <small class="text-muted">(Opcional)</small></h5></div>
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

                    @if($isEdit)
                        @php $stockActual = $this->getStockActual(); $stockTotal = $this->getStockTotal(); @endphp
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Stock Actual</h5>
                                <a href="{{ route('admin.inventario.productos.show', $producto) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri ri-file-list-3-line me-1"></i>Kardex
                                </a>
                            </div>
                            <div class="card-body p-0">
                                @forelse($stockActual as $stock)
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                        <small class="fw-semibold">{{ $stock->almacen->nombre }}</small>
                                        <span class="badge bg-{{ $stock->cantidad <= $stock_minimo ? 'danger' : 'success' }} fs-6">{{ $stock->cantidad }}</span>
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
                                        <small class="text-muted">Valorización: <strong>${{ number_format($stockTotal * (float)$precio_costo, 2) }}</strong></small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

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
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Costo:</span><span class="fw-bold">${{ number_format($c2, 2) }}</span></div>
                                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Venta:</span><span class="fw-bold">${{ number_format($v2, 2) }}</span></div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Ganancia:</span><span class="fw-bold text-{{ $mc2 }}">${{ number_format($g2, 2) }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Margen:</span><span class="fw-bold fs-5 text-{{ $mc2 }}">{{ $m2 }}%</span></div>
                            </div>
                        </div>
                    @endif
                @endif

            </div>

            {{-- SIDEBAR: botones de acción siempre visibles --}}
            <div class="col-lg-3">
                <div class="sticky-top" style="top:1rem;z-index:100;">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri ri-save-line me-1"></i>{{ $isEdit ? 'Actualizar' : 'Guardar' }} Producto
                                </button>
                                <a href="{{ route('admin.inventario.productos.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri ri-close-line me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </form>
</div>
