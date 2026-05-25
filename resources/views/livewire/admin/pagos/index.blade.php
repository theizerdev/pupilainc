<div>
    @section('title', 'Pagos y Facturación')

    @push('styles')
    <style>
        .pagos-hero { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .pagos-hero h2 { color:#fff; margin:0; }
        .pagos-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .pago-row { transition:background .12s; }
        .pago-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Pagos y Facturación</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="pagos-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-bank-card-line me-2"></i>Pagos y Facturación</h2>
                <p class="mt-1">Gestión de pagos, facturas y recibos</p>
            </div>
            <a href="{{ route('admin.pagos.create') }}" class="btn btn-light btn-sm">
                <i class="ri ri-add-line me-1"></i>Nuevo Pago
            </a>
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-file-text-line"></i></div>
                    <div>
                        <div class="stat-label">Total documentos</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Aprobados</div>
                        <div class="stat-value">{{ $stats['aprobados'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-time-line"></i></div>
                    <div>
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-value">{{ $stats['pendientes'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Total {{ auth()->user()->empresa->pais->moneda_principal ?? 'USD' }}</div>
                        @php
                            $esVenezuela = auth()->user()->empresa->pais->nombre == 'Venezuela';
                            $totalMostrar = $esVenezuela ? $stats['total_bs'] : $stats['total_usd'];
                            $simbolo = $esVenezuela ? 'Bs' : '$';
                        @endphp
                        <div class="stat-value">{{ money($totalSumado, 2) }}</div>
                        @if($esVenezuela)
                        <small class="text-muted">{{ money($stats['total_usd'], 2) }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Nº factura, cliente...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo Doc.</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo_pago">
                            <option value="">Todos</option>
                            <option value="recibo">Recibo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="estado">
                            <option value="">Todos</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Método Pago</label>
                        <select class="form-select form-select-sm" wire:model.live="metodo_pago">
                            <option value="">Todos</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de pagos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('created_at')" href="#" class="text-decoration-none text-primary">
                                        Fecha/Hora
                                        @if($sortField === 'created_at')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Tipo Doc.</th>
                                <th class="fw-semibold">Nº Factura</th>
                                <th class="fw-semibold">Cliente/Paciente</th>
                                <th class="fw-semibold">Caja</th>
                                <th class="fw-semibold">Método</th>
                                <th class="fw-semibold">Creado por</th>
                                <th class="fw-semibold text-end">
                                    Total ({{ auth()->user()->empresa->pais->moneda_principal ?? 'USD' }})
                                </th>
                                <th class="fw-semibold text-center">Estado</th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                @php
                                    $tieneNC = $pago->notasCredito->where('estado', 'aprobado')->count() > 0;
                                    $tieneND = $pago->notasDebito->where('estado', 'aprobado')->count() > 0;
                                @endphp
                                <tr class="pago-row">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $pago->fecha->format('d/m/Y') }}</span>
                                            <small class="text-muted">{{ $pago->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ strtoupper(str_replace('_', ' ', $pago->tipo_pago)) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ str_pad($pago->numero, 8, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        @if($pago->clienteFiscal)
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $pago->clienteFiscal->razon_social }}">
                                                <div class="fw-medium">{{ $pago->clienteFiscal->razon_social }}</div>
                                            </div>

                                        @elseif($pago->consulta && $pago->consulta->paciente)
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $pago->consulta->paciente->nombre_completo }}">
                                                <div class="fw-medium">{{ $pago->consulta->paciente->nombre_completo }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $pago->caja->id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ str_replace('_', ' ', ucwords($pago->metodo_pago)) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $pago->user->name ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $esVzla = auth()->user()->empresa->pais->nombre == 'Venezuela';
                                            // Usar total_con_impuestos si hay IVA o IGTF, de lo contrario usar total
                                            $tieneImpuestos = ($pago->iva_monto > 0 || $pago->igtf_monto > 0);
                                            $totalPrincipal = $tieneImpuestos
                                                ? ($esVzla ? $pago->total_bs : $pago->total_usd)
                                                : ($esVzla ? $pago->total_bs : $pago->total_usd);
                                            $simboloPrincipal = $esVzla ? 'Bs' : '$';
                                            $totalSecundario = $esVzla ? $pago->total_usd : $pago->total_bs;
                                            $simboloSecundario = $esVzla ? '$' : 'Bs';
                                        @endphp
                                        <div class="fw-bold text-primary">{{ money($pago->total, 2) }}</div>



                                    </td>
                                    <td class="text-center">
                                        @if($tieneNC)
                                            <span class="badge bg-label-danger">Reversada</span>
                                        @elseif($pago->estado === 'aprobado')
                                            <span class="badge bg-label-success">Aprobado</span>
                                        @elseif($pago->estado === 'pendiente')
                                            <span class="badge bg-label-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-label-danger">Cancelado</span>
                                        @endif
                                        @if($tieneND)
                                            <span class="badge bg-label-info ms-1">Con ND</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.pagos.show', $pago) }}">
                                                    <i class="ri ri-eye-line me-1 text-info"></i> Ver
                                                </a>
                                                <button class="dropdown-item" wire:click="printReceipt({{ $pago->id }})">
                                                    <i class="ri ri-printer-line me-1 text-primary"></i> Imprimir
                                                </button>

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="ri ri-bank-card-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron pagos</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $pagos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    @if($showPreview && $previewPagoId)
        @livewire('admin.pagos.comprobantes', ['pagoId' => $previewPagoId])
    @endif
</div>
