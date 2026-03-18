<div class="py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Pagos Registrados</h6>
                        <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Pago
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Buscar...">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select wire:model.live="tipo_pago_filter" class="form-select form-select-sm">
                                <option value="">Todos los tipos</option>
                                <option value="factura">Factura</option>
                                <option value="recibo">Recibo</option>
                                <option value="nota_credito">Nota de Crédito</option>
                                <option value="nota_debito">Nota de Débito</option>
                                <option value="boleta">Boleta</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="metodo_pago" class="form-select form-select-sm">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo_bs">Efectivo Bs</option>
                                <option value="efectivo_usd">Efectivo USD</option>
                                <option value="transferencia_bs">Transferencia Bs</option>
                                <option value="pago_movil">Pago Móvil</option>
                                <option value="zelle">Zelle</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Método</th>
                                    <th class="text-end">Total USD</th>
                                    <th class="text-end">Total Bs</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagos as $pago)
                                <tr>
                                    <td>{{ $pago->numero_completo }}</td>
                                    <td>
                                        @if($pago->tipo_pago === 'factura')
                                        <span class="badge badge-sm bg-primary">Factura</span>
                                        @elseif($pago->tipo_pago === 'nota_credito')
                                        <span class="badge badge-sm bg-danger">NC</span>
                                        @elseif($pago->tipo_pago === 'nota_debito')
                                        <span class="badge badge-sm bg-success">ND</span>
                                        @elseif($pago->tipo_pago === 'recibo')
                                        <span class="badge badge-sm bg-secondary">Recibo</span>
                                        @else
                                        <span class="badge badge-sm bg-light text-dark">{{ ucfirst($pago->tipo_pago) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        @if($pago->consulta)
                                        {{ $pago->consulta->paciente->nombre_completo }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-info">
                                            {{ str_replace('_', ' ', ucfirst($pago->metodo_pago)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ format_money($pago->total_usd, 2) }}</td>
                                    <td class="text-end">Bs {{ format_money($pago->total_bs, 2) }}</td>
                                    <td>
                                        @if($pago->estado === 'aprobado')
                                        <span class="badge badge-sm bg-success">Aprobado</span>
                                        @elseif($pago->estado === 'pendiente')
                                        <span class="badge badge-sm bg-warning">Pendiente</span>
                                        @else
                                        <span class="badge badge-sm bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.pagos.show', $pago->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay pagos registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-3">
                        {{ $pagos->links('livewire.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
