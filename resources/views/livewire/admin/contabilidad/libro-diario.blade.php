<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800"><i class="ri-book-open-line me-2"></i>Libro Diario</h1>
                <p class="text-muted">Registro cronológico de asientos contables</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.contabilidad.libro-diario.excel', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" class="btn btn-sm btn-outline-success">
                    <i class="ri-file-excel-2-line me-1"></i>Excel
                </a>
                <a href="{{ route('admin.contabilidad.libro-diario.pdf', ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="ri-file-pdf-2-line me-1"></i>PDF
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Asientos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_asientos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-file-list-3-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Debe</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Bs {{ format_money($stats['total_debe'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-arrow-up-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Haber</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Bs {{ format_money($stats['total_haber'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-arrow-down-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_desde">Desde:</label>
                            <input type="date" class="form-control" id="fecha_desde" wire:model.live="fecha_desde">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_hasta">Hasta:</label>
                            <input type="date" class="form-control" id="fecha_hasta" wire:model.live="fecha_hasta">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control" id="tipo" wire:model.live="tipo">
                                <option value="">Todos</option>
                                <option value="apertura">Apertura</option>
                                <option value="diario">Diario</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="cierre">Cierre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="resetFilters" title="Limpiar filtros">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="card shadow">
            <div class="card-body">
                @forelse($asientos as $asiento)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong class="text-primary">{{ $asiento->numero }}</strong>
                                        <span class="badge bg-label-{{ $asiento->tipo === 'diario' ? 'primary' : ($asiento->tipo === 'apertura' ? 'success' : ($asiento->tipo === 'cierre' ? 'danger' : 'warning')) }} ms-2">
                                            {{ ucfirst($asiento->tipo) }}
                                        </span>
                                    </div>
                                    <div class="text-muted">
                                        <i class="ri-calendar-line me-1"></i>{{ $asiento->fecha->format('d/m/Y') }}
                                        <span class="ms-2"><i class="ri-user-line me-1"></i>{{ $asiento->user->name ?? '-' }}</span>
                                    </div>
                                </div>

                                <p class="text-muted mb-2 small">{{ $asiento->descripcion }}</p>

                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Cuenta</th>
                                            <th>Descripción</th>
                                            <th class="text-end">Debe</th>
                                            <th class="text-end">Haber</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($asiento->detalles as $detalle)
                                            <tr>
                                                <td><strong>{{ $detalle->cuenta->codigo }}</strong></td>
                                                <td>{{ $detalle->cuenta->nombre }}</td>
                                                <td class="text-muted">{{ $detalle->descripcion }}</td>
                                                <td class="text-end">
                                                    @if($detalle->debe > 0)
                                                        {{ format_money($detalle->debe, 2, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($detalle->haber > 0)
                                                        {{ format_money($detalle->haber, 2, ',', '.') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">Totales:</td>
                                            <td class="text-end">{{ format_money($asiento->detalles->sum('debe'), 2, ',', '.') }}</td>
                                            <td class="text-end">{{ format_money($asiento->detalles->sum('haber'), 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="ri-book-open-line ri-3x mb-3 d-block"></i>
                        <p>No hay asientos en el período seleccionado</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
