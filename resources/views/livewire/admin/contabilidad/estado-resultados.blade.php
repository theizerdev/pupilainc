<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ri-line-chart-line me-2"></i>Estado de Resultados</h5>
                        <div class="d-flex align-items-center gap-2">
                            <input type="date" wire:model.live="fecha_desde" class="form-control" style="width: 160px;">
                            <span>al</span>
                            <input type="date" wire:model.live="fecha_hasta" class="form-control" style="width: 160px;">
                            <a href="{{ route('admin.contabilidad.estado-resultados.pdf', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="ri-file-pdf-2-line me-1"></i>PDF
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                {{-- INGRESOS --}}
                                <h6 class="text-success fw-bold border-bottom pb-2">INGRESOS OPERACIONALES</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($ingresos as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                <td class="text-end">Bs {{ number_format($cuenta->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-success">
                                            <td colspan="2">Total Ingresos</td>
                                            <td class="text-end">Bs {{ number_format($totalIngresos, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                {{-- COSTOS --}}
                                @if($costos->count() > 0)
                                    <h6 class="text-warning fw-bold border-bottom pb-2 mt-3">COSTOS DE SERVICIO</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($costos as $cuenta)
                                                <tr>
                                                    <td>{{ $cuenta->codigo }}</td>
                                                    <td>{{ $cuenta->nombre }}</td>
                                                    <td class="text-end">Bs {{ number_format($cuenta->saldo, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold table-warning">
                                                <td colspan="2">Total Costos</td>
                                                <td class="text-end">Bs {{ number_format($totalCostos, 2, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endif

                                {{-- UTILIDAD BRUTA --}}
                                <table class="table table-sm">
                                    <tr class="fw-bold table-light">
                                        <td colspan="2">UTILIDAD BRUTA</td>
                                        <td class="text-end">Bs {{ number_format($utilidadBruta, 2, ',', '.') }}</td>
                                    </tr>
                                </table>

                                {{-- GASTOS --}}
                                <h6 class="text-danger fw-bold border-bottom pb-2 mt-3">GASTOS OPERACIONALES</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($egresos as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                <td class="text-end">Bs {{ number_format($cuenta->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-danger">
                                            <td colspan="2">Total Gastos</td>
                                            <td class="text-end">Bs {{ number_format($totalEgresos, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                {{-- UTILIDAD NETA --}}
                                <table class="table mt-3">
                                    <tr class="fw-bold fs-5 {{ $utilidadNeta >= 0 ? 'table-success' : 'table-danger' }}">
                                        <td>{{ $utilidadNeta >= 0 ? 'UTILIDAD' : 'PÉRDIDA' }} NETA DEL EJERCICIO</td>
                                        <td class="text-end">Bs {{ number_format(abs($utilidadNeta), 2, ',', '.') }}</td>
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
