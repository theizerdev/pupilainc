<div>
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Notas de Débito</h1>
            <p class="text-muted">Gestión de notas de débito fiscales SENIAT - Montos en Bolívares (Bs.)</p>
        </div>
        <div>
            <a href="{{ route('admin.notas-debito.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Nota de Débito
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Notas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprobadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['aprobadas']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Anuladas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['anuladas']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Bs.</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_bs'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Búsqueda:</label>
                        <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Serie, número, paciente o RIF...">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select class="form-control" id="estado" wire:model.live="estado">
                            <option value="">Todos</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="cancelado">Anulado</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Fecha Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" wire:model.live="fecha_desde">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">Fecha Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" wire:model.live="fecha_hasta">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Notas de Débito</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Nota Débito</th>
                            <th>N° Control</th>
                            <th>Fecha</th>
                            <th>Cliente/Paciente</th>
                            <th>Factura Origen</th>
                            <th>Motivo</th>
                            <th class="text-end">Total Bs.</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notas as $nota)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $nota->numero_completo }}</span>
                            </td>
                            <td>
                                @if($nota->numero_control_fiscal)
                                    <span class="badge bg-info">{{ $nota->numero_control_fiscal }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $nota->fecha->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $nota->fecha->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($nota->clienteFiscal)
                                    <div class="text-truncate" style="max-width: 180px;" title="{{ $nota->clienteFiscal->razon_social }}">
                                        {{ $nota->clienteFiscal->razon_social }}
                                    </div>
                                    <small class="text-muted">{{ $nota->clienteFiscal->documento_completo }}</small>
                                @elseif($nota->consulta && $nota->consulta->paciente)
                                    <div class="text-truncate" style="max-width: 180px;" title="{{ $nota->consulta->paciente->nombre_completo }}">
                                        {{ $nota->consulta->paciente->nombre_completo }}
                                    </div>
                                    <small class="text-muted">{{ $nota->consulta->paciente->documento_identidad }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($nota->pagoOrigen)
                                    <span class="badge bg-secondary">{{ $nota->pagoOrigen->numero_completo }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($nota->tipoNotaDebito)
                                    <span class="badge bg-info">{{ $nota->tipoNotaDebito->codigo }}</span>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $nota->tipoNotaDebito->descripcion }}">
                                        <small class="text-muted">{{ $nota->tipoNotaDebito->descripcion }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-primary">Bs. {{ number_format($nota->total_bs ?: ($nota->total * ($nota->tasa_cambio_usd ?: 1)), 2, ',', '.') }}</div>
                                <small class="text-muted">USD {{ number_format($nota->total_usd ?: ($nota->total_bs / ($nota->tasa_cambio_usd ?: 1)), 2) }}</small>
                            </td>
                            <td class="text-center">
                                @if($nota->estado === 'aprobado')
                                <span class="badge bg-success">Aprobado</span>
                                @elseif($nota->estado === 'cancelado')
                                <span class="badge bg-danger">Anulado</span>
                                @else
                                <span class="badge bg-warning">{{ ucfirst($nota->estado) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri ri-more-2-line"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.pagos.show', $nota->id) }}">
                                            <i class="ri ri-eye-line me-1"></i> Ver Detalle
                                        </a>
                                        @if($nota->estado === 'aprobado')
                                        <button type="button" class="dropdown-item text-danger"
                                                wire:click="anular({{ $nota->id }})"
                                                wire:confirm="¿Está seguro de anular esta nota de débito? Esta acción no se puede deshacer.">
                                            <i class="ri ri-close-circle-line me-1"></i> Anular
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No se encontraron notas de débito</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $notas->links() }}
            </div>
        </div>
    </div>
</div>
