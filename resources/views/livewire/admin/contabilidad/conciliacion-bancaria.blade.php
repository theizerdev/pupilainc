<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Configuración de Conciliación -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri-bank-line me-2"></i>Conciliación Bancaria</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cuenta Bancaria</label>
                                <select wire:model.live="cuenta_bancaria_id" class="form-select">
                                    <option value="">Seleccionar cuenta bancaria</option>
                                    @foreach($cuentas_bancarias as $cuenta)
                                        <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('cuenta_bancaria_id') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha de Corte</label>
                                <input type="date" wire:model.live="fecha_corte" class="form-control">
                                @error('fecha_corte') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Saldo Estado de Cuenta</label>
                                <input type="number" step="0.01" wire:model.live="saldo_estado_cuenta" class="form-control" placeholder="0.00">
                                @error('saldo_estado_cuenta') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Acciones</label>
                                <div class="d-flex gap-2">
                                    @if(!$conciliacion_activa)
                                        <button type="button" wire:click="iniciarConciliacion" class="btn btn-primary">
                                            <i class="ri-play-line me-1"></i>Iniciar
                                        </button>
                                    @else
                                        <button type="button" wire:click="finalizarConciliacion" class="btn btn-success">
                                            <i class="ri-check-line me-1"></i>Finalizar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($cuenta_bancaria_id)
            <!-- Resumen de Conciliación -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-primary"><i class="ri-calculator-line"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ format_money($resumen['saldo_contable']) }}</h6>
                                    <small class="text-muted">Saldo Contable</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-info"><i class="ri-bank-line"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ format_money($resumen['saldo_bancario']) }}</h6>
                                    <small class="text-muted">Saldo Bancario</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-{{ abs($resumen['diferencia']) < 0.01 ? 'success' : 'warning' }}">
                                        <i class="ri-{{ abs($resumen['diferencia']) < 0.01 ? 'check' : 'alert' }}-line"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-{{ $resumen['diferencia'] >= 0 ? 'success' : 'danger' }}">
                                        {{ format_money(abs($resumen['diferencia'])) }}
                                    </h6>
                                    <small class="text-muted">Diferencia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-label-secondary"><i class="ri-time-line"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $resumen['movimientos_pendientes_contables'] + $resumen['movimientos_pendientes_bancarios'] }}</h6>
                                    <small class="text-muted">Mov. Pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Conciliación -->
            @if($conciliacion_activa)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="ri-information-line me-2"></i>
                            <div>
                                <strong>Conciliación en proceso</strong> - 
                                Iniciada el {{ $conciliacion_activa->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Buscar</label>
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar por descripción, número, referencia...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Opciones</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.live="mostrar_conciliados" id="mostrarConciliados">
                                        <label class="form-check-label" for="mostrarConciliados">
                                            Mostrar movimientos ya conciliados
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movimientos -->
            <div class="row">
                <!-- Movimientos Contables -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="ri-book-line me-1"></i>Movimientos Contables
                                <span class="badge bg-label-primary ms-2">{{ $movimientos_contables->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($movimientos_contables->count() > 0)
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Asiento</th>
                                                <th>Descripción</th>
                                                <th class="text-end">Monto</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($movimientos_contables as $mov)
                                                <tr class="{{ $mov->conciliado ? 'table-success' : '' }}">
                                                    <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                                                    <td><code>{{ $mov->numero }}</code></td>
                                                    <td class="small">{{ Str::limit($mov->descripcion, 30) }}</td>
                                                    <td class="text-end">
                                                        <span class="text-{{ $mov->monto >= 0 ? 'success' : 'danger' }}">
                                                            {{ $mov->monto >= 0 ? '+' : '' }}{{ format_money(abs($mov->monto)) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($mov->conciliado)
                                                            <span class="badge bg-success">Conciliado</span>
                                                        @else
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($conciliacion_activa)
                                                            @if($mov->conciliado)
                                                                <button type="button" wire:click="desconciliarMovimiento('contable', {{ $mov->id }})" class="btn btn-sm btn-outline-danger">
                                                                    <i class="ri-close-line"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="conciliarMovimiento('contable', {{ $mov->id }})" class="btn btn-sm btn-outline-success">
                                                                    <i class="ri-check-line"></i>
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ri-book-line ri-2x text-muted mb-2 d-block"></i>
                                    <p class="text-muted">No hay movimientos contables</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Movimientos Bancarios -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="ri-bank-line me-1"></i>Movimientos Bancarios
                                <span class="badge bg-label-info ms-2">{{ $movimientos_bancarios->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($movimientos_bancarios->count() > 0)
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Referencia</th>
                                                <th>Descripción</th>
                                                <th class="text-end">Monto</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($movimientos_bancarios as $mov)
                                                <tr class="{{ $mov->conciliado ? 'table-success' : '' }}">
                                                    <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                                                    <td><code>{{ $mov->referencia }}</code></td>
                                                    <td class="small">{{ Str::limit($mov->descripcion, 30) }}</td>
                                                    <td class="text-end">
                                                        <span class="text-{{ $mov->monto >= 0 ? 'success' : 'danger' }}">
                                                            {{ $mov->monto >= 0 ? '+' : '' }}{{ format_money(abs($mov->monto)) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($mov->conciliado)
                                                            <span class="badge bg-success">Conciliado</span>
                                                        @else
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($conciliacion_activa)
                                                            @if($mov->conciliado)
                                                                <button type="button" wire:click="desconciliarMovimiento('bancario', {{ $mov->id }})" class="btn btn-sm btn-outline-danger">
                                                                    <i class="ri-close-line"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="conciliarMovimiento('bancario', {{ $mov->id }})" class="btn btn-sm btn-outline-success">
                                                                    <i class="ri-check-line"></i>
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ri-bank-line ri-2x text-muted mb-2 d-block"></i>
                                    <p class="text-muted">No hay movimientos bancarios</p>
                                    <small class="text-muted">Importe el estado de cuenta bancario para comenzar</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ri-bank-line ri-2x"></i>
                            </span>
                        </div>
                        <h6 class="mb-1">Seleccione una cuenta bancaria</h6>
                        <p class="text-muted">Elija la cuenta bancaria que desea conciliar para comenzar el proceso.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>