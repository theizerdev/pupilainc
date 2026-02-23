<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri-lock-line me-2"></i>Cierre Contable</h5>
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
                            <label class="form-label">Tipo de cierre</label>
                            <select wire:model.live="tipo_cierre" class="form-select">
                                <option value="mensual">Cierre Mensual</option>
                                <option value="anual">Cierre Anual</option>
                                <option value="apertura">Asiento de Apertura</option>
                            </select>
                        </div>

                        @if($tipo_cierre === 'mensual')
                            <div class="mb-3">
                                <label class="form-label">Mes</label>
                                <select wire:model="mes" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Año</label>
                            <input type="number" wire:model="anio" class="form-control" min="2020" max="{{ now()->year + 1 }}">
                        </div>

                        <button wire:click="ejecutarCierre"
                                wire:confirm="¿Está seguro de ejecutar el {{ $tipo_cierre === 'mensual' ? 'cierre mensual' : ($tipo_cierre === 'anual' ? 'cierre anual' : 'asiento de apertura') }}? Esta acción generará un asiento contable."
                                class="btn btn-primary w-100">
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
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri-history-line me-2"></i>Cierres Realizados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Debe</th>
                                        <th>Haber</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cierresRealizados as $cierre)
                                        <tr>
                                            <td><strong>{{ $cierre->numero }}</strong></td>
                                            <td>{{ $cierre->fecha->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $cierre->tipo === 'cierre' ? 'danger' : 'success' }}">
                                                    {{ ucfirst($cierre->tipo) }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($cierre->descripcion, 40) }}</td>
                                            <td class="text-end">{{ number_format($cierre->total_debe, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($cierre->total_haber, 2, ',', '.') }}</td>
                                            <td>
                                                @if($cierre->esta_balanceado)
                                                    <span class="badge bg-success">✓</span>
                                                @else
                                                    <span class="badge bg-danger">✗</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No hay cierres registrados</td>
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
