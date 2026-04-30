<div>
    @section('title', 'Nueva Nota de Crédito')

    @push('styles')
    <style>
        .nc-hero { background: linear-gradient(135deg, #EF4444 0%, #F97316 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; margin-bottom:1.5rem; }
        .nc-hero h2 { color:#fff; margin:0; }
        .nc-hero p { opacity:.9; margin:0; }
    </style>
    @endpush

    {{-- Mensajes flash --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri ri-error-warning-line me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.notas-credito.index') }}">Notas de Crédito</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="nc-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-arrow-go-back-line me-2"></i>Nueva Nota de Crédito</h2>
                <p class="mt-1">Todos los montos se expresan en Bolívares (Bs.) conforme a la normativa vigente</p>
            </div>
            @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
            <span class="badge bg-white text-dark fs-6">Tasa BCV: $1 = Bs. {{ format_money($tasa_usd, 2) }}</span>
            @endif
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">

                    {{-- ============ PASO 1: BUSCAR FACTURA ORIGEN ============ --}}
                    @if(!$pago_origen)
                    <div class="card border border-primary mb-3">
                        <div class="card-header py-2 bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-sm text-primary"><i class="ri ri-search-line me-2"></i>Buscar Documento de Origen</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small">Serie o Número del Documento</label>
                                    <div class="input-group">
                                        <input wire:model="buscar_serie" type="text" class="form-control form-control-sm" placeholder="Ej: F001-00000123 o 00000123" wire:keydown.enter="buscarFactura">
                                        <button wire:click="buscarFactura" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="buscarFactura"><i class="ri ri-search-line me-1"></i>Buscar</span>
                                            <span wire:loading wire:target="buscarFactura"><span class="spinner-border spinner-border-sm me-1"></span>Buscando...</span>
                                        </button>
                                    </div>
                                    @error('buscar_serie') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @else

                    {{-- ============ DOCUMENTO ORIGEN ENCONTRADO ============ --}}
                    <div class="card border border-info mb-3">
                        <div class="card-header py-2 bg-info bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-sm text-info"><i class="ri ri-file-list-3-line me-2"></i>Documento de Origen</h6>
                                <button wire:click="$set('pago_origen', null)" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri ri-close-line me-1"></i>Cambiar
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="small text-muted">Documento</div>
                                    <div class="fw-semibold">{{ $pago_origen->numero_completo }}</div>
                                    @if($pago_origen->es_factura_fiscal)
                                    <span class="badge bg-label-primary">Fiscal</span>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <div class="small text-muted">Fecha</div>
                                    <div class="fw-semibold">{{ $pago_origen->fecha->format('d/m/Y') }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted">Cliente</div>
                                    <div class="fw-semibold">
                                        @if($pago_origen->clienteFiscal)
                                            {{ $pago_origen->clienteFiscal->documento_completo }} — {{ $pago_origen->clienteFiscal->razon_social }}
                                        @elseif($pago_origen->consulta && $pago_origen->consulta->paciente)
                                            {{ $pago_origen->consulta->paciente->nombre_completo }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="small text-muted">Total Original</div>
                                    @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                    <div class="fw-semibold text-success">{{ format_money($pago_origen->total_bs ?? ($pago_origen->total_usd * $tasa_usd), 2) }}</div>
                                    <small class="text-muted">(USD {{ format_money($pago_origen->total_usd, 2) }})</small>
                                    @endif
                                </div>
                            </div>
                            @if($pago_origen->numero_control_fiscal)
                            <div class="mt-2">
                                <small class="text-primary"><i class="ri ri-government-line me-1"></i>N° Control Fiscal: <strong>{{ $pago_origen->numero_control_fiscal }}</strong></small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <form wire:submit.prevent="guardar">

                        {{-- ============ TIPO Y MOTIVO ============ --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0"><i class="ri ri-settings-3-line me-2 text-primary"></i>Datos de la Nota de Crédito</h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold small">Tipo de Nota de Crédito *</label>
                                        <select wire:model="tipo_nota_credito_id" class="form-select form-select-sm @error('tipo_nota_credito_id') is-invalid @enderror">
                                            <option value="">Seleccionar tipo...</option>
                                            @foreach($tipos as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->codigo }} — {{ $tipo->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        @error('tipo_nota_credito_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold small">Motivo / Sustentación *</label>
                                        <textarea wire:model="motivo_nota" class="form-control form-control-sm @error('motivo_nota') is-invalid @enderror" rows="2" placeholder="Describa el motivo de la nota de crédito..."></textarea>
                                        @error('motivo_nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============ ITEMS / DETALLES ============ --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Items de la Nota de Crédito <small class="text-muted">(Montos en Bs.)</small></h6>
                            </div>
                            <div class="card-body py-3">
                                @error('detalles') <div class="alert alert-danger py-2 mb-3"><small><i class="ri ri-error-warning-line me-1"></i>{{ $message }}</small></div> @enderror

                                {{-- Tabla de items --}}
                                @if(count($detalles) > 0)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th class="text-center fw-semibold" width="50">
                                                    <i class="ri ri-checkbox-blank-line"></i>
                                                </th>
                                                <th class="text-center fw-semibold" width="40">#</th>
                                                <th class="fw-semibold">Descripción</th>
                                                <th class="text-center fw-semibold" width="100">Cantidad</th>
                                                <th class="text-end fw-semibold" width="130">P/U (USD)</th>
                                                <th class="text-end fw-semibold" width="130">P/U (Bs.)</th>
                                                <th class="text-end fw-semibold" width="140">Subtotal Bs.</th>
                                                <th class="text-center fw-semibold" width="70">IVA</th>
                                                <th class="text-center fw-semibold" width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($detalles as $index => $detalle)
                                            <tr class="{{ isset($items_seleccionados[$index]) ? '' : 'bg-light opacity-50' }}">
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        wire:click="toggleItem({{ $index }})"
                                                        {{ isset($items_seleccionados[$index]) ? 'checked' : '' }}
                                                        class="form-check-input">
                                                </td>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $detalle['descripcion'] }}</td>
                                                <td class="text-center">
                                                    @if(isset($items_seleccionados[$index]) && ($detalle['detalle_origen_id'] ?? null))
                                                    <input wire:model.live.debounce.500ms="detalles.{{ $index }}.cantidad"
                                                        type="number" min="0.01" max="{{ $detalle['cantidad_max'] }}" step="0.01"
                                                        class="form-control form-control-sm text-center" style="width: 80px; margin: 0 auto;">
                                                    @else
                                                    {{ $detalle['cantidad'] }}
                                                    @endif
                                                </td>
                                                <td class="text-end text-muted"><small>{{ format_money($detalle['precio_unitario_usd'], 2) }}</small></td>
                                                <td class="text-end">{{ format_money($detalle['precio_unitario_bs'], 2) }}</td>
                                                <td class="text-end fw-semibold">{{ format_money($detalle['subtotal_bs'], 2) }}</td>
                                                <td class="text-center">
                                                    @if($detalle['exento_iva'] ?? false)
                                                    <span class="badge bg-label-warning">Exento</span>
                                                    @elseif($detalle['aplica_iva'] ?? true)
                                                    <span class="badge bg-label-success">{{ $detalle['iva_alicuota'] ?? $iva_pct }}%</span>
                                                    @else
                                                    <span class="badge bg-label-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if(!($detalle['detalle_origen_id'] ?? null))
                                                    <button type="button" wire:click="eliminarDetalle({{ $index }})" class="btn btn-sm btn-outline-danger p-1">
                                                        <i class="ri ri-delete-bin-line"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="ri ri-inbox-line" style="font-size:1.5rem;opacity:.3;"></i>
                                    <p class="mb-0 small mt-1">No hay items cargados.</p>
                                </div>
                                @endif

                                {{-- Agregar item manual --}}
                                <div class="border-top pt-3">
                                    <small class="fw-semibold text-muted mb-2 d-block"><i class="ri ri-add-circle-line me-1"></i>Agregar Item Adicional (precio en USD)</small>
                                    <div class="row align-items-end g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Descripción *</label>
                                            <input wire:model="descripcion_temp" type="text" class="form-control form-control-sm" placeholder="Descripción del concepto">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small text-muted">Cantidad *</label>
                                            <input wire:model="cantidad_temp" type="number" min="1" step="0.01" class="form-control form-control-sm" placeholder="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted">Precio USD *</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input wire:model="precio_temp" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" wire:click="agregarDetalle" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="ri ri-add-line me-1"></i>Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============ TOTALES Y OBSERVACIONES ============ --}}
                        <div class="row g-4">
                            {{-- Referencia fiscal --}}
                            <div class="col-md-7">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body py-3">
                                        <div class="alert alert-info mb-3 d-flex align-items-start gap-2">
                                            <i class="ri ri-file-list-3-line mt-1"></i>
                                            <div>
                                                <strong>Documento Afectado:</strong><br>
                                                <small>
                                                    {{ ucfirst($pago_origen->tipo_pago) }} N°: <strong>{{ $pago_origen->numero_completo }}</strong>
                                                    @if($pago_origen->numero_control_fiscal)
                                                    | N° Control: <strong>{{ $pago_origen->numero_control_fiscal }}</strong>
                                                    @endif
                                                    | Fecha: <strong>{{ $pago_origen->fecha->format('d/m/Y') }}</strong>
                                                    | Total: <strong>{{ format_money($pago_origen->total_bs ?? ($pago_origen->total_usd * $tasa_usd), 2) }}</strong>
                                                </small>
                                            </div>
                                        </div>

                                        @if($igtf_monto_bs > 0)
                                        <div class="alert alert-warning py-2 mb-0 d-flex align-items-start gap-2">
                                            <i class="ri ri-information-line mt-1"></i>
                                            <small>
                                                <strong>IGTF:</strong> Se aplica el {{ $igtf_pct }}% sobre el monto pagado en divisas extranjeras,
                                                conforme a la normativa vigente.
                                            </small>
                                        </div>
                                        @endif

                                        <div class="mt-3">
                                            <small class="text-muted fst-italic">
                                                <i class="ri ri-government-line me-1"></i>
                                                "La presente Nota de Crédito se emite conforme a lo establecido en la legislación fiscal vigente."
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen de Totales --}}
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm" style="border-left:4px solid #ef4444!important;">
                                    <div class="card-header bg-transparent border-0 pb-0">
                                        <h6 class="mb-0 text-danger"><i class="ri ri-calculator-line me-2"></i>Resumen en Bolívares (Bs.)</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                @if($monto_exento_bs > 0)
                                                <tr>
                                                    <td class="text-muted small">Monto Exento:</td>
                                                    <td class="text-end fw-medium">{{ format_money($monto_exento_bs, 2) }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-muted small">Base Imponible:</td>
                                                    <td class="text-end fw-medium">{{ format_money($base_imponible_bs, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted small">Subtotal:</td>
                                                    <td class="text-end fw-semibold">{{ format_money($subtotal_bs, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="pt-2"><small class="fw-semibold text-primary">Impuestos</small></td>
                                                </tr>
                                                <tr>
                                                    <td class="small"><span class="fw-semibold">IVA ({{ $iva_pct }}%):</span></td>
                                                    <td class="text-end small fw-semibold">{{ format_money($iva_monto_bs, 2) }}</td>
                                                </tr>
                                                @if($igtf_monto_bs > 0)
                                                <tr>
                                                    <td class="small"><span class="fw-semibold">IGTF ({{ $igtf_pct }}%) — Divisas:</span></td>
                                                    <td class="text-end small fw-semibold">{{ format_money($igtf_monto_bs, 2) }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="2"><hr class="my-1"></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold text-danger">Total Nota de Crédito:</td>
                                                    <td class="text-end fw-semibold fs-5 text-danger">{{ format_money($total_bs, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end">
                                                        <small class="text-muted">
                                                            Equiv. USD: {{ format_money($tasa_usd > 0 ? $total_bs / $tasa_usd : 0, 2) }}
                                                            | Tasa BCV: {{ format_money($tasa_usd, 2) }}
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
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.notas-credito.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri ri-arrow-left-line me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger" @if(count($detalles) === 0) disabled @endif wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="guardar"><i class="ri ri-save-line me-1"></i>Emitir Nota de Crédito</span>
                                <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm me-1"></span>Procesando...</span>
                            </button>
                        </div>
                    </form>
                    @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
