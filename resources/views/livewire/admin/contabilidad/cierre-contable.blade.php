<div>
    @section('title', 'Cierre Contable')

    @push('styles')
    <style>
        .cc-hero { background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .cc-hero h2 { color:#fff; margin:0; }
        .cc-hero p { opacity:.9; margin:0; }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Cierre Contable</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="cc-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-lock-line me-2"></i>Cierre Contable</h2>
                <p class="mt-1">Generación de cierres mensuales, anuales y asientos de apertura</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri ri-settings-3-line me-2 text-primary"></i>Configuración de cierre</h6>
                    </div>
                    <div class="card-body">
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tipo de cierre</label>
                            <select wire:model.live="tipo_cierre" class="form-select form-select-sm">
                                <option value="mensual">Cierre Mensual</option>
                                <option value="anual">Cierre Anual</option>
                                <option value="apertura">Asiento de Apertura</option>
                            </select>
                        </div>

                        @if($tipo_cierre === 'mensual')
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mes</label>
                                <select wire:model="mes" class="form-select form-select-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Año</label>
                            <input type="number" wire:model="anio" class="form-control form-control-sm" min="2020" max="{{ now()->year + 1 }}">
                        </div>

                        <button wire:click="ejecutarCierre"
                                wire:confirm="¿Está seguro de ejecutar el {{ $tipo_cierre === 'mensual' ? 'cierre mensual' : ($tipo_cierre === 'anual' ? 'cierre anual' : 'asiento de apertura') }}? Esta acción generará un asiento contable."
                                class="btn btn-primary w-100 btn-sm">
                            <i class="ri-lock-line me-1"></i>
                            @if($tipo_cierre === 'mensual')
                                Ejecutar Cierre Mensual
                            @elseif($tipo_cierre === 'anual')
                                Ejecutar Cierre Anual
                            @else
                                Generar Asiento de Apertura
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="mb-0"><i class="ri-history-line me-2 text-primary"></i>Cierres realizados</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="fw-semibold">Número</th>
                                        <th class="fw-semibold">Fecha</th>
                                        <th class="fw-semibold">Tipo</th>
                                        <th class="fw-semibold">Descripción</th>
                                        <th class="text-end fw-semibold">Debe</th>
                                        <th class="text-end fw-semibold">Haber</th>
                                        <th class="text-center fw-semibold">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cierresRealizados as $cierre)
                                        <tr>
                                            <td class="fw-semibold">{{ $cierre->numero }}</td>
                                            <td><small>{{ $cierre->fecha->format('d/m/Y') }}</small></td>
                                            <td>
                                                <span class="badge bg-label-{{ $cierre->tipo === 'cierre' ? 'danger' : 'success' }}">
                                                    {{ ucfirst($cierre->tipo) }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">{{ Str::limit($cierre->descripcion, 40) }}</td>
                                            <td class="text-end">{{ format_money($cierre->total_debe, 2) }}</td>
                                            <td class="text-end">{{ format_money($cierre->total_haber, 2) }}</td>
                                            <td class="text-center">
                                                @if($cierre->esta_balanceado)
                                                    <span class="badge bg-label-success"><i class="ri ri-checkbox-circle-line me-1"></i>Balanceado</span>
                                                @else
                                                    <span class="badge bg-label-danger"><i class="ri ri-error-warning-line me-1"></i>Desbalanceado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="ri ri-history-line" style="font-size:1.5rem;opacity:.3;"></i>
                                                <p class="mb-0 mt-1 small">No hay cierres registrados</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
