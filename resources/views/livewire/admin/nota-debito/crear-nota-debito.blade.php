<div class="py-4">
    {{-- Mensajes flash --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
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
                                <i class="fas fa-file-invoice me-2 text-success"></i>
                                Nueva Nota de Débito
                            </h6>
                            <small class="text-muted">Todos los montos se expresan en Bolívares (Bs.) conforme a la normativa SENIAT</small>
                        </div>
                        <span class="badge bg-info fs-6">Tasa BCV: $1 = Bs. {{ number_format($tasa_usd, 2, ',', '.') }}</span>
                    </div>
                </div>
                <div class="card-body">

                    {{-- ============ PASO 1: BUSCAR FACTURA ORIGEN ============ --}}
                    @if(!$pago_origen && !$nota_credito_origen)
                    <div class="card border border-primary mb-3">
                        <div class="card-header py-2 bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-sm text-primary"><i class="fas fa-search me-2"></i>Buscar Documento de Origen</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Serie o Número del Documento</label>
                                    <div class="input-group">
                                        <input wire:model="buscar_serie" type="text" class="form-control" placeholder="Ej: F001-00000123 o 00000123" wire:keydown.enter="buscarFactura">
                                        <button wire:click="buscarFactura" class="btn btn-primary" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="buscarFactura"><i class="fas fa-search me-1"></i> Buscar</span>
                                            <span wire:loading wire:target="buscarFactura"><i class="fas fa-spinner fa-spin me-1"></i> Buscando...</span>
                                        </button>
                                    </div>
                                    @error('buscar_serie') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @else

                    {{-- ============ DOCUMENTO ORIGEN ENCONTRADO ============ --}}
                    @if($nota_credito_origen)
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Anulación de Nota de Crédito:</strong> Esta Nota de Débito anulará la NC {{ $nota_credito_origen->numero_completo }}
                    </div>
                    @endif
                    
                    <div class="card border border-info mb-3">
                        <div class="card-header py-2 bg-info bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-sm text-info"><i class="fas fa-file-invoice-dollar me-2"></i>Documento de Origen</h6>
                                <button wire:click="$set('pago_origen', null)" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cambiar
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Documento</small>
                                    <strong>{{ $pago_origen->numero_completo }}</strong>
                                    @if($pago_origen->es_factura_fiscal)
                                    <span class="badge bg-primary ms-1">Fiscal</span>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted d-block">Fecha</small>
                                    <strong>{{ $pago_origen->fecha->format('d/m/Y') }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Cliente</small>
                                    <strong>
                                        @if($pago_origen->clienteFiscal)
                                            {{ $pago_origen->clienteFiscal->documento_completo }} - {{ $pago_origen->clienteFiscal->razon_social }}
                                        @elseif($pago_origen->consulta && $pago_origen->consulta->paciente)
                                            {{ $pago_origen->consulta->paciente->nombre_completo }}
                                        @else
                                            N/A
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-md-3 text-end">
                                    <small class="text-muted d-block">Total Original</small>
                                    <strong class="text-success fs-6">Bs. {{ number_format($pago_origen->total_bs ?? ($pago_origen->total_usd * $tasa_usd), 2, ',', '.') }}</strong>
                                    <br><small class="text-muted">(USD {{ format_money($pago_origen->total_usd, 2) }})</small>
                                </div>
                            </div>
                            @if($pago_origen->numero_control_fiscal)
                            <div class="mt-2">
                                <small class="text-primary"><i class="fas fa-landmark me-1"></i>N° Control Fiscal: <strong>{{ $pago_origen->numero_control_fiscal }}</strong></small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <form wire:submit.prevent="guardar">

                        {{-- ============ TIPO Y MOTIVO ============ --}}
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-sm"><i class="fas fa-cog me-2"></i>Datos de la Nota de Débito</h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label fw-bold">Tipo de Nota de Débito *</label>
                                        <select wire:model="tipo_nota_debito_id" class="form-select @error('tipo_nota_debito_id') is-invalid @enderror">
                                            <option value="">Seleccionar tipo...</option>
                                            @foreach($tipos as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->codigo }} - {{ $tipo->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        @error('tipo_nota_debito_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label fw-bold">Motivo / Sustentación *</label>
                                        <textarea wire:model="motivo_nota" class="form-control @error('motivo_nota') is-invalid @enderror" rows="2" placeholder="Describa el motivo del ajuste (ej: diferencial cambiario, servicios adicionales no facturados)..."></textarea>
                                        @error('motivo_nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============ ITEMS / DETALLES ============ --}}
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-sm"><i class="fas fa-list me-2"></i>Items de la Nota de Débito <small class="text-muted">(Montos en Bs.)</small></h6>
                            </div>
                            <div class="card-body py-3">
                                @error('detalles') <div class="alert alert-danger py-2 mb-3"><small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small></div> @enderror

                                {{-- Tabla de items --}}
                                @if(count($detalles) > 0)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-hover align-items-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="40">#</th>
                                                <th>Descripción</th>
                                                <th class="text-center" width="100">Cantidad</th>
                                                <th class="text-end" width="130">P/U (USD)</th>
                                                <th class="text-end" width="130">P/U (Bs.)</th>
                                                <th class="text-end" width="140">Subtotal Bs.</th>
                                                <th class="text-center" width="70">IVA</th>
                                                <th class="text-center" width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($detalles as $index => $detalle)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $detalle['descripcion'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-sm bg-secondary">{{ $detalle['cantidad'] }}</span>
                                                </td>
                                                <td class="text-end text-muted"><small>{{ format_money($detalle['precio_unitario_usd'], 2) }}</small></td>
                                                <td class="text-end">Bs. {{ number_format($detalle['precio_unitario_bs'], 2, ',', '.') }}</td>
                                                <td class="text-end fw-bold">Bs. {{ number_format($detalle['subtotal_bs'], 2, ',', '.') }}</td>
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
                                                    <button type="button" wire:click="eliminarDetalle({{ $index }})" class="btn btn-sm btn-outline-danger p-1">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No hay items cargados. Agregue los conceptos del ajuste.</p>
                                </div>
                                @endif

                                {{-- Agregar item manual --}}
                                <div class="border-top pt-3">
                                    <small class="fw-bold text-muted mb-2 d-block"><i class="fas fa-plus-circle me-1"></i>Agregar Concepto de Ajuste (precio en USD)</small>
                                    <div class="row align-items-end">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label text-xs">Descripción *</label>
                                            <input wire:model="descripcion_temp" type="text" class="form-control form-control-sm" placeholder="Ej: Diferencial cambiario, Servicio adicional">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label text-xs">Cantidad *</label>
                                            <input wire:model="cantidad_temp" type="number" min="0.01" step="0.01" class="form-control form-control-sm" placeholder="1">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label text-xs">Precio USD *</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input wire:model="precio_temp" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <button type="button" wire:click="agregarDetalle" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-plus me-1"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============ TOTALES Y OBSERVACIONES ============ --}}
                        <div class="row">
                            {{-- Referencia SENIAT --}}
                            <div class="col-md-7">
                                <div class="card border mb-3">
                                    <div class="card-body py-3">
                                        {{-- Referencia al Documento Afectado --}}
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-file-invoice me-2"></i>
                                            <strong>Documento Afectado (Ref. SENIAT):</strong><br>
                                            <small>
                                                {{ ucfirst($pago_origen->tipo_pago) }} N°: <strong>{{ $pago_origen->numero_completo }}</strong>
                                                @if($pago_origen->numero_control_fiscal)
                                                | N° Control: <strong>{{ $pago_origen->numero_control_fiscal }}</strong>
                                                @endif
                                                | Fecha: <strong>{{ $pago_origen->fecha->format('d/m/Y') }}</strong>
                                                | Total: <strong>Bs. {{ number_format($pago_origen->total_bs ?? ($pago_origen->total_usd * $tasa_usd), 2, ',', '.') }}</strong>
                                            </small>
                                        </div>

                                        {{-- Coletilla fiscal --}}
                                        <div class="mt-3">
                                            <small class="text-muted fst-italic">
                                                <i class="fas fa-gavel me-1"></i>
                                                "La presente Nota de Débito se emite conforme a lo establecido en el artículo 17 de la Providencia N° SNAT/2011/00071,
                                                para incrementar el monto de la factura original por los conceptos descritos."
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen de Totales --}}
                            <div class="col-md-5">
                                <div class="card border border-success">
                                    <div class="card-header py-2 bg-success bg-opacity-10">
                                        <h6 class="mb-0 text-sm text-success"><i class="fas fa-calculator me-2"></i>Resumen en Bolívares (Bs.)</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>Base Imponible:</td>
                                                    <td class="text-end">Bs. {{ number_format($base_imponible_bs, 2, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Subtotal:</td>
                                                    <td class="text-end fw-bold">Bs. {{ number_format($subtotal_bs, 2, ',', '.') }}</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="2"><small class="fw-bold text-primary">Impuestos SENIAT</small></td>
                                                </tr>
                                                <tr>
                                                    <td><small class="fw-bold">IVA (16%):</small></td>
                                                    <td class="text-end"><small class="fw-bold">Bs. {{ number_format($iva_monto_bs, 2, ',', '.') }}</small></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr class="my-1"></td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td class="fw-bold fs-6 text-success">Total Nota de Débito:</td>
                                                    <td class="text-end fw-bold fs-6 text-success">Bs. {{ number_format($total_bs, 2, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end">
                                                        <small class="text-muted">
                                                            Equiv. USD: {{ format_money($tasa_usd > 0 ? $total_bs / $tasa_usd : 0, 2) }}
                                                            | Tasa BCV: Bs. {{ number_format($tasa_usd, 2, ',', '.') }}
                                                        </small>
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
                            <a href="{{ route('admin.notas-debito.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" @if(count($detalles) === 0) disabled @endif wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="guardar"><i class="fas fa-save me-1"></i> Emitir Nota de Débito</span>
                                <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin me-1"></i> Procesando...</span>
                            </button>
                        </div>
                    </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
