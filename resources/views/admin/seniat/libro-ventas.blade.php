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
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>N° Documento</th>
                                    <th>N° Control</th>
                                    <th>RIF Cliente</th>
                                    <th>Razón Social</th>
                                    <th class="text-end">Base Imponible</th>
                                    <th class="text-end">Monto Exento</th>
                                    <th class="text-end">IVA</th>
                                    <th class="text-end">IGTF</th>
                                    <th class="text-end">Total</th>
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
                                    <td>{{ $doc->numero_completo }}</td>
                                    <td>{{ $doc->numero_control_fiscal ?? '-' }}</td>
                                    <td>{{ $doc->clienteFiscal ? $doc->clienteFiscal->documento_completo : '-' }}</td>
                                    <td>{{ $doc->clienteFiscal->razon_social ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($doc->base_imponible ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($doc->monto_exento ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($doc->iva_monto ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($doc->igtf_monto ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($doc->total_con_impuestos ?? 0, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        No se encontraron documentos fiscales en el período seleccionado.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($documentos->count() > 0)
                            <tfoot>
                                <tr class="fw-bold table-light">
                                    <td colspan="7" class="text-end">TOTALES:</td>
                                    <td class="text-end">{{ number_format($totales['base_imponible'], 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totales['monto_exento'], 2, ',', '.') }}</td>
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
