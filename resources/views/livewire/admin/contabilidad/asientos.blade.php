<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Asientos Contables</h1>
                <p class="text-muted">Gestión de asientos contables</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Asientos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-file-list-3-line ri-2x text-gray-300"></i>
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
                                    Aprobados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['aprobados'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-check-line ri-2x text-gray-300"></i>
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
                                    Borradores
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['borradores'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-draft-line ri-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Anulados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['anulados'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="ri-close-circle-line ri-2x text-gray-300"></i>
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
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o descripción...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control" id="tipo" wire:model.live="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="apertura">Apertura</option>
                                <option value="diario">Diario</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="cierre">Cierre</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" wire:model.live="estado">
                                <option value="">Todos los estados</option>
                                <option value="borrador">Borrador</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="anulado">Anulado</option>
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

        <!-- Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Asientos Contables</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a wire:click.prevent="sortBy('numero')" href="#" class="text-decoration-none">
                                        Número
                                        @if($sortField === 'numero')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('fecha')" href="#" class="text-decoration-none">
                                        Fecha
                                        @if($sortField === 'fecha')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a wire:click.prevent="sortBy('tipo')" href="#" class="text-decoration-none">
                                        Tipo
                                        @if($sortField === 'tipo')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Descripción</th>
                                <th class="text-end">Debe</th>
                                <th class="text-end">Haber</th>
                                <th>
                                    <a wire:click.prevent="sortBy('estado')" href="#" class="text-decoration-none">
                                        Estado
                                        @if($sortField === 'estado')
                                            <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                        @else
                                            <i class="ri-expand-up-down-line"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asientos as $asiento)
                                <tr>
                                    <td><strong>{{ $asiento->numero }}</strong></td>
                                    <td>{{ $asiento->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $asiento->tipo === 'diario' ? 'primary' : ($asiento->tipo === 'apertura' ? 'success' : ($asiento->tipo === 'cierre' ? 'danger' : 'warning')) }}">
                                            {{ ucfirst($asiento->tipo) }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($asiento->descripcion, 50) }}</td>
                                    <td class="text-end">Bs {{ format_money($asiento->total_debe, 2) }}</td>
                                    <td class="text-end">Bs {{ format_money($asiento->total_haber, 2) }}</td>
                                    <td>
                                        @if($asiento->estado === 'aprobado')
                                            <span class="badge bg-label-success">Aprobado</span>
                                        @elseif($asiento->estado === 'anulado')
                                            <span class="badge bg-label-danger">Anulado</span>
                                        @else
                                            <span class="badge bg-label-warning">Borrador</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @can('view contabilidad')
                                                <button class="dropdown-item" wire:click="verDetalles({{ $asiento->id }})">
                                                    <i class="ri ri-eye-line me-1"></i> Ver Detalles
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
                                    <td colspan="8" class="text-center">No se encontraron asientos</td>
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

    <!-- Modal Detalles -->
    @if($showDetalles && $asientoSeleccionado)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Asiento Contable {{ $asientoSeleccionado->numero }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetalles"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Fecha:</strong> {{ $asientoSeleccionado->fecha->format('d/m/Y') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Tipo:</strong> {{ ucfirst($asientoSeleccionado->tipo) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Estado:</strong> 
                                <span class="badge bg-label-{{ $asientoSeleccionado->estado === 'aprobado' ? 'success' : ($asientoSeleccionado->estado === 'anulado' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($asientoSeleccionado->estado) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>Usuario:</strong> {{ $asientoSeleccionado->user->name }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Descripción:</strong> {{ $asientoSeleccionado->descripcion }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cuenta</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Debe</th>
                                        <th class="text-end">Haber</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asientoSeleccionado->detalles as $detalle)
                                        <tr>
                                            <td>{{ $detalle->cuenta->codigo }} - {{ $detalle->cuenta->nombre }}</td>
                                            <td>{{ $detalle->descripcion }}</td>
                                            <td class="text-end">
                                                @if($detalle->debe > 0)
                                                    Bs {{ format_money($detalle->debe, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($detalle->haber > 0)
                                                    Bs {{ format_money($detalle->haber, 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTALES:</th>
                                        <th class="text-end">Bs {{ format_money($asientoSeleccionado->total_debe, 2) }}</th>
                                        <th class="text-end">Bs {{ format_money($asientoSeleccionado->total_haber, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="text-end">Balance:</th>
                                        <th colspan="2" class="text-center">
                                            @if($asientoSeleccionado->esta_balanceado)
                                                <span class="badge bg-success">✓ Balanceado</span>
                                            @else
                                                <span class="badge bg-danger">✗ Desbalanceado</span>
                                            @endif
                                        </th>
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
