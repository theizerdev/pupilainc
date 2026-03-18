<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ri-bar-chart-box-line me-2"></i>Balance General</h5>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0">Fecha de corte:</label>
                            <input type="date" wire:model.live="fecha_corte" class="form-control" style="width: 180px;">
                            <a href="{{ route('admin.contabilidad.balance-general.pdf', ['fecha' => $fecha_corte]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="ri-file-pdf-2-line me-1"></i>PDF
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- ACTIVOS --}}
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold border-bottom pb-2">ACTIVOS</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($activos as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                <td class="text-end">Bs {{ format_money($cuenta->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-primary">
                                            <td colspan="2">TOTAL ACTIVOS</td>
                                            <td class="text-end">Bs {{ format_money($totalActivos, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- PASIVOS Y PATRIMONIO --}}
                            <div class="col-md-6">
                                <h6 class="text-danger fw-bold border-bottom pb-2">PASIVOS</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($pasivos as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                <td class="text-end">Bs {{ format_money($cuenta->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-danger">
                                            <td colspan="2">TOTAL PASIVOS</td>
                                            <td class="text-end">Bs {{ format_money($totalPasivos, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <h6 class="text-info fw-bold border-bottom pb-2 mt-3">PATRIMONIO</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($patrimonio as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                <td class="text-end">Bs {{ format_money($cuenta->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="fst-italic">
                                            <td></td>
                                            <td>Resultado del Ejercicio</td>
                                            <td class="text-end">Bs {{ format_money($resultadoEjercicio, 2, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-info">
                                            <td colspan="2">TOTAL PATRIMONIO</td>
                                            <td class="text-end">Bs {{ format_money($totalPatrimonio, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <table class="table table-sm mt-2">
                                    <tfoot>
                                        <tr class="fw-bold table-dark">
                                            <td>PASIVO + PATRIMONIO</td>
                                            <td class="text-end">Bs {{ format_money($totalPasivos + $totalPatrimonio, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            @if(round($totalActivos, 2) === round($totalPasivos + $totalPatrimonio, 2))
                                <span class="badge bg-success fs-6"><i class="ri-check-line me-1"></i> Ecuación Patrimonial Verificada: A = P + Pt</span>
                            @else
                                <span class="badge bg-danger fs-6"><i class="ri-error-warning-line me-1"></i> Descuadre: Bs {{ format_money(abs($totalActivos - $totalPasivos - $totalPatrimonio), 2, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
