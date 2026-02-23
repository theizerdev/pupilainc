<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Asientos Contables</h5>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3 g-3">
                            <div class="col-md-4">
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Buscar por número o descripción...">
                            </div>
                            <div class="col-md-2">
                                <select wire:model.live="tipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    <option value="apertura">Apertura</option>
                                    <option value="diario">Diario</option>
                                    <option value="ajuste">Ajuste</option>
                                    <option value="cierre">Cierre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select wire:model.live="estado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="borrador">Borrador</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="anulado">Anulado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" wire:model.live="fecha_desde" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="date" wire:model.live="fecha_hasta" class="form-control">
                            </div>
                        </div>

                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Debe</th>
                                        <th>Haber</th>
                                        <th>Estado</th>
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
                                            <td class="text-end">Bs {{ number_format($asiento->total_debe, 2) }}</td>
                                            <td class="text-end">Bs {{ number_format($asiento->total_haber, 2) }}</td>
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
                                                <button wire:click="verDetalles({{ $asiento->id }})" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Ver detalles">
                                                    <i class="ri-eye-line ri-20px"></i>
                                                </button>
                                                @if($asiento->estado !== 'anulado')
                                                    <button wire:click="anular({{ $asiento->id }})" wire:confirm="¿Está seguro de anular este asiento?" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Anular">
                                                        <i class="ri-close-circle-line ri-20px"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No hay asientos registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $asientos->links() }}
                        </div>
                    </div>
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
                                                    Bs {{ number_format($detalle->debe, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($detalle->haber > 0)
                                                    Bs {{ number_format($detalle->haber, 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTALES:</th>
                                        <th class="text-end">Bs {{ number_format($asientoSeleccionado->total_debe, 2) }}</th>
                                        <th class="text-end">Bs {{ number_format($asientoSeleccionado->total_haber, 2) }}</th>
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
