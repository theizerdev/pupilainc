<div>
    @section('title', 'Estado de Resultados')

    @push('styles')
    <style>
        .er-hero { background: linear-gradient(135deg, #0891b2 0%, #0ea5e9 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .er-hero h2 { color:#fff; margin:0; }
        .er-hero p { opacity:.9; margin:0; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Estado de Resultados</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="er-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-line-chart-line me-2"></i>Estado de Resultados</h2>
                <p class="mt-1">Rendimiento financiero del período</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" wire:model.live="fecha_desde" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" wire:model.live="fecha_hasta" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2" x-show="$wire.comparativo">
                        <label class="form-label small fw-semibold">Período comparativo</label>
                        <select wire:model.live="periodo_comparativo" class="form-select form-select-sm">
                            <option value="anio_anterior">Año Anterior</option>
                            <option value="periodo_anterior">Período Anterior</option>
                            <option value="mes_anterior">Mes Anterior</option>
                        </select>
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

        {{-- Indicadores --}}
        @if(!$comparativo)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-percent-line text-success" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($indicadores['margen_bruto'], 1) }}%</div>
                            <small class="text-muted">Margen Bruto</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-bar-chart-line text-primary" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ number_format($indicadores['margen_operacional'], 1) }}%</div>
                            <small class="text-muted">Margen Operacional</small>
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
                            <div class="fw-semibold">{{ number_format($indicadores['margen_neto'], 1) }}%</div>
                            <small class="text-muted">Margen Neto</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-money-dollar-circle-line text-warning" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ format_money($resultados['ingresos']) }}</div>
                            <small class="text-muted">Ingresos Totales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Estado de Resultados -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                @if($comparativo)
                                    <div class="text-center mb-3">
                                        <small class="text-muted">
                                            Comparativo: {{ \Carbon\Carbon::parse($fecha_desde_comp)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta_comp)->format('d/m/Y') }} 
                                            vs 
                                            {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                @endif

                                {{-- INGRESOS --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-success fw-bold mb-0">INGRESOS OPERACIONALES</h6>
                                    @if($comparativo && $indicadores['crecimiento_ingresos'] != 0)
                                        <span class="badge bg-label-{{ $indicadores['crecimiento_ingresos'] > 0 ? 'success' : 'danger' }}">
                                            {{ $indicadores['crecimiento_ingresos'] > 0 ? '+' : '' }}{{ number_format($indicadores['crecimiento_ingresos'], 1) }}%
                                        </span>
                                    @endif
                                </div>
                                
                                @if($agrupar_por_categoria)
                                    @php
                                        $ingresosAgrupados = $ingresos->groupBy('categoria');
                                    @endphp
                                    @foreach($ingresosAgrupados as $categoria => $cuentasCategoria)
                                        <div class="mb-2">
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
                                            @foreach($ingresos as $cuenta)
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
                                
                                <div class="border-top pt-2 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-success">Total Ingresos</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($resultados['ingresos_comparativo']) }}</div>
                                                <div class="fw-bold text-success">{{ format_money($resultados['ingresos']) }}</div>
                                            </div>
                                        @else
                                            <strong class="text-success">{{ format_money($resultados['ingresos']) }}</strong>
                                        @endif
                                    </div>
                                </div>

                                {{-- COSTOS --}}
                                @if($costos->count() > 0)
                                    <h6 class="text-warning fw-bold mb-2">COSTOS DE SERVICIO</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($costos as $cuenta)
                                                <tr>
                                                    <td>{{ $cuenta->codigo }}</td>
                                                    <td>{{ $cuenta->nombre }}</td>
                                                    @if($comparativo)
                                                        <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                        <td class="text-end">
                                                            @if($cuenta->variacion != 0)
                                                                <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'danger' : 'success' }}">
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
                                    
                                    <div class="border-top pt-2 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="text-warning">Total Costos</strong>
                                            @if($comparativo)
                                                <div class="text-end">
                                                    <div class="text-muted small">{{ format_money($resultados['costos_comparativo']) }}</div>
                                                    <div class="fw-bold text-warning">{{ format_money($resultados['costos']) }}</div>
                                                </div>
                                            @else
                                                <strong class="text-warning">{{ format_money($resultados['costos']) }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- UTILIDAD BRUTA --}}
                                <div class="bg-light p-2 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>UTILIDAD BRUTA</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($resultados['utilidad_bruta_comparativo']) }}</div>
                                                <div class="fw-bold">{{ format_money($resultados['utilidad_bruta']) }}</div>
                                            </div>
                                        @else
                                            <strong>{{ format_money($resultados['utilidad_bruta']) }}</strong>
                                        @endif
                                    </div>
                                </div>

                                {{-- GASTOS --}}
                                <h6 class="text-danger fw-bold mb-2">GASTOS OPERACIONALES</h6>
                                
                                @if($agrupar_por_categoria)
                                    @php
                                        $egresosAgrupados = $egresos->groupBy('categoria');
                                    @endphp
                                    @foreach($egresosAgrupados as $categoria => $cuentasCategoria)
                                        <div class="mb-2">
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
                                                                        <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'danger' : 'success' }}">
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
                                            @foreach($egresos as $cuenta)
                                                <tr>
                                                    <td>{{ $cuenta->codigo }}</td>
                                                    <td>{{ $cuenta->nombre }}</td>
                                                    @if($comparativo)
                                                        <td class="text-end text-muted">{{ format_money($cuenta->saldo_comparativo) }}</td>
                                                        <td class="text-end fw-semibold">{{ format_money($cuenta->saldo) }}</td>
                                                        <td class="text-end">
                                                            @if($cuenta->variacion != 0)
                                                                <span class="badge bg-label-{{ $cuenta->variacion > 0 ? 'danger' : 'success' }}">
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
                                
                                <div class="border-top pt-2 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-danger">Total Gastos</strong>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted small">{{ format_money($resultados['egresos_comparativo']) }}</div>
                                                <div class="fw-bold text-danger">{{ format_money($resultados['egresos']) }}</div>
                                            </div>
                                        @else
                                            <strong class="text-danger">{{ format_money($resultados['egresos']) }}</strong>
                                        @endif
                                    </div>
                                </div>

                                {{-- UTILIDAD NETA --}}
                                <div class="bg-{{ $resultados['utilidad_neta'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-{{ $resultados['utilidad_neta'] >= 0 ? 'success' : 'danger' }}">
                                            {{ $resultados['utilidad_neta'] >= 0 ? 'UTILIDAD' : 'PÉRDIDA' }} NETA DEL EJERCICIO
                                        </h5>
                                        @if($comparativo)
                                            <div class="text-end">
                                                <div class="text-muted">{{ format_money($resultados['utilidad_neta_comparativo']) }}</div>
                                                <h5 class="mb-0 text-{{ $resultados['utilidad_neta'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ format_money(abs($resultados['utilidad_neta'])) }}
                                                </h5>
                                            </div>
                                        @else
                                            <h5 class="mb-0 text-{{ $resultados['utilidad_neta'] >= 0 ? 'success' : 'danger' }}">
                                                {{ format_money(abs($resultados['utilidad_neta'])) }}
                                            </h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
