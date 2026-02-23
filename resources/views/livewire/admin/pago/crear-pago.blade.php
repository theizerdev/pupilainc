<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Nueva Factura</h6>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="guardar">
                            <div class="row">
                                <!-- Búsqueda de Consulta Finalizada -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Buscar Consulta Finalizada <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="search_consulta" class="form-control @error('consulta_id') is-invalid @enderror" placeholder="Buscar por paciente, doctor o ID de consulta...">
                                    @if(strlen($search_consulta) >= 2 && !$consulta_id)
                                    <div class="list-group mt-1 position-absolute" style="z-index: 1000; max-height: 300px; overflow-y: auto; width: 95%;">
                                        @forelse($consultas as $consulta)
                                        <button type="button" wire:click="seleccionarConsulta({{ $consulta->id }})" class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>Consulta #{{ $consulta->id }}</strong>
                                                    <span class="badge bg-success ms-2">{{ ucfirst($consulta->estado) }}</span>
                                                    <br>
                                                    <small><i class="fas fa-user"></i> <strong>Paciente:</strong> {{ $consulta->paciente->nombre_completo }}</small>
                                                    <br>
                                                    <small><i class="fas fa-user-md"></i> <strong>Doctor:</strong> {{ $consulta->medico->nombre_completo }}</small>
                                                    <br>
                                                    <small><i class="fas fa-calendar"></i> {{ $consulta->fecha_consulta->format('d/m/Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </button>
                                        @empty
                                        <div class="list-group-item">No se encontraron consultas finalizadas</div>
                                        @endforelse
                                    </div>
                                    @endif
                                    @error('consulta_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                    @if($consulta_seleccionada)
                                    <div class="alert alert-info mt-2">
                                        <strong>Consulta Seleccionada:</strong><br>
                                        <i class="fas fa-user"></i> {{ $consulta_seleccionada->paciente->nombre_completo }}<br>
                                        <i class="fas fa-user-md"></i> Dr. {{ $consulta_seleccionada->medico->nombre_completo }}<br>
                                        <i class="fas fa-calendar"></i> {{ $consulta_seleccionada->fecha_consulta->format('d/m/Y H:i') }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <!-- Búsqueda de Servicios -->
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Buscar Servicio <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.300ms="search_servicio" class="form-control" placeholder="Buscar por código o nombre del servicio...">
                                    @if(strlen($search_servicio) >= 2)
                                    <div class="list-group mt-1">
                                        @forelse($servicios as $servicio)
                                        <button type="button" wire:click="agregarServicio({{ $servicio->id }})" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $servicio->nombre_servicio }}</strong>
                                                <span class="badge bg-primary ms-2">{{ $servicio->codigo }}</span>
                                                <span class="badge" style="background-color: {{ $servicio->especialidad->color ?? '#6c757d' }}">{{ $servicio->especialidad->nombre ?? '' }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    @if($servicio->exento_iva)
                                                    <span class="badge bg-warning">Exento IVA</span>
                                                    @elseif($servicio->aplica_iva)
                                                    <span class="badge bg-success">Aplica IVA</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="text-primary fw-bold">${{ number_format($servicio->costo_usd, 2) }}</span>
                                        </button>
                                        @empty
                                        <div class="list-group-item">No se encontraron servicios</div>
                                        @endforelse
                                    </div>
                                    @endif
                                    @error('carrito') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Carrito de Compras -->
                            @if(count($carrito) > 0)
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Carrito de Servicios</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Descripción</th>
                                                    <th>Especialidad</th>
                                                    <th width="100">Cantidad</th>
                                                    <th class="text-end">P. Unit.</th>
                                                    <th class="text-end">Subtotal</th>
                                                    <th width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($carrito as $key => $item)
                                                <tr>
                                                    <td><span class="badge bg-dark">{{ $item['codigo'] }}</span></td>
                                                    <td>{{ $item['descripcion'] }}</td>
                                                    <td><small>{{ $item['especialidad'] }}</small></td>
                                                    <td>
                                                        <input type="number" wire:change="actualizarCantidad('{{ $key }}', $event.target.value)" value="{{ $item['cantidad'] }}" class="form-control form-control-sm" min="1">
                                                    </td>
                                                    <td class="text-end">${{ number_format($item['precio_unitario'], 2) }}</td>
                                                    <td class="text-end fw-bold">${{ number_format($item['cantidad'] * $item['precio_unitario'], 2) }}</td>
                                                    <td>
                                                        <button type="button" wire:click="eliminarItem('{{ $key }}')" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <!-- Configuración de Pago -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Configuración de Pago</h6>

                                            <div class="mb-3">
                                                <label class="form-label">Tipo de Documento</label>
                                                <select wire:model="tipo_pago" class="form-select">
                                                    <option value="factura">Factura</option>
                                                    <option value="boleta">Boleta</option>
                                                    <option value="recibo">Recibo</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Método de Pago</label>
                                                <select wire:model.live="metodo_pago" class="form-select">
                                                    <option value="efectivo_bs">Efectivo Bs</option>
                                                    <option value="efectivo_usd">Efectivo USD</option>
                                                    <option value="transferencia_bs">Transferencia Bs</option>
                                                    <option value="transferencia_usd">Transferencia USD</option>
                                                    <option value="pago_movil">Pago Móvil</option>
                                                    <option value="zelle">Zelle</option>
                                                    <option value="paypal">PayPal</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Descuento ($)</label>
                                                <input type="number" wire:model.live="descuento" step="0.01" class="form-control">
                                            </div>

                                            <div class="form-check mb-3">
                                                <input type="checkbox" wire:model.live="es_factura_fiscal" class="form-check-input" id="factura_fiscal">
                                                <label class="form-check-label" for="factura_fiscal">Factura Fiscal (SENIAT)</label>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Observaciones</label>
                                                <textarea wire:model="observaciones" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen de Totales -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Resumen de Pago</h6>

                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Subtotal:</td>
                                                    <td class="text-end">${{ number_format($subtotal, 2) }}</td>
                                                </tr>
                                                @if($descuento > 0)
                                                <tr>
                                                    <td>Descuento:</td>
                                                    <td class="text-end text-danger">-${{ number_format($descuento, 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($iva_monto > 0)
                                                <tr>
                                                    <td>IVA (16%):</td>
                                                    <td class="text-end">${{ number_format($iva_monto, 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($igtf_monto > 0)
                                                <tr>
                                                    <td>IGTF (3%):</td>
                                                    <td class="text-end">${{ number_format($igtf_monto, 2) }}</td>
                                                </tr>
                                                @endif
                                                <tr class="fw-bold border-top">
                                                    <td>Total USD:</td>
                                                    <td class="text-end text-primary">${{ number_format($total, 2) }}</td>
                                                </tr>
                                                <tr class="fw-bold">
                                                    <td>Total Bs:</td>
                                                    <td class="text-end text-success">Bs {{ number_format($total * $tasa_usd, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><small class="text-muted">Tasa: $1 = Bs {{ number_format($tasa_usd, 2) }}</small></td>
                                                </tr>
                                            </table>

                                            <div class="d-grid gap-2 mt-4">
                                                <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                                    <span wire:loading.remove wire:target="guardar">
                                                        <i class="fas fa-save me-2"></i> Generar Factura
                                                    </span>
                                                    <span wire:loading wire:target="guardar">
                                                        <i class="fas fa-spinner fa-spin me-2"></i> Procesando...
                                                    </span>
                                                </button>
                                                <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times me-2"></i> Cancelar
                                                </a>
                                            </div>

                                            @if (session()->has('error'))
                                            <div class="alert alert-danger mt-3">
                                                {{ session('error') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
