<div>
    @section('title', 'Libro Diario')

    @push('styles')
    <style>
        .ld-hero { background: linear-gradient(135deg, #3B82F6 0%, #6366F1 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .ld-hero h2 { color:#fff; margin:0; }
        .ld-hero p { opacity:.9; margin:0; }
        .ld-stat { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                   transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .ld-stat:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .ld-stat .ld-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                            display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .ld-stat .ld-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .ld-stat .ld-label { font-size:.72rem; color:var(--bs-secondary-color);
                             text-transform:uppercase; letter-spacing:.4px; font-weight:600; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Libro Diario</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="ld-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-book-open-line me-2"></i>Libro Diario</h2>
                <p class="mt-1">Registro cronológico de todos los asientos contables</p>
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
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select wire:model.live="tipo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="apertura">Apertura</option>
                            <option value="diario">Diario</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="cierre">Cierre</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Cuenta</label>
                        <select wire:model.live="cuenta_id" class="form-select form-select-sm">
                            <option value="">Todas las cuentas</option>
                            @foreach($cuentas as $cuenta)
                                <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} — {{ $cuenta->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Buscar</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Número, descripción, cuenta...">
                    </div>
                </div>
                <div class="row g-3 mt-2 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-center flex-wrap">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" wire:model.live="mostrar_detalles" id="mostrarDetalles">
                                <label class="form-check-label small" for="mostrarDetalles">Mostrar detalles</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" wire:model.live="agrupar_por_fecha" id="agruparFecha">
                                <label class="form-check-label small" for="agruparFecha">Agrupar por fecha</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="ri ri-refresh-line me-1"></i>Limpiar
                            </button>
                            <button type="button" wire:click="exportarExcel" class="btn btn-outline-success btn-sm">
                                <i class="ri ri-file-excel-2-line me-1"></i>Excel
                            </button>
                            <button type="button" wire:click="exportarPdf" class="btn btn-outline-danger btn-sm">
                                <i class="ri ri-file-pdf-2-line me-1"></i>PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="ld-stat">
                    <div class="ld-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-file-list-3-line"></i></div>
                    <div>
                        <div class="ld-label">Total asientos</div>
                        <div class="ld-value">{{ number_format($stats['total_asientos']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ld-stat">
                    <div class="ld-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-arrow-up-line"></i></div>
                    <div>
                        <div class="ld-label">Total debe</div>
                        <div class="ld-value">{{ format_money($stats['total_debe']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ld-stat">
                    <div class="ld-icon" style="background:#fee2e2;color:#ef4444;"><i class="ri ri-arrow-down-line"></i></div>
                    <div>
                        <div class="ld-label">Total haber</div>
                        <div class="ld-value">{{ format_money($stats['total_haber']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ld-stat">
                    <div class="ld-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-calculator-line"></i></div>
                    <div>
                        <div class="ld-label">Promedio/asiento</div>
                        <div class="ld-value">{{ format_money($stats['promedio_por_asiento']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Libro Diario -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Asientos Contables</h6>
                        <div class="d-flex align-items-center gap-2">
                            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-muted small">por página</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($agrupar_por_fecha)
                            @php
                                $asientosPorFecha = $asientos->groupBy(function($asiento) {
                                    return $asiento->fecha->format('Y-m-d');
                                });
                            @endphp
                            @foreach($asientosPorFecha as $fecha => $asientosFecha)
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                        <span class="badge bg-label-primary ms-2">{{ $asientosFecha->count() }} asientos</span>
                                    </h6>
                                    @foreach($asientosFecha as $asiento)
                                        @include('livewire.admin.contabilidad.partials.asiento-card', ['asiento' => $asiento])
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            @forelse($asientos as $asiento)
                                @include('livewire.admin.contabilidad.partials.asiento-card', ['asiento' => $asiento])
                            @empty
                                <div class="text-center py-5">
                                    <div class="avatar avatar-xl mx-auto mb-3">
                                        <span class="avatar-initial rounded bg-label-secondary">
                                            <i class="ri-book-open-line ri-2x"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">No hay asientos contables</h6>
                                    <p class="text-muted">No se encontraron asientos en el período seleccionado con los filtros aplicados.</p>
                                </div>
                            @endforelse
                        @endif

                        @if($asientos->hasPages())
                            <div class="mt-4">
                                {{ $asientos->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
