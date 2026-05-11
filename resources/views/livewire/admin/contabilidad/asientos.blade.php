<div>
    @section('title', 'Asientos Contables')

    @push('styles')
    <style>
        .as-hero { background: linear-gradient(135deg, #3B82F6 0%, #6366F1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .as-hero h2 { color:#fff; margin:0; }
        .as-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .asiento-row { transition:background .12s; }
        .asiento-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Asientos Contables</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="as-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-file-list-3-line me-2"></i>Asientos Contables</h2>
                <p class="mt-1">Gestión de asientos contables</p>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-file-list-3-line"></i></div>
                    <div>
                        <div class="stat-label">Total asientos</div>
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
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-draft-line"></i></div>
                    <div>
                        <div class="stat-label">Borradores</div>
                        <div class="stat-value">{{ $stats['borradores'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;color:#ef4444;"><i class="ri ri-close-circle-line"></i></div>
                    <div>
                        <div class="stat-label">Anulados</div>
                        <div class="stat-value">{{ $stats['anulados'] }}</div>
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
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Número o descripción...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo">
                            <option value="">Todos</option>
                            <option value="apertura">Apertura</option>
                            <option value="diario">Diario</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="cierre">Cierre</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="estado">
                            <option value="">Todos</option>
                            <option value="borrador">Borrador</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="anulado">Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" class="form-control form-select-sm" wire:model.live="fecha_desde">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" class="form-control form-select-sm" wire:model.live="fecha_hasta">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters" title="Limpiar filtros">
                            <i class="ri ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de asientos contables</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('numero')" href="#" class="text-decoration-none text-white">
                                        Número
                                        @if($sortField === 'numero')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('fecha')" href="#" class="text-decoration-none text-white">
                                        Fecha
                                        @if($sortField === 'fecha')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">Tipo</th>
                                <th class="fw-semibold">Descripción</th>
                                <th class="text-end fw-semibold">Debe</th>
                                <th class="text-end fw-semibold">Haber</th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('estado')" href="#" class="text-decoration-none text-white">
                                        Estado
                                        @if($sortField === 'estado')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asientos as $asiento)
                                @php
                                    $tipoBadge = match($asiento->tipo) {
                                        'diario'   => 'bg-label-primary',
                                        'apertura' => 'bg-label-success',
                                        'cierre'   => 'bg-label-danger',
                                        default    => 'bg-label-warning',
                                    };
                                @endphp
                                <tr class="asiento-row">
                                    <td class="fw-semibold">{{ $asiento->numero }}</td>
                                    <td><small>{{ $asiento->fecha->format('d/m/Y') }}</small></td>
                                    <td><span class="badge {{ $tipoBadge }}">{{ ucfirst($asiento->tipo) }}</span></td>
                                    <td>{{ Str::limit($asiento->descripcion, 50) }}</td>
                                    <td class="text-end">{{ format_money($asiento->total_debe, 2) }}</td>
                                    <td class="text-end">{{ format_money($asiento->total_haber, 2) }}</td>
                                    <td>
                                        @if($asiento->estado === 'aprobado')
                                            <span class="badge bg-label-success">Aprobado</span>
                                        @elseif($asiento->estado === 'anulado')
                                            <span class="badge bg-label-danger">Anulado</span>
                                        @else
                                            <span class="badge bg-label-warning">Borrador</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('view contabilidad')
                                                <button class="dropdown-item" wire:click="verDetalles({{ $asiento->id }})">
                                                    <i class="ri ri-eye-line me-1 text-primary"></i> Ver Detalles
                                                </button>
                                                @endcan
                                                @if($asiento->estado !== 'anulado')
                                                    @can('delete contabilidad')
                                                    <button type="button" class="dropdown-item text-danger"
                                                            wire:click="anular({{ $asiento->id }})"
                                                            wire:confirm="¿Está seguro de anular este asiento?">
                                                        <i class="ri ri-close-circle-line me-1"></i> Anular
                                                    </button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No se encontraron asientos</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $asientos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detalles --}}
    @if($showDetalles && $asientoSeleccionado)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asiento Contable {{ $asientoSeleccionado->numero }}</h5>
                    <button type="button" class="btn-close" wire:click="closeDetalles"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:#f8fafc;">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:44px;height:44px;flex:0 0 44px;">
                            <i class="ri ri-file-list-3-line text-primary" style="font-size:1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0 fw-semibold">{{ $asientoSeleccionado->numero }}</h5>
                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                <span class="badge bg-label-primary">{{ ucfirst($asientoSeleccionado->tipo) }}</span>
                                @if($asientoSeleccionado->estado === 'aprobado')
                                    <span class="badge bg-label-success">Aprobado</span>
                                @elseif($asientoSeleccionado->estado === 'anulado')
                                    <span class="badge bg-label-danger">Anulado</span>
                                @else
                                    <span class="badge bg-label-warning">Borrador</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 small">
                        <div class="col-md-4"><i class="ri ri-calendar-line me-1 text-muted"></i><strong>Fecha:</strong> {{ $asientoSeleccionado->fecha->format('d/m/Y') }}</div>
                        <div class="col-md-4"><i class="ri ri-user-line me-1 text-muted"></i><strong>Usuario:</strong> {{ $asientoSeleccionado->user->name }}</div>
                        <div class="col-md-4"><i class="ri ri-bookmark-line me-1 text-muted"></i><strong>Descripción:</strong> {{ $asientoSeleccionado->descripcion }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="fw-semibold">Cuenta</th>
                                    <th class="fw-semibold">Descripción</th>
                                    <th class="text-end fw-semibold">Debe</th>
                                    <th class="text-end fw-semibold">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asientoSeleccionado->detalles as $detalle)
                                    <tr>
                                        <td><code class="text-primary">{{ $detalle->cuenta->codigo }}</code> — {{ $detalle->cuenta->nombre }}</td>
                                        <td class="text-muted small">{{ $detalle->descripcion }}</td>
                                        <td class="text-end">
                                            @if($detalle->debe > 0)
                                                {{ format_money($detalle->debe, 2) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($detalle->haber > 0)
                                                {{ format_money($detalle->haber, 2) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-semibold">
                                <tr>
                                    <td colspan="2" class="text-end">TOTALES</td>
                                    <td class="text-end">{{ format_money($asientoSeleccionado->total_debe, 2) }}</td>
                                    <td class="text-end">{{ format_money($asientoSeleccionado->total_haber, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end">Balance</td>
                                    <td colspan="2" class="text-center">
                                        @if($asientoSeleccionado->esta_balanceado)
                                            <span class="badge bg-label-success"><i class="ri ri-checkbox-circle-line me-1"></i>Balanceado</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="ri ri-error-warning-line me-1"></i>Desbalanceado</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeDetalles">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
