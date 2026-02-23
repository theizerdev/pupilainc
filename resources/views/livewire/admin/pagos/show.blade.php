<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Detalle del Pago #{{ $pago->numero_completo }}</h6>
                            <small class="text-muted">{{ $pago->fecha->format('d/m/Y') }}</small>
                        </div>
                        <div class="btn-group">
                            @if($pago->tipo_pago === 'factura' && $pago->estado === 'aprobado')
                            <a href="{{ route('admin.notas-credito.create') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-file-invoice"></i> Crear Nota de Crédito
                            </a>
                            <a href="{{ route('admin.notas-debito.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-invoice"></i> Crear Nota de Débito
                            </a>
                            @endif
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información del Cliente -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Información del Cliente</h6>
                            @if($pago->clienteFiscal)
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Razón Social:</td>
                                        <td>{{ $pago->clienteFiscal->razon_social }}</td>
                                    </tr>
                                    @if($pago->clienteFiscal->nombre_comercial)
                                    <tr>
                                        <td class="fw-bold">Nombre Comercial:</td>
                                        <td>{{ $pago->clienteFiscal->nombre_comercial }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="fw-bold">RIF/CI:</td>
                                        <td>{{ $pago->clienteFiscal->documento_completo }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Dirección:</td>
                                        <td>{{ $pago->clienteFiscal->direccion_fiscal }}</td>
                                    </tr>
                                    @if($pago->clienteFiscal->telefono)
                                    <tr>
                                        <td class="fw-bold">Teléfono:</td>
                                        <td>{{ $pago->clienteFiscal->telefono }}</td>
                                    </tr>
                                    @endif
                                </table>
                            @elseif($pago->consulta)
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Paciente:</td>
                                        <td>{{ $pago->consulta->paciente->nombre_completo }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Documento:</td>
                                        <td>{{ $pago->consulta->paciente->numero_documento }}</td>
                                    </tr>
                                </table>
                            @endif
                        </div>

                        <!-- Información del Pago -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Información del Pago</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold" width="40%">Tipo:</td>
                                    <td><span class="badge bg-info">{{ strtoupper($pago->tipo_pago) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Método de Pago:</td>
                                    <td>{{ str_replace('_', ' ', ucwords($pago->metodo_pago)) }}</td>
                                </tr>
                                @if($pago->referencia)
                                <tr>
                                    <td class="fw-bold">Referencia:</td>
                                    <td>{{ $pago->referencia }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Estado:</td>
                                    <td>
                                        @if($pago->estado === 'aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                        @elseif($pago->estado === 'pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @else
                                        <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Procesado por:</td>
                                    <td>{{ $pago->user->name }}</td>
                                </tr>
                                @if($pago->es_factura_fiscal)
                                <tr>
                                    <td class="fw-bold">Factura Fiscal:</td>
                                    <td><span class="badge bg-primary">SÍ</span></td>
                                </tr>
                                @if($pago->numero_control_fiscal)
                                <tr>
                                    <td class="fw-bold">N° Control:</td>
                                    <td><span class="text-primary fw-bold">{{ $pago->numero_control_fiscal }}</span></td>
                                </tr>
                                @endif
                                @if($pago->seniat_tipo_documento)
                                <tr>
                                    <td class="fw-bold">Tipo Doc. SENIAT:</td>
                                    <td>{{ $pago->seniat_tipo_documento }} - {{ $pago->tipo_pago === 'factura' ? 'Factura' : ($pago->tipo_pago === 'nota_credito' ? 'Nota de Crédito' : 'Nota de Débito') }}</td>
                                </tr>
                                @endif
                                @if($pago->condicion_pago)
                                <tr>
                                    <td class="fw-bold">Condición:</td>
                                    <td>{{ ucfirst($pago->condicion_pago) }}</td>
                                </tr>
                                @endif
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Detalles del Pago -->
                    <h6 class="text-primary mb-3">Detalles <small class="text-muted">(Montos en Bs.)</small></h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm align-items-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">P/U (USD)</th>
                                    <th class="text-end">P/U (Bs.)</th>
                                    <th class="text-end">Subtotal Bs.</th>
                                    <th class="text-center">IVA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pago->detalles as $index => $detalle)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $detalle->descripcion }}
                                        @if($detalle->baremo)
                                        <br><small class="text-muted">Código: {{ $detalle->baremo->codigo }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $detalle->cantidad }}</td>
                                    <td class="text-end text-muted"><small>${{ number_format($detalle->precio_unitario / ($pago->tasa_cambio_usd ?: 1), 2) }}</small></td>
                                    <td class="text-end">Bs. {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Bs. {{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($detalle->exento_iva)
                                        <span class="badge badge-sm bg-warning">Exento</span>
                                        @elseif($detalle->aplica_iva)
                                        <span class="badge badge-sm bg-success">{{ $detalle->iva_alicuota ?? 16 }}%</span>
                                        @else
                                        <span class="badge badge-sm bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="row">
                        <div class="col-md-8">
                            @if($pago->observaciones)
                            <div class="alert alert-info">
                                <strong>Observaciones:</strong><br>
                                {{ $pago->observaciones }}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-end fw-bold">Bs. {{ number_format($pago->subtotal_bs ?: ($pago->subtotal * $pago->tasa_cambio_usd), 2, ',', '.') }}</td>
                                        </tr>
                                        @if($pago->descuento > 0)
                                        <tr>
                                            <td>Descuento:</td>
                                            <td class="text-end text-danger">-Bs. {{ number_format($pago->descuento * $pago->tasa_cambio_usd, 2, ',', '.') }}</td>
                                        </tr>
                                        @endif
                                        @if($pago->es_factura_fiscal)
                                            @if($pago->base_imponible > 0)
                                            <tr>
                                                <td>Base Imponible:</td>
                                                <td class="text-end">Bs. {{ number_format($pago->base_imponible, 2, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                            @if($pago->iva_monto > 0)
                                            <tr>
                                                <td>IVA (16%):</td>
                                                <td class="text-end">Bs. {{ number_format($pago->iva_monto, 2, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                            @if($pago->iva_monto_reducida > 0)
                                            <tr>
                                                <td>IVA Reducida (8%):</td>
                                                <td class="text-end">Bs. {{ number_format($pago->iva_monto_reducida, 2, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                            @if($pago->igtf_monto > 0)
                                            <tr>
                                                <td>IGTF (3%):</td>
                                                <td class="text-end">Bs. {{ number_format($pago->igtf_monto, 2, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                        @endif
                                        <tr class="table-success">
                                            <td class="fw-bold fs-6">TOTAL Bs:</td>
                                            <td class="text-end fw-bold fs-6">Bs. {{ number_format($pago->total_bs, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end">
                                                <small class="text-muted">Equiv. USD: ${{ number_format($pago->total_usd, 2) }} | Tasa: Bs. {{ number_format($pago->tasa_cambio_usd, 2, ',', '.') }}</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
