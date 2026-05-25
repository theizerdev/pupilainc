<div>
    @section('title', 'Notas de Crédito')

    @push('styles')
    <style>
        .nc-hero { background: linear-gradient(135deg, #EF4444 0%, #F97316 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .nc-hero h2 { color:#fff; margin:0; }
        .nc-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .nc-row { transition:background .12s; }
        .nc-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Notas de Crédito</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="nc-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-arrow-go-back-line me-2"></i>Notas de Crédito</h2>
                <p class="mt-1">Gestión de notas de crédito fiscales y no fiscales</p>
            </div>
            <div>
                <a href="{{ route('admin.notas-credito.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Nota de Crédito
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ri ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;color:#ef4444;"><i class="ri ri-file-list-3-line"></i></div>
                    <div>
                        <div class="stat-label">Total notas</div>
                        <div class="stat-value">{{ $notas->total() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Aprobadas</div>
                        <div class="stat-value">{{ $notas->where('estado', 'aprobado')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Anuladas</div>
                        <div class="stat-value">{{ $notas->where('estado', 'cancelado')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Total Bs.</div>
                        <div class="stat-value">{{ format_money($notas->where('estado', 'aprobado')->sum('total_bs') ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Serie, número, cliente...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="estado">
                            <option value="">Todos</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="cancelado">Anulado</option>
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
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="$set('search', ''); $set('estado', ''); $set('fecha_desde', ''); $set('fecha_hasta', '')">
                            <i class="ri ri-refresh-line me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de notas de crédito</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">N° Nota Crédito</th>
                                <th class="fw-semibold">N° Control</th>
                                <th class="fw-semibold">Fecha</th>
                                <th class="fw-semibold">Cliente / Paciente</th>
                                <th class="fw-semibold">Factura Origen</th>
                                <th class="fw-semibold">Motivo</th>
                                <th class="text-end fw-semibold">Total Bs.</th>
                                <th class="text-center fw-semibold">Estado</th>
                                <th class="text-center fw-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notas as $nota)
                                <tr class="nc-row {{ $nota->estado === 'cancelado' ? 'bg-light' : '' }}">
                                    <td>
                                        <div class="fw-semibold text-danger">{{ $nota->numero_completo }}</div>
                                        @if($nota->es_factura_fiscal)
                                            <span class="badge bg-label-primary">Fiscal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($nota->numero_control_fiscal)
                                            <code class="small text-primary">{{ $nota->numero_control_fiscal }}</code>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><small>{{ $nota->fecha->format('d/m/Y') }}</small></div>
                                        <small class="text-muted">{{ $nota->fecha->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($nota->clienteFiscal)
                                            <div class="text-truncate" style="max-width: 180px;" title="{{ $nota->clienteFiscal->razon_social }}">
                                                <span class="fw-medium">{{ $nota->clienteFiscal->razon_social }}</span>
                                            </div>
                                            <small class="text-muted">{{ $nota->clienteFiscal->documento_completo }}</small>
                                        @elseif($nota->consulta && $nota->consulta->paciente)
                                            <div class="text-truncate" style="max-width: 180px;" title="{{ $nota->consulta->paciente->nombre_completo }}">
                                                <span class="fw-medium">{{ $nota->consulta->paciente->nombre_completo }}</span>
                                            </div>
                                            <small class="text-muted">{{ $nota->consulta->paciente->documento_identidad }}</small>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($nota->pagoOrigen)
                                            <span class="badge bg-label-secondary">{{ $nota->pagoOrigen->numero_completo }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($nota->tipoNotaCredito)
                                            <span class="badge bg-label-info">{{ $nota->tipoNotaCredito->codigo }}</span>
                                            <div class="text-truncate small text-muted" style="max-width: 150px;" title="{{ $nota->tipoNotaCredito->descripcion }}">
                                                {{ $nota->tipoNotaCredito->descripcion }}
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        {{ format_money($nota->total_bs ?: 0, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($nota->estado === 'aprobado')
                                            <span class="badge bg-label-success"><i class="ri ri-checkbox-circle-line me-1"></i>Aprobado</span>
                                        @elseif($nota->estado === 'cancelado')
                                            <span class="badge bg-label-danger"><i class="ri ri-close-circle-line me-1"></i>Anulado</span>
                                        @else
                                            <span class="badge bg-label-warning">{{ ucfirst($nota->estado) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.pagos.show', $nota->id) }}">
                                                    <i class="ri ri-eye-line me-1 text-primary"></i> Ver Detalle
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.pagos.download', ['pago' => $nota->id, 'formato' => 'letter']) }}" target="_blank">
                                                    <i class="ri ri-download-line me-1 text-info"></i> Descargar Media Carta
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.pagos.download', ['pago' => $nota->id, 'formato' => 'a4']) }}" target="_blank">
                                                    <i class="ri ri-file-pdf-line me-1 text-danger"></i> Descargar A4
                                                </a>
                                                @if($nota->estado === 'aprobado')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.notas-debito.create', ['nota_credito_id' => $nota->id]) }}">
                                                    <i class="ri ri-file-add-line me-1 text-primary"></i> Crear Nota Débito (Anular NC)
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="anular({{ $nota->id }})"
                                                        wire:confirm="¿Está seguro de anular esta nota de crédito? Esta acción no se puede deshacer.">
                                                    <i class="ri ri-close-circle-line me-1"></i> Anular
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron notas de crédito</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $notas->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
