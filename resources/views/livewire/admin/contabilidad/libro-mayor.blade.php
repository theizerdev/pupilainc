<div>
    @section('title', 'Libro Mayor')

    @push('styles')
    <style>
        .lm-hero { background: linear-gradient(135deg, #3B82F6 0%, #6366F1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .lm-hero h2 { color:#fff; margin:0; }
        .lm-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .cuenta-item { border-left:3px solid transparent; padding:.75rem .9rem; cursor:pointer;
                       transition:all .15s; border-bottom:1px solid #f1f3f5; }
        .cuenta-item:hover { background:#f8f9ff; border-left-color:#3B82F6; }
        .cuenta-item.active { background:#eef2ff; border-left-color:#3B82F6; }
        .cuenta-item .ci-codigo { font-size:.78rem; font-weight:600; color:var(--bs-primary); }
        .cuenta-item .ci-nombre { font-size:.85rem; font-weight:500; color:var(--bs-body-color); }
        .cuenta-item .ci-saldo { font-size:.8rem; font-weight:600; }

        .resumen-card { border:1px solid rgba(0,0,0,.06); border-radius:.55rem; padding:.7rem .9rem;
                        text-align:center; transition:all .15s; background:#fff; }
        .resumen-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.06); }
        .resumen-card .rc-label { font-size:.7rem; text-transform:uppercase; color:var(--bs-secondary-color);
                                 letter-spacing:.4px; font-weight:600; margin-bottom:.25rem; }
        .resumen-card .rc-value { font-size:1.15rem; font-weight:600; }

        .mov-row { transition:background .12s; }
        .mov-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Libro Mayor</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="lm-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-book-open-line me-2"></i>Libro Mayor</h2>
                <p class="mt-1">Movimientos detallados por cuenta contable</p>
            </div>
            @if($cuentaSeleccionada)
            <div class="d-flex gap-2">
                <a href="{{ route('admin.contabilidad.libro-mayor.excel', ['cuenta_id' => $cuenta_id, 'desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}"
                   class="btn btn-light btn-sm">
                    <i class="ri ri-file-excel-line me-1 text-success"></i>Excel
                </a>
                <a href="{{ route('admin.contabilidad.libro-mayor.pdf', ['cuenta_id' => $cuenta_id, 'desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}"
                   target="_blank" class="btn btn-light btn-sm">
                    <i class="ri ri-file-pdf-line me-1 text-danger"></i>PDF
                </a>
            </div>
            @endif
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-list-check-2"></i></div>
                    <div>
                        <div class="stat-label">Total cuentas</div>
                        <div class="stat-value">{{ $stats['total_cuentas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-exchange-line"></i></div>
                    <div>
                        <div class="stat-label">Con movimientos</div>
                        <div class="stat-value">{{ $stats['cuentas_con_movimientos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-receipt-line"></i></div>
                    <div>
                        <div class="stat-label">Total asientos</div>
                        <div class="stat-value">{{ $stats['total_asientos'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar cuenta</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo_cuenta">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="pasivo">Pasivo</option>
                            <option value="patrimonio">Patrimonio</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="fecha_desde">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="fecha_hasta">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="checkbox" id="mostrar_saldos_cero" wire:model.live="mostrar_saldos_cero">
                            <label class="form-check-label small" for="mostrar_saldos_cero">Saldos en cero</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Lista de Cuentas --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Cuentas contables</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                        @forelse($cuentas as $cuenta)
                            @php
                                $saldo = $this->calcularSaldoCuenta($cuenta);
                                $badgeTipo = match($cuenta->tipo) {
                                    'activo'    => 'bg-label-success',
                                    'pasivo'    => 'bg-label-danger',
                                    'ingreso'   => 'bg-label-primary',
                                    'patrimonio'=> 'bg-label-info',
                                    default     => 'bg-label-warning',
                                };
                            @endphp
                            <div class="cuenta-item {{ $cuenta_id == $cuenta->id ? 'active' : '' }}"
                                 wire:click="seleccionarCuenta({{ $cuenta->id }})">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="ci-codigo">{{ $cuenta->codigo }}</div>
                                        <div class="ci-nombre text-truncate">{{ $cuenta->nombre }}</div>
                                        <span class="badge {{ $badgeTipo }} mt-1">{{ ucfirst($cuenta->tipo) }}</span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <small class="text-muted d-block" style="font-size:.7rem;">Saldo</small>
                                        <span class="ci-saldo {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ format_money(abs($saldo), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="ri ri-search-line" style="font-size:2rem;opacity:.3;"></i>
                                <p class="mb-0 mt-2 small">No se encontraron cuentas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Movimientos --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-exchange-line me-2 text-primary"></i>Movimientos de la cuenta</h6>
                    </div>
                    <div class="card-body">
                        @if($cuentaSeleccionada)
                            {{-- Info de cuenta --}}
                            <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:#f8fafc;">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;flex:0 0 44px;">
                                    <i class="ri ri-book-open-line text-primary" style="font-size:1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0 fw-semibold">{{ $cuentaSeleccionada->codigo }} — {{ $cuentaSeleccionada->nombre }}</h5>
                                    <div class="d-flex gap-2 mt-1 flex-wrap">
                                        @php
                                            $selBadge = match($cuentaSeleccionada->tipo) {
                                                'activo'    => 'bg-label-success',
                                                'pasivo'    => 'bg-label-danger',
                                                'ingreso'   => 'bg-label-primary',
                                                'patrimonio'=> 'bg-label-info',
                                                default     => 'bg-label-warning',
                                            };
                                        @endphp
                                        <span class="badge {{ $selBadge }}">{{ ucfirst($cuentaSeleccionada->tipo) }}</span>
                                        <span class="badge bg-label-secondary">Naturaleza {{ ucfirst($cuentaSeleccionada->naturaleza) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen KPIs --}}
                            @if($resumen)
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <div class="resumen-card">
                                        <div class="rc-label">Saldo inicial</div>
                                        <div class="rc-value {{ $resumen['saldo_inicial'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ format_money(abs($resumen['saldo_inicial']), 2) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="resumen-card">
                                        <div class="rc-label">Total debe</div>
                                        <div class="rc-value text-primary">{{ format_money($resumen['total_debe'], 2) }}</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="resumen-card">
                                        <div class="rc-label">Total haber</div>
                                        <div class="rc-value text-info">{{ format_money($resumen['total_haber'], 2) }}</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="resumen-card">
                                        <div class="rc-label">Saldo final</div>
                                        <div class="rc-value {{ $resumen['saldo_final'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ format_money(abs($resumen['saldo_final']), 2) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="resumen-card">
                                        <div class="rc-label">Movimientos</div>
                                        <div class="rc-value text-secondary">{{ $resumen['total_movimientos'] }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Tabla de movimientos --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="fw-semibold">Fecha</th>
                                            <th class="fw-semibold">Asiento</th>
                                            <th class="fw-semibold">Tipo</th>
                                            <th class="fw-semibold">Descripción</th>
                                            <th class="text-end fw-semibold">Debe</th>
                                            <th class="text-end fw-semibold">Haber</th>
                                            <th class="text-end fw-semibold">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Saldo inicial --}}
                                        <tr style="background:#f8fafc;">
                                            <td colspan="4" class="fw-semibold small">Saldo inicial</td>
                                            <td class="text-end text-muted">—</td>
                                            <td class="text-end text-muted">—</td>
                                            <td class="text-end fw-semibold {{ $saldoInicial >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ format_money(abs($saldoInicial), 2) }}
                                            </td>
                                        </tr>

                                        @forelse($movimientos as $mov)
                                            @php
                                                $tipoBadge = match($mov->tipo) {
                                                    'diario'   => 'bg-label-primary',
                                                    'apertura' => 'bg-label-success',
                                                    default    => 'bg-label-warning',
                                                };
                                            @endphp
                                            <tr class="mov-row">
                                                <td><small>{{ $mov->fecha->format('d/m/Y') }}</small></td>
                                                <td>
                                                    <a href="#" class="text-decoration-none fw-semibold text-primary" title="Ver asiento completo">
                                                        {{ $mov->numero }}
                                                    </a>
                                                </td>
                                                <td><span class="badge {{ $tipoBadge }}">{{ ucfirst($mov->tipo) }}</span></td>
                                                <td>
                                                    <div class="small" title="{{ $mov->descripcion }}">{{ Str::limit($mov->descripcion, 40) }}</div>
                                                    @if($mov->referencia)
                                                        <small class="text-muted">Ref: {{ $mov->referencia }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($mov->debe > 0)
                                                        <span class="text-primary fw-semibold">{{ format_money($mov->debe, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($mov->haber > 0)
                                                        <span class="text-info fw-semibold">{{ format_money($mov->haber, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold {{ $mov->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ format_money(abs($mov->saldo), 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                                    <p class="mb-0 mt-1 small">No hay movimientos en el período seleccionado</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($movimientos->count() > 0)
                                        <tfoot class="bg-primary text-white fw-semibold">
                                            <tr>
                                                <td colspan="4" class="text-end">TOTALES DEL PERÍODO</td>
                                                <td class="text-end">{{ format_money($movimientos->sum('debe'), 2) }}</td>
                                                <td class="text-end">{{ format_money($movimientos->sum('haber'), 2) }}</td>
                                                <td class="text-end {{ $movimientos->last()->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ format_money(abs($movimientos->last()->saldo), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="ri ri-hand-pointer-line" style="font-size:2.5rem;opacity:.3;"></i>
                                <h5 class="mt-2 mb-1">Seleccione una cuenta</h5>
                                <p class="mb-0 small">Haga clic en una cuenta de la lista para ver sus movimientos detallados</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
