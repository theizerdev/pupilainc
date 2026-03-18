<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800"><i class="ri-book-2-line me-2"></i>Libro Mayor</h1>
                <p class="text-muted">Movimientos por cuenta contable</p>
            </div>
            @if($cuenta_id)
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.contabilidad.libro-mayor.excel', ['cuenta_id' => $cuenta_id, 'desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" class="btn btn-sm btn-outline-success">
                        <i class="ri-file-excel-2-line me-1"></i>Excel
                    </a>
                    <a href="{{ route('admin.contabilidad.libro-mayor.pdf', ['cuenta_id' => $cuenta_id, 'desde' => $fecha_desde, 'hasta' => $fecha_hasta]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="ri-file-pdf-2-line me-1"></i>PDF
                    </a>
                </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cuenta_id">Cuenta:</label>
                            <select wire:model.live="cuenta_id" class="form-control" id="cuenta_id">
                                <option value="">Seleccione una cuenta...</option>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_desde">Desde:</label>
                            <input type="date" wire:model.live="fecha_desde" class="form-control" id="fecha_desde">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_hasta">Hasta:</label>
                            <input type="date" wire:model.live="fecha_hasta" class="form-control" id="fecha_hasta">
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

                        @if($cuentaSeleccionada)
                            <div class="alert alert-light border mb-3">
                                <strong>{{ $cuentaSeleccionada->codigo }}</strong> — {{ $cuentaSeleccionada->nombre }}
                                <span class="badge bg-label-{{ $cuentaSeleccionada->tipo === 'activo' ? 'success' : ($cuentaSeleccionada->tipo === 'pasivo' ? 'danger' : ($cuentaSeleccionada->tipo === 'ingreso' ? 'primary' : 'warning')) }} ms-2">
                                    {{ ucfirst($cuentaSeleccionada->tipo) }}
                                </span>
                                <span class="badge bg-label-secondary ms-1">{{ ucfirst($cuentaSeleccionada->naturaleza) }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Asiento</th>
                                            <th>Descripción</th>
                                            <th class="text-end">Debe</th>
                                            <th class="text-end">Haber</th>
                                            <th class="text-end">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-light fst-italic">
                                            <td colspan="3">Saldo Anterior</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end fw-bold">Bs {{ format_money($saldoInicial, 2, ',', '.') }}</td>
                                        </tr>
                                        @forelse($movimientos as $mov)
                                            <tr>
                                                <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                                                <td><strong>{{ $mov->numero }}</strong></td>
                                                <td>{{ Str::limit($mov->descripcion, 60) }}</td>
                                                <td class="text-end">
                                                    @if($mov->debe > 0)
                                                        {{ format_money($mov->debe, 2, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($mov->haber > 0)
                                                        {{ format_money($mov->haber, 2, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">Bs {{ format_money($mov->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No hay movimientos en el período</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($movimientos->count() > 0)
                                        <tfoot class="table-dark fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end">TOTALES PERÍODO</td>
                                                <td class="text-end">{{ format_money($movimientos->sum('debe'), 2, ',', '.') }}</td>
                                                <td class="text-end">{{ format_money($movimientos->sum('haber'), 2, ',', '.') }}</td>
                                                <td class="text-end">Bs {{ format_money($movimientos->last()->saldo, 2, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="ri-book-2-line ri-3x mb-3 d-block"></i>
                        <p>Seleccione una cuenta para ver sus movimientos</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
