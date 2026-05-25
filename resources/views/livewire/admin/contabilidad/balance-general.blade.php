<div>
    @section('title', 'Balance General')

    @push('styles')
    <style>
        .bg-hero { background: linear-gradient(135deg, #3B82F6 0%, #6366F1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .bg-hero h2 { color:#fff; margin:0; }
        .bg-hero p { opacity:.9; margin:0; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Balance General</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="bg-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-bar-chart-box-line me-2"></i>Balance General</h2>
                <p class="mt-1">Posición financiera de la empresa a una fecha determinada</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Fecha de corte</label>
                        <input type="date" wire:model.live="fecha_corte" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3" x-show="$wire.comparativo">
                        <label class="form-label small fw-semibold">Fecha comparativa</label>
                        <input type="date" wire:model.live="fecha_comparativa" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold d-block">Opciones</label>
                        <div class="d-flex gap-3 align-items-center flex-wrap">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" wire:model.live="mostrar_cuentas_cero" id="mostrarCero">
                                <label class="form-check-label small" for="mostrarCero">Cuentas en cero</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" wire:model.live="agrupar_por_categoria" id="agruparCat">
                                <label class="form-check-label small" for="agruparCat">Agrupar por categoría</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" wire:model.live="comparativo" id="comparativo">
                                <label class="form-check-label small" for="comparativo">Análisis comparativo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary btn-sm">
                        <i class="ri ri-refresh-line me-1"></i>Limpiar
                    </button>
                    <button type="button" wire:click="exportarPdf" class="btn btn-outline-danger btn-sm">
                        <i class="ri ri-file-pdf-2-line me-1"></i>PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Ratios Financieros --}}
        @if(!$comparativo)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-drop-line text-primary" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($ratios['liquidez_corriente'], 2) }}</div>
                            <small class="text-muted">Liquidez Corriente</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-percent-line text-warning" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($ratios['endeudamiento'], 1) }}%</div>
                            <small class="text-muted">Endeudamiento</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-shield-check-line text-success" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($ratios['autonomia_financiera'], 1) }}%</div>
                            <small class="text-muted">Autonomía Financiera</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-trending-up-line text-info" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($ratios['rentabilidad_patrimonio'], 1) }}%</div>
                            <small class="text-muted">ROE</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Balance General -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            {{-- ACTIVOS --}}
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary fw-bold mb-0">ACTIVOS</h6>
                                    @if($comparativo)
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($fecha_comparativa)->format('d/m/Y') }} vs {{ \Carbon\Carbon::parse($fecha_corte)->format('d/m/Y') }}</small>
                                    @endif
                                </div>

                                @if($agrupar_por_categoria)
                                    @php
                                        $activosAgrupados = $activos->groupBy('categoria');
                                    @endphp
                                    @foreach($activosAgrupados as $categoria => $cuentasCategoria)
                                        <div class="mb-3">
                                            <h6 class="text-muted fw-semibold border-bottom pb-1">{{ $categoria }}</h6>
                                            <table class="table table-sm table-borderless">
                                                <tbody>
                                                    @foreach($cuentasCategoria as $cuenta)
                                                        <tr>
                                                            <td class="ps-3">{{ $cuenta->codigo }}</td>
                                                            <td class="ps-2">{{ $cuenta->nombre }}</td>
                                                            @if($comparativo)
                                                                <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                                <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                                <td class="text-end">
                                                                    @if($cuenta->variacion != 0)
                                                                        <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'success' : 'danger' }}">
                                                                            {{ $cuenta->variacion > 0 ? '+' : '' }}{{ number_format($cuenta->variacion_porcentaje, 1) }}%
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            @else
                                                                <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                @else
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($activos as $cuenta)
                                                <tr>
                                                    <td>{{ $cuenta->codigo }}</td>
                                                    <td>{{ $cuenta->nombre }}</td>
                                                    @if($comparativo)
                                                        <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                        <td class="text-end">
                                                            @if($cuenta->variacion != 0)
                                                                <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'success' : 'danger' }}">
                                                                    {{ $cuenta->variacion > 0 ? '+' : '' }}{{ number_format($cuenta->variacion_porcentaje, 1) }}%
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                <div class="border-top pt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-primary">TOTAL ACTIVOS</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($totales['activos_comparativo']) }}</div>
                                                <div class="fw-bold text-primary">{{ format_money($totales['activos']) }}</div>
                                            </div>
                                        @else
                                            <strong class="text-primary">{{ format_money($totales['activos']) }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- PASIVOS Y PATRIMONIO --}}
                            <div class="col-md-6">
                                <h6 class="text-danger fw-bold mb-3">PASIVOS</h6>

                                @if($agrupar_por_categoria)
                                    @php
                                        $pasivosAgrupados = $pasivos->groupBy('categoria');
                                    @endphp
                                    @foreach($pasivosAgrupados as $categoria => $cuentasCategoria)
                                        <div class="mb-3">
                                            <h6 class="text-muted fw-semibold border-bottom pb-1">{{ $categoria }}</h6>
                                            <table class="table table-sm table-borderless">
                                                <tbody>
                                                    @foreach($cuentasCategoria as $cuenta)
                                                        <tr>
                                                            <td class="ps-3">{{ $cuenta->codigo }}</td>
                                                            <td class="ps-2">{{ $cuenta->nombre }}</td>
                                                            @if($comparativo)
                                                                <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                                <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                                <td class="text-end">
                                                                    @if($cuenta->variacion != 0)
                                                                        <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'success' : 'danger' }}">
                                                                            {{ $cuenta->variacion > 0 ? '+' : '' }}{{ number_format($cuenta->variacion_porcentaje, 1) }}%
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            @else
                                                                <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                @else
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($pasivos as $cuenta)
                                                <tr>
                                                    <td>{{ $cuenta->codigo }}</td>
                                                    <td>{{ $cuenta->nombre }}</td>
                                                    @if($comparativo)
                                                        <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                        <td class="text-end">
                                                            @if($cuenta->variacion != 0)
                                                                <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'success' : 'danger' }}">
                                                                    {{ $cuenta->variacion > 0 ? '+' : '' }}{{ number_format($cuenta->variacion_porcentaje, 1) }}%
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                <div class="border-top pt-2 mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-danger">TOTAL PASIVOS</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($totales['pasivos_comparativo']) }}</div>
                                                <div class="fw-bold text-danger">{{ format_money($totales['pasivos']) }}</div>
                                            </div>
                                        @else
                                            <strong class="text-danger">{{ format_money($totales['pasivos']) }}</strong>
                                        @endif
                                    </div>
                                </div>

                                <h6 class="text-info fw-bold mb-3">PATRIMONIO</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($patrimonio as $cuenta)
                                            <tr>
                                                <td>{{ $cuenta->codigo }}</td>
                                                <td>{{ $cuenta->nombre }}</td>
                                                @if($comparativo)
                                                    <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                    <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                    <td class="text-end">
                                                        @if($cuenta->variacion != 0)
                                                            <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'success' : 'danger' }}">
                                                                {{ $cuenta->variacion > 0 ? '+' : '' }}{{ number_format($cuenta->variacion_porcentaje, 1) }}%
                                                            </span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        <tr class="fst-italic">
                                            <td></td>
                                            <td>Resultado del Ejercicio</td>
                                            @if($comparativo)
                                                <td class="text-end text-muted">{{ format_money($this->getResultadoComparativo()) }}</td>
                                                <td class="text-end fw-semibold">{{ format_money($resultado_ejercicio) }}</td>
                                                <td class="text-end"></td>
                                            @else
                                                <td class="text-end fw-semibold">{{ format_money($resultado_ejercicio) }}</td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="border-top pt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-info">TOTAL PATRIMONIO</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($totales['patrimonio_comparativo']) }}</div>
                                                <div class="fw-bold text-info">{{ format_money($totales['patrimonio']) }}</div>
                                            </div>
                                        @else
                                            <strong class="text-info">{{ format_money($totales['patrimonio']) }}</strong>
                                        @endif
                                    </div>
                                </div>

                                <div class="border-top pt-2 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>PASIVO + PATRIMONIO</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($totales['pasivos_comparativo'] + $totales['patrimonio_comparativo']) }}</div>
                                                <div class="fw-bold">{{ format_money($totales['pasivos'] + $totales['patrimonio']) }}</div>
                                            </div>
                                        @else
                                            <strong>{{ format_money($totales['pasivos'] + $totales['patrimonio']) }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            @if($totales['ecuacion_balanceada'])
                                <span class="badge bg-success fs-6">
                                    <i class="ri-check-line me-1"></i>
                                    Ecuación Patrimonial Verificada: A = P + Pt
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="ri-error-warning-line me-1"></i>
                                    Descuadre: {{ format_money($totales['descuadre']) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
