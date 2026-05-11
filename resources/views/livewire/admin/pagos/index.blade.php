<div>
    <div class="">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Pagos y Facturación</h1>
                <p class="text-muted">Gestión de pagos, facturas y recibos</p>
            </div>
            <div>
                <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Pago
                </a>
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
                                    Total Pagos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pagos->total() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pagos->where('estado', 'aprobado')->count() }}</div>
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
                                    Pendientes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pagos->where('estado', 'pendiente')->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    Total (USD)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ format_money($pagos->where('estado', 'aprobado')->sum('total_usd'), 2) }}</div>
                                @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                <small class="text-muted">Bs {{ format_money($pagos->where('estado', 'aprobado')->sum('total_bs'), 2) }}</small>
                                @endif
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Búsqueda:</label>
                            <input type="text" class="form-control" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar pago...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tipo_pago">Tipo:</label>
                            <select class="form-control" id="tipo_pago" wire:model.live="tipo_pago">
                                <option value="">Todos</option>
                                <option value="factura">Factura</option>
                                <option value="boleta">Boleta</option>
                                <option value="recibo">Recibo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" wire:model.live="estado">
                                <option value="">Todos</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="metodo_pago">Método:</label>
                            <select class="form-control" id="metodo_pago" wire:model.live="metodo_pago">
                                <option value="">Todos</option>
                                <option value="efectivo_bs">Efectivo Bs</option>
                                <option value="efectivo_usd">Efectivo USD</option>
                                <option value="transferencia_bs">Transferencia Bs</option>
                                <option value="pago_movil">Pago Móvil</option>
                                <option value="zelle">Zelle</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-secondary btn-block" wire:click="$set('search', ''); $set('tipo_pago', ''); $set('estado', ''); $set('metodo_pago', '')">
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
                <h6 class="m-0 font-weight-bold text-primary">Listado de Pagos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Tipo Doc.</th>
                                <th>Factura</th>
                                <th>Cliente/Paciente</th>
                                <th>Caja</th>
                                <th>Método</th>

                                <th>Creado por</th>
                                <th class="text-end">Total (USD)</th>
                                <th class="text-center">Estado</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr>
                                    <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $pago->created_at->format('H:i') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ strtoupper(str_replace('_', ' ', $pago->tipo_pago)) }}</span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold">{{ str_pad($pago->numero, 8, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </td>


                                    <td>
                                        @if($pago->clienteFiscal)
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $pago->clienteFiscal->razon_social }}">
                                                {{ $pago->clienteFiscal->razon_social }}
                                            </div>
                                            <small class="text-muted">{{ $pago->clienteFiscal->documento_completo }}</small>
                                        @elseif($pago->consulta && $pago->consulta->paciente)
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $pago->consulta->paciente->nombre_completo }}">
                                                {{ $pago->consulta->paciente->nombre_completo }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $pago->caja->id ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ str_replace('_', ' ', ucwords($pago->metodo_pago)) }}</span>
                                    </td>

                                    <td>{{ $pago->user->name ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <div class="fw-bold text-primary">{{ format_money($pago->total_usd, 2) }}</div>
                                        @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                        <small class="text-muted">Bs {{ format_money($pago->total_bs, 2) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $tieneNC = $pago->notasCredito->where('estado', 'aprobado')->count() > 0;
                                            $tieneND = $pago->notasDebito->where('estado', 'aprobado')->count() > 0;
                                        @endphp
                                        @if($tieneNC)
                                        <span class="badge bg-danger">Reversada</span>
                                        @elseif($pago->estado === 'aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                        @elseif($pago->estado === 'pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @else
                                        <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                        @if($tieneND)
                                        <span class="badge bg-info ms-1">Con ND</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">No se encontraron pagos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $pagos->links('livewire.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
