<div>
    @section('title', 'Detalle de Caja')

    @push('styles')
    <style>
        .caja-detail-hero { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .caja-detail-hero h2 { color:#fff; margin:0; }
        .caja-detail-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:#6b7280; margin-top:.2rem; }

        .filter-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:1rem; background:#fff; }

        .table thead th { font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; font-weight:600; border-bottom-width:1px; }
        .table tbody td { vertical-align:middle; }
    </style>
    @endpush

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero Section --}}
    <div class="caja-detail-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-safe-line me-2"></i>Detalle de Caja - {{ format_date($caja->fecha) }}</h2>
            <p class="mt-1">
                Estado:
                @if($caja->estado === 'abierta')
                    <span class="badge bg-light text-success"><i class="ri ri-checkbox-circle-line me-1"></i>Abierta</span>
                @else
                    <span class="badge bg-light text-secondary"><i class="ri ri-lock-line me-1"></i>Cerrada</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm" wire:click="exportarExcel">
                <i class="ri ri-file-excel-2-line me-1"></i> Reporte Conglomerado
            </button>
            @if($caja->estado === 'abierta')
                @can('edit cajas')
                <button type="button" class="btn btn-warning btn-sm" wire:click="abrirModalCerrar">
                    <i class="ri ri-lock-line me-1"></i> Cerrar Caja
                </button>
                @endcan
            @else
                @can('edit cajas')
                <button type="button" class="btn btn-info btn-sm" wire:click="recalcularMontos">
                    <i class="ri ri-calculator-line me-1"></i> Recalcular Montos
                </button>
                @endcan
            @endif
            <a href="{{ route('admin.cajas.index') }}" class="btn btn-light btn-sm">
                <i class="ri ri-arrow-left-line me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-money-dollar-circle-line"></i></div>
                <div>
                    <div class="stat-label">Monto Inicial</div>
                    @php
                        $esVenezuela = auth()->user()->empresa->pais->nombre == 'Venezuela';
                        $montoInicial = $esVenezuela ? $caja->monto_inicial_bs : $caja->monto_inicial;
                        $simboloInicial = $esVenezuela ? 'Bs' : '$';
                    @endphp
                    <div class="stat-value">{{ money($montoInicial, 2) }}</div>
                    @if($esVenezuela)
                    <small class="text-muted">{{ money($caja->monto_inicial, 2) }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-arrow-up-circle-line"></i></div>
                <div>
                    <div class="stat-label">Total Ingresos</div>
                    @php
                        $totalIngresos = $esVenezuela ? $caja->total_ingresos_bs : $caja->total_ingresos;
                        $simboloIngresos = $esVenezuela ? 'Bs' : '$';
                    @endphp
                    <div class="stat-value">{{ money($totalIngresos, 2) }}</div>
                    @if($esVenezuela)
                    <small class="text-muted">{{ money($caja->total_ingresos, 2) }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-safe-line"></i></div>
                <div>
                    <div class="stat-label">Monto Final</div>
                    @php
                        $montoFinal = $esVenezuela ? $caja->monto_final_ajustado_bs : $caja->monto_final_ajustado;
                        $simboloFinal = $esVenezuela ? 'Bs' : '$';
                    @endphp
                    <div class="stat-value">{{ money($montoFinal, 2) }}</div>
                    @if($esVenezuela)
                    <small class="text-muted">{{ money($caja->monto_final_ajustado, 2) }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-file-list-3-line"></i></div>
                <div>
                    <div class="stat-label">Total Pagos</div>
                    <div class="stat-value">{{ $caja->pagos->where('estado', 'aprobado')->filter(fn($p) => !$p->notasCredito()->where('estado', 'aprobado')->exists())->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Resumen por Método de Pago --}}
        <div class="col-md-6 mb-4">
            <div class="filter-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="ri ri-bank-card-line me-2"></i>Resumen por Método de Pago</h6>
                    <button type="button" class="btn btn-sm btn-success" wire:click="exportarResumenPorMetodoExcel">
                        <i class="ri ri-file-excel-2-line me-1"></i> Exportar Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->resumenPorMetodo as $metodo)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $iconClass = \App\Helpers\MetodoPagoHelper::getIcono($metodo->metodo_pago);
                                                $nombreMostrar = \App\Helpers\MetodoPagoHelper::getNombreAmigable($metodo->metodo_pago);
                                            @endphp
                                            <i class="{{ $iconClass }} me-2"></i>
                                            {{ $nombreMostrar }}
                                        </div>
                                    </td>
                                    <td class="text-end">{{ $metodo->cantidad }}</td>
                                    <td class="text-end fw-semibold">
                                        @php
                                            $totalMetodo = $esVenezuela ? ($metodo->total_bs ?? 0) : $metodo->total_usd;
                                        @endphp
                                        {{ money($totalMetodo, 2) }}
                                        @if($esVenezuela && isset($metodo->total_usd))
                                        <br><small class="text-muted">{{ money($metodo->total_usd, 2) }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No hay pagos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Resumen por Concepto --}}
        <div class="col-md-6 mb-4">
            <div class="filter-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="ri ri-file-text-line me-2"></i>Resumen por Concepto</h6>
                    <button type="button" class="btn btn-sm btn-success" wire:click="exportarResumenConceptosExcel">
                        <i class="ri ri-file-excel-2-line me-1"></i> Exportar
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->resumenPorConcepto as $concepto)
                                <tr>
                                    <td>{{ $concepto['concepto'] }}</td>
                                    <td class="text-end">{{ $concepto['cantidad'] }}</td>
                                    <td class="text-end fw-semibold">
                                        @php
                                            $totalConcepto = $esVenezuela ? ($concepto['total_bs'] ?? 0) : $concepto['total'];
                                        @endphp
                                        {{ money($totalConcepto, 2) }}
                                        @if($esVenezuela && isset($concepto['total']))
                                        <br><small class="text-muted">{{ money($concepto['total'], 2) }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No hay conceptos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle de Pagos --}}
    <div class="row">
        <div class="col-12">
            <div class="filter-card">
                <h6 class="fw-semibold mb-3"><i class="ri ri-file-list-3-line me-2"></i>Detalle de Pagos</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N° Factura</th>
                                <th>Control Fiscal</th>
                                <th>Paciente</th>
                                <th>Método</th>
                                <th class="text-end">Total ({{ auth()->user()->empresa->pais->moneda_principal ?? 'USD' }})</th>
                                <th>Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caja->pagos as $pago)
                                @php
                                    $tieneNotaCredito = $pago->notasCredito()->where('estado', 'aprobado')->exists();
                                    $esVzla = auth()->user()->empresa->pais->nombre == 'Venezuela';
                                    $totalPrincipal = $esVzla ? $pago->total_bs : $pago->total_usd;
                                    $totalSecundario = $esVzla ? $pago->total_usd : $pago->total_bs;
                                    $simboloPrincipal = $esVzla ? 'Bs' : '$';
                                    $simboloSecundario = $esVzla ? '$' : 'Bs';
                                @endphp
                                <tr class="{{ $tieneNotaCredito ? 'text-danger' : '' }}">
                                    <td>
                                        <div class="fw-medium {{ $tieneNotaCredito ? 'text-danger' : 'text-primary' }}">
                                            {{ $pago->numero_completo }}
                                            @if($tieneNotaCredito)
                                                <span class="badge bg-danger ms-1">ANULADA</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($pago->numero_control_fiscal)
                                            <span class="badge bg-info">{{ $pago->numero_control_fiscal }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pago->consulta && $pago->consulta->paciente)
                                            <div>{{ $pago->consulta->paciente->nombre_completo }}</div>
                                            <small class="text-muted">{{ $pago->consulta->paciente->documento_identidad }}</small>
                                        @elseif($pago->clienteFiscal)
                                            <div>{{ $pago->clienteFiscal->razon_social }}</div>
                                            <small class="text-muted">{{ $pago->clienteFiscal->documento_completo }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $iconClass = \App\Helpers\MetodoPagoHelper::getIcono($pago->metodo_pago);
                                            $nombreMetodo = \App\Helpers\MetodoPagoHelper::getNombreAmigable($pago->metodo_pago);
                                        @endphp
                                        <i class="{{ $iconClass }} me-1"></i>
                                        {{ $nombreMetodo }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        @if($tieneNotaCredito)
                                            <del>{{ money($totalPrincipal, 2) }}</del>
                                        @else
                                            {{ money($totalPrincipal, 2) }}
                                        @endif
                                        @if($esVzla && $totalSecundario > 0)
                                        <br><small class="text-muted">{{ money($totalSecundario, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $pago->created_at->format('H:i') }}</td>
                                    <td>
                                        @if($tieneNotaCredito)
                                            <span class="badge bg-danger">Anulada</span>
                                        @elseif($pago->estado === 'aprobado')
                                            <span class="badge bg-success">Aprobado</span>
                                        @elseif($pago->estado === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay pagos registrados en esta caja</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cerrar Caja -->
    @if($showCerrarModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cerrar Caja</h5>
                    <button type="button" class="btn-close" wire:click="$set('showCerrarModal', false)"></button>
                </div>
                <form wire:submit="cerrarCaja">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ri ri-alert-line me-2"></i>
                            <strong>¿Estás seguro de cerrar la caja?</strong><br>
                            Una vez cerrada no se podrán agregar más pagos a esta caja.
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Monto Final Calculado:</label>
                                @php
                                    $montoFinalCierre = $esVenezuela ? $caja->monto_final_ajustado_bs : $caja->monto_final_ajustado;
                                @endphp
                                <div class="fw-bold text-success">{{ money($montoFinalCierre, 2) }}</div>
                                @if($esVenezuela)
                                <small class="text-muted">{{ money($caja->monto_final_ajustado, 2) }}</small>
                                @endif
                            </div>
                            <div class="col-6">
                                <label class="form-label">Total Ingresos:</label>
                                @php
                                    $totalIngresosCierre = $esVenezuela ? $caja->total_ingresos_bs : $caja->total_ingresos;
                                @endphp
                                <div class="fw-bold text-primary">{{ money($totalIngresosCierre, 2) }}</div>
                                @if($esVenezuela)
                                <small class="text-muted">{{ money($caja->total_ingresos, 2) }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones de Cierre</label>
                            <textarea class="form-control" wire:model="observaciones_cierre" rows="3"
                                      placeholder="Observaciones sobre el cierre de caja..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCerrarModal', false)">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ri ri-lock-line"></i> Cerrar Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Recalcular Montos -->
    @if($showRecalcularModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recalcular Montos</h5>
                    <button type="button" class="btn-close" wire:click="$set('showRecalcularModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri ri-calculator-line me-2"></i>
                        <strong>¿Deseas recalcular los montos de esta caja?</strong><br>
                        Esto actualizará el monto final de cierre basándose en los pagos actuales.
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Monto Actual:</label>
                            @php
                                $montoActualRecalc = $esVenezuela ? $caja->monto_final_ajustado_bs : $caja->monto_final_ajustado;
                            @endphp
                            <div class="fw-bold">{{ money($montoActualRecalc, 2) }}</div>
                            @if($esVenezuela)
                            <small class="text-muted">{{ money($caja->monto_final_ajustado, 2) }}</small>
                            @endif
                        </div>
                        <div class="col-6">
                            <label class="form-label">Total Ingresos:</label>
                            @php
                                $totalIngresosRecalc = $esVenezuela ? $caja->total_ingresos_bs : $caja->total_ingresos;
                            @endphp
                            <div class="fw-bold">{{ money($totalIngresosRecalc, 2) }}</div>
                            @if($esVenezuela)
                            <small class="text-muted">{{ money($caja->total_ingresos, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRecalcularModal', false)">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-info" wire:click="confirmarRecalcular">
                        <i class="ri ri-calculator-line"></i> Recalcular Montos
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
