<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ri-file-list-3-line me-2"></i>Balance de Comprobación</h5>
                        <a href="{{ route('admin.contabilidad.balance-comprobacion.excel', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" class="btn btn-sm btn-outline-success">
                            <i class="ri-file-excel-2-line me-1"></i>Excel
                        </a>
                        <a href="{{ route('admin.contabilidad.balance-comprobacion.pdf', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3 g-3">
                            <div class="col-md-3">
                                <label class="form-label">Desde</label>
                                <input type="date" wire:model.live="fecha_desde" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hasta</label>
                                <input type="date" wire:model.live="fecha_hasta" class="form-control">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th rowspan="2" class="align-middle">Código</th>
                                        <th rowspan="2" class="align-middle">Cuenta</th>
                                        <th colspan="2" class="text-center">Movimientos del período</th>
                                        <th colspan="2" class="text-center">Saldos finales</th>
                                    </tr>
                                    <tr>
                                        <th class="text-end">Debe</th>
                                        <th class="text-end">Haber</th>
                                        <th class="text-end">Deudor</th>
                                        <th class="text-end">Acreedor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    // Obtener separadores según configuración regional
                                    $decimalSep = $regionalConfig['decimal_separator'] ?? ',';
                                    $thousandsSep = $regionalConfig['thousands_separator'] ?? '.';
                                @endphp
                                    @forelse($cuentas as $cuenta)
                                        <tr>
                                            <td><strong>{{ $cuenta->codigo }}</strong></td>
                                            <td>{{ $cuenta->nombre }}</td>
                                            <td class="text-end">{{ format_money($cuenta->debe_periodo, 2, $decimalSep, $thousandsSep) }}</td>
                                            <td class="text-end">{{ format_money($cuenta->haber_periodo, 2, $decimalSep, $thousandsSep) }}</td>
                                            <td class="text-end">
                                                @if($cuenta->saldo_deudor > 0)
                                                    {{ format_money($cuenta->saldo_deudor, 2, $decimalSep, $thousandsSep) }}
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($cuenta->saldo_acreedor > 0)
                                                    {{ format_money($cuenta->saldo_acreedor, 2, $decimalSep, $thousandsSep) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No hay movimientos en el período seleccionado</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($cuentas->count() > 0)
                                    <tfoot class="bg-primary text-white fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end">TOTALES</td>
                                            <td class="text-end">{{ format_money($totales->debe_periodo, 2, $decimalSep, $thousandsSep) }}</td>
                                            <td class="text-end">{{ format_money($totales->haber_periodo, 2, $decimalSep, $thousandsSep) }}</td>
                                            <td class="text-end">{{ format_money($totales->saldo_final_deudor, 2, $decimalSep, $thousandsSep) }}</td>
                                            <td class="text-end">{{ format_money($totales->saldo_final_acreedor, 2, $decimalSep, $thousandsSep) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        @if($cuentas->count() > 0)
                            <div class="mt-3 text-center">
                                @if(round($totales->saldo_final_deudor, 2) === round($totales->saldo_final_acreedor, 2))
                                    <span class="badge bg-success fs-6"><i class="ri-check-line me-1"></i> Balance Cuadrado</span>
                                @else
                                    <span class="badge bg-danger fs-6"><i class="ri-error-warning-line me-1"></i> Descuadre: {{ auth()->user()->empresa->pais->codigo_moneda ?? 'Bs' }} {{ format_money(abs($totales->saldo_final_deudor - $totales->saldo_final_acreedor), 2, $decimalSep, $thousandsSep) }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
