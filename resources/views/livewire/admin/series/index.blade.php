<div>
    @section('title', 'Series de Documentos')

    @push('styles')
    <style>
        .series-hero { background: linear-gradient(135deg, #06B6D4 0%, #3B82F6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .series-hero h2 { color:#fff; margin:0; }
        .series-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .serie-row { transition:background .12s; }
        .serie-row:hover { background:#f8f9ff; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Series de Documentos</li>
            </ol>
        </nav>

        {{-- Hero + acciones --}}
        <div class="series-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-hashtag me-2"></i>Series de Documentos</h2>
                <p class="mt-1">Gestión de numeración de documentos fiscales</p>
            </div>
            @can('create series')
                <a href="{{ route('admin.series.create') }}" class="btn btn-light btn-sm">
                    <i class="ri ri-add-line me-1"></i>Nueva Serie
                </a>
            @endcan
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-hashtag"></i></div>
                    <div>
                        <div class="stat-label">Total series</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-check-line"></i></div>
                    <div>
                        <div class="stat-label">Series activas</div>
                        <div class="stat-value">{{ $stats['activas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-close-line"></i></div>
                    <div>
                        <div class="stat-label">Series inactivas</div>
                        <div class="stat-value">{{ $stats['inactivas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-file-text-line"></i></div>
                    <div>
                        <div class="stat-label">Tipos documento</div>
                        <div class="stat-value">{{ count($tipos) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri ri-check-line me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><i class="ri ri-search-line me-1"></i>Buscar</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search" placeholder="Número de serie...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tipo Documento</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo_documento">
                            <option value="">Todos</option>
                            @foreach($tipos as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" wire:model.live="filtro_activo">
                            <option value="">Todos</option>
                            <option value="1">Activas</option>
                            <option value="0">Inactivas</option>
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
                <h6 class="mb-0"><i class="ri ri-list-check-2 me-2 text-primary"></i>Listado de series</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('tipo_documento')" href="#" class="text-decoration-none text-primary">
                                        Tipo Documento
                                        @if($sortField === 'tipo_documento')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold">
                                    <a wire:click.prevent="sortBy('serie')" href="#" class="text-decoration-none text-primary">
                                        Serie
                                        @if($sortField === 'serie')
                                            <i class="ri ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="fw-semibold text-center">Correlativo Actual</th>
                                <th class="fw-semibold">Empresa</th>
                                <th class="fw-semibold">Sucursal</th>
                                <th class="fw-semibold text-center">Estado</th>
                                <th class="fw-semibold text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($series as $serie)
                                @php
                                    $iconos = [
                                        'factura' => 'ri ri-file-text-line text-primary',
                                        'boleta' => 'ri ri-receipt-line text-info',
                                        'nota_credito' => 'ri ri-file-reduce-line text-warning',
                                        'nota_debito' => 'ri ri-file-add-line text-danger',
                                        'recibo' => 'ri ri-file-copy-line text-success'
                                    ];
                                @endphp
                                <tr class="serie-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="{{ $iconos[$serie->tipo_documento] ?? 'ri ri-file-line' }} me-2 fs-5"></i>
                                            <span class="fw-medium">{{ $tipos[$serie->tipo_documento] ?? $serie->tipo_documento }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-dark fs-6">{{ $serie->serie }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="fw-bold fs-6">{{ str_pad($serie->correlativo_actual, $serie->longitud_correlativo, '0', STR_PAD_LEFT) }}</span>
                                            <small class="text-muted">Long: {{ $serie->longitud_correlativo }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $serie->empresa->nombre ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $serie->sucursal->nombre ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="statusSwitch{{ $serie->id }}"
                                                   wire:click="toggleActivo({{ $serie->id }})"
                                                   wire:confirm="¿Estás seguro de {{ $serie->activo ? 'desactivar' : 'activar' }} esta serie?"
                                                   {{ $serie->activo ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('edit series')
                                                <a href="{{ route('admin.series.edit', $serie) }}" class="dropdown-item">
                                                    <i class="ri ri-pencil-line me-1 text-primary"></i> Editar
                                                </a>
                                                @endcan
                                                @can('delete series')
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="delete({{ $serie->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar la serie {{ $serie->serie }}?">
                                                    <i class="ri ri-delete-bin-line me-1"></i> Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="ri ri-file-list-3-line" style="font-size:1.5rem;opacity:.3;"></i>
                                        <p class="mb-0 mt-1 small">No hay series registradas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $series->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
