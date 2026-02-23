<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Notas de Crédito</h1>
                <p class="text-muted">Gestión de notas de crédito - SENIAT</p>
            </div>
            <div>
                <a href="{{ route('admin.notas-credito.create') }}" class="btn btn-danger">
                    <i class="fas fa-plus"></i> Nueva Nota de Crédito
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Notas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notas->total() }}</div>
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
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Aprobadas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notas->where('estado', 'aprobado')->count() }}</div>
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
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Anuladas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notas->where('estado', 'cancelado')->count() }}</div>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Bs.
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ money($notas->where('estado', 'aprobado')->sum('total_bs'), 2, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por serie, número, cliente...">
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

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fecha_desde">Desde:</label>
                            <input type="date" class="form-control" id="fecha_desde" wire:model.live="fecha_desde">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fecha_hasta">Hasta:</label>
                            <input type="date" class="form-control" id="fecha_hasta" wire:model.live="fecha_hasta">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="$set('search', ''); $set('estado', ''); $set('fecha_desde', ''); $set('fecha_hasta', '')">
                                <i class="fas fa-refresh"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Listado de Notas de Crédito</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Nota Crédito</th>
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
                                <tr class="{{ $nota->estado === 'cancelado' ? 'table-secondary' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-danger">{{ $nota->numero_completo }}</span>
                                            @if($nota->es_factura_fiscal)
                                            <span class="badge bg-primary">Fiscal</span>
                                            @endif
                                        </div>
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
                                        @if($nota->tipoNotaCredito)
                                            <span class="badge bg-info">{{ $nota->tipoNotaCredito->codigo }}</span>
                                            <div class="text-truncate" style="max-width: 150px;" title="{{ $nota->tipoNotaCredito->descripcion }}">
                                                <small class="text-muted">{{ $nota->tipoNotaCredito->descripcion }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-danger">{{ money($nota->total_bs ?: 0, 2, ',', '.') }}</div>

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
                                                <a class="dropdown-item" href="{{ route('admin.pagos.download', ['pago' => $nota->id, 'formato' => 'letter']) }}" target="_blank">
                                                    <i class="ri ri-download-line me-1"></i> Descargar Media Carta
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.pagos.download', ['pago' => $nota->id, 'formato' => 'a4']) }}" target="_blank">
                                                    <i class="ri ri-file-pdf-line me-1"></i> Descargar A4
                                                </a>
                                                @if($nota->estado === 'aprobado')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.notas-debito.create', ['nota_credito_id' => $nota->id]) }}">
                                                    <i class="ri ri-file-add-line me-1"></i> Crear Nota Débito (Anular NC)
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                        wire:click="anular({{ $nota->id }})"
                                                        wire:confirm="¿Está seguro de anular esta nota de crédito? Esta acción no se puede deshacer.">
                                                    <i class="ri ri-close-circle-line me-1"></i> Anular Directamente
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No se encontraron notas de crédito</td>
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
</div>
