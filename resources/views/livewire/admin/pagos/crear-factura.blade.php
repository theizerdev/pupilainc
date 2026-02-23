<div class="container-fluid py-4">
    {{-- Mensajes flash --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Registrar Pago
                                @if($consulta_seleccionada)
                                    - Consulta #{{ $consulta_seleccionada->codigo }}
                                @endif
                            </h6>
                            @if($consulta_seleccionada && $consulta_seleccionada->paciente)
                            <small class="text-muted">
                                Paciente: <strong>{{ $consulta_seleccionada->paciente->nombre_completo }}</strong>
                                | Doc: {{ $consulta_seleccionada->paciente->documento_identidad ?? 'N/A' }}
                                | Fecha consulta: {{ $consulta_seleccionada->fecha_consulta->format('d/m/Y') }}
                            </small>
                            @endif
                        </div>
                        <span class="badge bg-info">Tasa BCV: $1 =  {{ number_format($tasa_usd, 2) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- ============ BUSCAR CONSULTA (si no viene de una consulta) ============ --}}
                    @if(!$consulta_seleccionada)
                    <div class="card border border-primary mb-3">
                        <div class="card-header py-2 bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-sm text-primary"><i class="fas fa-search me-2"></i>Buscar Consulta</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Buscar por Paciente (Nombre, Apellido o Documento)</label>
                                    <div class="position-relative">
                                        <input wire:model.live.debounce.300ms="search_consulta" type="text" class="form-control" placeholder="Ej: Juan Pérez, 12345678..." autocomplete="off">

                                        {{-- Dropdown de resultados --}}
                                        @if(count($consultas) > 0)
                                        <div class="position-absolute w-100 bg-white border rounded shadow-sm mt-1" style="z-index: 1000; max-height: 400px; overflow-y: auto;">
                                            @foreach($consultas as $consulta)
                                            <div wire:click="seleccionarConsulta({{ $consulta->id }})" class="p-3 border-bottom cursor-pointer" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="text-primary">Consulta #{{ $consulta->codigo ?? $consulta->id }}</strong>
                                                        <br><strong>{{ $consulta->paciente->nombre_completo }}</strong>
                                                        <br><small class="text-muted">Doc: {{ $consulta->paciente->documento_identidad ?? 'N/A' }}</small>
                                                        @if($consulta->medico)
                                                        <br><small class="text-info">Dr(a). {{ $consulta->medico->nombre_completo }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted">{{ $consulta->fecha_consulta->format('d/m/Y') }}</small>
                                                        <br><span class="badge bg-success">{{ ucfirst($consulta->estado) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>Busque una consulta finalizada para asociar el pago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-success mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Consulta Asociada:</strong> {{ $consulta_seleccionada->codigo }}
                                | Paciente: {{ $consulta_seleccionada->paciente->nombre_completo }}
                                @if($consulta_seleccionada->medico)
                                | Médico: Dr(a). {{ $consulta_seleccionada->medico->nombre }} {{ $consulta_seleccionada->medico->apellido }}
                                @endif
                            </div>
                            <button type="button" wire:click="$set('consulta_seleccionada', null)" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cambiar
                            </button>
                        </div>
                    </div>
                    @endif

                    <form wire:submit.prevent="guardar">
                        {{-- ============ SECCIÓN 1: CONFIGURACIÓN DEL DOCUMENTO ============ --}}
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-sm"><i class="fas fa-cog me-2"></i>Configuración del Documento</h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    <!-- Tipo de Documento -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Tipo Documento *</label>
                                        <select wire:model.live="tipo_pago" wire:change="obtenerProximoNumero" class="form-select">
                                            <option value="recibo">Recibo</option>
                                            <option value="factura">Factura</option>
                                            <option value="boleta">Boleta</option>
                                        </select>
                                        @if($proximo_numero)
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-hashtag"></i> Número: <strong class="text-primary">{{ $proximo_numero }}</strong>
                                        </small>
                                        @endif
                                    </div>

                                    <!-- Número de Control Fiscal -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-shield-alt text-primary"></i> N° Control Fiscal
                                        </label>
                                        @if($proximo_control_fiscal)
                                        <div class="form-control bg-light text-center" style="font-size: 1.3rem; font-weight: bold; color: #0d6efd; border: 2px solid #0d6efd;">
                                            {{ $proximo_control_fiscal }}
                                        </div>

                                        @else
                                        <div class="form-control bg-light text-center text-muted">
                                            Se asignará automáticamente
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Método de Pago -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Método de Pago *</label>
                                        <select wire:model.live="metodo_pago" class="form-select">
                                            <option value="efectivo_bs">Efectivo Bs</option>
                                            <option value="efectivo_usd">Efectivo USD</option>
                                            <option value="transferencia_bs">Transferencia Bs</option>
                                            <option value="transferencia_usd">Transferencia USD</option>
                                            <option value="pago_movil">Pago Móvil</option>
                                            <option value="zelle">Zelle</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="mixto">Mixto</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">

                                </div>

                                <div class="row">
                                    <!-- Factura Fiscal -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold d-block">Documento Fiscal</label>
                                        <div class="form-check form-switch mt-1">
                                            <input wire:model.live="es_factura_fiscal" class="form-check-input" type="checkbox" id="factura_fiscal" role="switch">
                                            <label class="form-check-label" for="factura_fiscal">
                                                @if($es_factura_fiscal)
                                                    <span class="badge bg-primary"><i class="fas fa-check"></i> Factura Fiscal SENIAT</span>
                                                @else
                                                    <span class="text-muted">No fiscal</span>
                                                @endif
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Condición de Pago -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Condición de Pago</label>
                                        <select wire:model="condicion_pago" class="form-select">
                                            <option value="contado">Contado</option>
                                            <option value="credito">Crédito</option>
                                        </select>
                                    </div>

                                    <!-- Referencia -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Referencia</label>
                                        <input wire:model="referencia" type="text" class="form-control" placeholder="N° referencia bancaria">
                                    </div>
                                </div>

                                {{-- Datos Fiscales SENIAT (del Paciente) --}}
                                @if($es_factura_fiscal)
                                <div class="row">
                                    <div class="col-12">
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-primary fw-bold"><i class="fas fa-landmark me-1"></i> Datos Fiscales del Paciente (SENIAT)</small>
                                            @if($paciente_tiene_fiscal)
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Cliente fiscal existente</span>
                                            @else
                                            <span class="badge bg-info"><i class="fas fa-user-plus me-1"></i>Se creará automáticamente</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Tipo + Número Documento (RIF/CI) -->
                                    <div class="col-md-2 mb-2 mt-2">
                                        <label class="form-label text-xs fw-bold">Tipo *</label>
                                        <select wire:model="fiscal_tipo_documento" class="form-select form-select-sm">
                                            <option value="V">V - Venezolano</option>
                                            <option value="E">E - Extranjero</option>
                                            <option value="J">J - Jurídico</option>
                                            <option value="G">G - Gobierno</option>
                                            <option value="P">P - Pasaporte</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2 mt-2">
                                        <label class="form-label text-xs fw-bold">N° Documento / RIF *</label>
                                        <input wire:model="fiscal_numero_documento" type="text" class="form-control form-control-sm @error('fiscal_numero_documento') is-invalid @enderror" placeholder="Ej: 12345678">
                                        @error('fiscal_numero_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Razón Social -->
                                    <div class="col-md-4 mb-2 mt-2">
                                        <label class="form-label text-xs fw-bold">Razón Social / Nombre *</label>
                                        <input wire:model="fiscal_razon_social" type="text" class="form-control form-control-sm @error('fiscal_razon_social') is-invalid @enderror" placeholder="Nombre completo o razón social">
                                        @error('fiscal_razon_social') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Condición de Pago -->
                                    <div class="col-md-3 mb-2 mt-2">
                                        <label class="form-label text-xs fw-bold">Condición de Pago</label>
                                        <select wire:model="condicion_pago" class="form-select form-select-sm">
                                            <option value="contado">Contado</option>
                                            <option value="credito">Crédito</option>
                                        </select>
                                    </div>

                                    <!-- Dirección Fiscal -->
                                    <div class="col-md-5 mb-2">
                                        <label class="form-label text-xs fw-bold">Dirección Fiscal *</label>
                                        <input wire:model="fiscal_direccion" type="text" class="form-control form-control-sm @error('fiscal_direccion') is-invalid @enderror" placeholder="Dirección fiscal completa">
                                        @error('fiscal_direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Teléfono -->
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label text-xs fw-bold">Teléfono *</label>
                                        <input wire:model="fiscal_telefono" type="text" class="form-control form-control-sm @error('fiscal_telefono') is-invalid @enderror" placeholder="0414-1234567">
                                        @error('fiscal_telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label text-xs">Email</label>
                                        <input wire:model="fiscal_email" type="email" class="form-control form-control-sm" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ============ SECCIÓN 2: PAGO MIXTO ============ --}}
                        @if($metodo_pago === 'mixto')
                        <div class="card border border-info mb-3">
                            <div class="card-header py-2 bg-info bg-opacity-10">
                                <h6 class="mb-0 text-sm text-info"><i class="fas fa-layer-group me-2"></i>Detalles Pago Mixto</h6>
                            </div>
                            <div class="card-body py-3">
                                @foreach($pagos_mixtos as $index => $pago)
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-4">
                                        <select wire:model.live="pagos_mixtos.{{ $index }}.metodo" class="form-select form-select-sm">
                                            <option value="efectivo_bs">Efectivo Bs</option>
                                            <option value="efectivo_usd">Efectivo USD</option>
                                            <option value="transferencia_bs">Transferencia Bs</option>
                                            <option value="transferencia_usd">Transferencia USD</option>
                                            <option value="pago_movil">Pago Móvil</option>
                                            <option value="zelle">Zelle</option>
                                            <option value="paypal">PayPal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Bs</span>
                                            <input wire:model.live="pagos_mixtos.{{ $index }}.monto_bs" type="number" step="0.01" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input wire:model.live="pagos_mixtos.{{ $index }}.monto_usd" type="number" step="0.01" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" wire:click="eliminarPagoMixto({{ $index }})" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                                <button type="button" wire:click="agregarPagoMixto" class="btn btn-sm btn-outline-info mt-1">
                                    <i class="fas fa-plus me-1"></i> Agregar Método de Pago
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- ============ SECCIÓN 3: ITEMS / DETALLES ============ --}}
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-sm"><i class="fas fa-list me-2"></i>Items del Documento</h6>
                            </div>
                            <div class="card-body py-3">
                                {{-- Formulario agregar item --}}
                                <div class="card border-primary mb-3">
                                    <div class="card-header py-2 bg-primary bg-opacity-10">
                                        <small class="fw-bold text-primary"><i class="fas fa-shopping-cart me-1"></i>Buscar y Agregar Servicios</small>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row align-items-end">
                                            <div class="col-md-5 mb-2">
                                                <label class="form-label text-xs fw-bold mb-1">Buscar Servicio</label>
                                                <div class="position-relative">
                                                    <input wire:model.live.debounce.300ms="buscar_baremo" type="text" class="form-control form-control-sm" placeholder="Buscar por nombre, código o descripción..." autocomplete="off">
                                                    @if($buscar_baremo)
                                                    <button type="button" wire:click="limpiarBusqueda" class="btn btn-sm btn-link position-absolute end-0 top-0 text-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    @endif

                                                    {{-- Dropdown de resultados --}}
                                                    @if(count($baremos_filtrados) > 0)
                                                    <div class="position-absolute w-100 bg-white border rounded shadow-sm" style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                                                        @foreach($baremos_filtrados as $baremo)
                                                        <div wire:click="seleccionarBaremo({{ $baremo['id'] }})" class="p-2 border-bottom cursor-pointer hover-bg-light" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong class="text-primary">{{ $baremo['nombre_servicio'] }}</strong>
                                                                    <br><small class="text-muted">Código: {{ $baremo['codigo'] }}</small>
                                                                    @if($baremo['exento_iva'])
                                                                    <span class="badge badge-sm bg-warning text-dark ms-1">Exento IVA</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-end">
                                                                    <strong class="text-success">${{ number_format($baremo['costo_usd'], 2) }}</strong>
                                                                    <br><small class="text-muted">Bs. {{ number_format($baremo['costo_usd'] * $tasa_usd, 2, ',', '.') }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label text-xs fw-bold mb-1">Descripción *</label>
                                                <input wire:model="descripcion" type="text" class="form-control form-control-sm @error('descripcion') is-invalid @enderror" placeholder="Descripción">
                                                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label text-xs fw-bold mb-1">Cantidad *</label>
                                                <input wire:model="cantidad" type="number" min="1" step="0.01" class="form-control form-control-sm text-center" placeholder="1">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label text-xs fw-bold mb-1">Precio USD *</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input wire:model="precio_unitario" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 text-end">
                                                <button type="button" wire:click="agregarDetalle" class="btn btn-sm btn-success">
                                                    <i class="fas fa-cart-plus me-1"></i> Agregar al Carrito
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @error('detalles') <div class="alert alert-danger py-2 mb-3"><small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small></div> @enderror

                                {{-- Tabla de items --}}
                                @if(count($detalles) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-items-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="40">#</th>
                                                <th>Descripción</th>
                                                <th class="text-center" width="80">Cant.</th>
                                                <th class="text-end" width="100">P/U USD</th>
                                                <th class="text-end" width="120">P/U Bs.</th>
                                                <th class="text-end" width="130">Subtotal USD</th>
                                                <th class="text-end" width="140">Subtotal Bs.</th>
                                                <th class="text-center" width="80">IVA</th>
                                                <th class="text-center" width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($detalles as $index => $detalle)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $detalle['descripcion'] }}</strong>
                                                    @if($detalle['baremo_id'])
                                                    <br><small class="text-muted"><i class="fas fa-tag"></i> Baremo</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $detalle['cantidad'] }}</span>
                                                </td>
                                                <td class="text-end text-muted">
                                                    <small>${{ number_format($detalle['precio_unitario'], 2) }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <strong>Bs. {{ number_format($detalle['precio_unitario'] * $tasa_usd, 2, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-end text-muted">
                                                    <small>${{ number_format($detalle['subtotal'], 2) }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-success">Bs. {{ number_format($detalle['subtotal'] * $tasa_usd, 2, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    @if($detalle['exento_iva'] ?? false)
                                                    <span class="badge badge-sm bg-warning text-dark">Exento</span>
                                                    @elseif($detalle['aplica_iva'] ?? true)
                                                    <span class="badge badge-sm bg-success">{{ $detalle['iva_alicuota'] ?? 16 }}%</span>
                                                    @else
                                                    <span class="badge badge-sm bg-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarDetalle({{ $index }})" class="btn btn-sm btn-danger p-1" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Subtotal Items:</td>
                                                <td class="text-end fw-bold">${{ number_format(collect($detalles)->sum('subtotal'), 2) }}</td>
                                                <td class="text-end fw-bold text-success">Bs. {{ number_format(collect($detalles)->sum('subtotal') * $tasa_usd, 2, ',', '.') }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0 fw-bold">No hay items agregados</p>
                                    <small>Use el formulario de arriba para agregar servicios al documento</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ============ SECCIÓN 4: TOTALES Y OBSERVACIONES ============ --}}
                        <div class="row">
                            <div class="col-md-7">
                                <div class="card border mb-3">
                                    <div class="card-body py-3">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Observaciones</label>
                                            <textarea wire:model="observaciones" class="form-control" rows="3" placeholder="Notas u observaciones adicionales..."></textarea>
                                        </div>

                                        {{-- Aviso IGTF --}}
                                        @if($es_factura_fiscal && $igtf_monto > 0)
                                        <div class="alert alert-warning py-2 mb-0">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                <strong>IGTF:</strong> Se aplica el 3% sobre el monto pagado en divisas extranjeras,
                                                conforme al Decreto con Rango, Valor y Fuerza de Ley de Impuesto a las Grandes Transacciones Financieras.
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="card border border-primary">
                                    <div class="card-header py-2 bg-primary bg-opacity-10">
                                        <h6 class="mb-0 text-sm text-primary"><i class="fas fa-calculator me-2"></i>Resumen de Totales</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>Subtotal:</td>
                                                    <td class="text-end fw-bold">${{ number_format(collect($detalles)->sum('subtotal'), 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Descuento:</td>
                                                    <td class="text-end">
                                                        <div class="input-group input-group-sm" style="width: 120px; margin-left: auto;">
                                                            <span class="input-group-text">$</span>
                                                            <input wire:model.live.debounce.500ms="descuento" type="number" step="0.01" min="0" class="form-control text-end" placeholder="0.00">
                                                        </div>
                                                    </td>
                                                </tr>

                                                @if($es_factura_fiscal)
                                                <tr class="table-light">
                                                    <td colspan="2"><small class="fw-bold text-primary">Desglose Fiscal SENIAT</small></td>
                                                </tr>
                                                <tr>
                                                    <td><small>Base Imponible (16%):</small></td>
                                                    <td class="text-end"><small>{{ money($base_imponible, 2) }}</small></td>
                                                </tr>
                                                <tr>
                                                    <td><small class="fw-bold">IVA (16%):</small></td>
                                                    <td class="text-end"><small class="fw-bold">{{ money($iva_monto, 2) }}</small></td>
                                                </tr>
                                                @if($igtf_monto > 0)
                                                <tr class="table-warning">
                                                    <td><small class="fw-bold">IGTF (3%) - Divisas:</small></td>
                                                    <td class="text-end"><small class="fw-bold">{{ money($igtf_monto, 2) }}</small></td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td><small class="text-muted">Condición:</small></td>
                                                    <td class="text-end"><small class="text-muted">{{ $condicion_pago === 'credito' ? 'Crédito' : 'Contado' }}</small></td>
                                                </tr>
                                                @endif

                                                <tr>
                                                    <td colspan="2"><hr class="my-1"></td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td class="fw-bold fs-6">Total USD:</td>
                                                    <td class="text-end fw-bold fs-6">${{ number_format(collect($detalles)->sum('subtotal') - $descuento + ($es_factura_fiscal ? ($iva_monto + $igtf_monto) / $tasa_usd : 0), 2) }}</td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td class="fw-bold">Total Bs:</td>
                                                    <td class="text-end fw-bold">{{ money($total, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end">
                                                        <small class="text-muted">Tasa BCV: $1 =  {{ money($tasa_usd, 2) }}</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============ BOTONES DE ACCIÓN ============ --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" @if(count($detalles) === 0) disabled @endif>
                                <i class="fas fa-save me-1"></i>
                                @if($es_factura_fiscal)
                                    Emitir Factura Fiscal
                                @else
                                    Guardar {{ ucfirst($tipo_pago) }}
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
