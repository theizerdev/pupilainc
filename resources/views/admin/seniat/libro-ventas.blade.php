@extends('components.layouts.balance')

@section('title', 'Libro de Ventas - SENIAT')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-book"></i> Libro de Ventas - SENIAT</h6>
                        <a href="{{ route('admin.seniat.libro-ventas.excel', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </a>
                        <a href="{{ route('admin.seniat.libro-ventas.export-txt', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-export"></i> Exportar TXT
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('admin.seniat.libro-ventas') }}" class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Desde</label>
                            <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </form>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo Doc.</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">N° Control</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nro. Factura</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Doc. Afectado</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">RIF / C.I.</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Razón Social / Nombre</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Base Imponible</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Ventas Exentas</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Alíc. %</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">IVA</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">IGTF</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentos as $index => $doc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $doc->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        @if($doc->tipo_pago === 'factura')
                                            <span class="badge badge-sm bg-primary">01 - Factura</span>
                                        @elseif($doc->tipo_pago === 'nota_debito')
                                            <span class="badge badge-sm bg-success">02 - N. Débito</span>
                                        @elseif($doc->tipo_pago === 'nota_credito')
                                            <span class="badge badge-sm bg-danger">03 - N. Crédito</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->numero_control_fiscal ?? '-' }}</td>
                                    <td>{{ str_pad($doc->numero, 8, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        @if(($doc->tipo_pago === 'nota_credito' || $doc->tipo_pago === 'nota_debito') && $doc->pagoOrigen)
                                            <small class="text-muted">{{ $doc->pagoOrigen->numero_completo }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->clienteFiscal)
                                            {{ $doc->clienteFiscal->documento_completo }}
                                        @elseif($doc->consulta && $doc->consulta->paciente)
                                            {{ $doc->consulta->paciente->documento_identidad }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->clienteFiscal)
                                            {{ $doc->clienteFiscal->razon_social }}
                                        @elseif($doc->consulta && $doc->consulta->paciente)
                                            {{ $doc->consulta->paciente->nombre_completo }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($doc->base_imponible ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($doc->monto_exento ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ number_format($doc->iva_porcentaje ?? 16, 0) }}%</td>
                                    <td class="text-end">{{ number_format($doc->iva_monto ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($doc->igtf_monto ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($doc->total_con_impuestos ?? 0, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-4">
                                        No se encontraron documentos fiscales en el período seleccionado.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($documentos->count() > 0)
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td colspan="8" class="text-end">TOTALES:</td>
                                    <td class="text-end">{{ number_format($totales['base_imponible'], 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totales['monto_exento'], 2, ',', '.') }}</td>
                                    <td></td>
                                    <td class="text-end">{{ number_format($totales['iva_monto'], 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totales['igtf_monto'], 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totales['total'], 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
