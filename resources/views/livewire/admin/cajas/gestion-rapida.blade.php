<div class="card" wire:poll.5s="polling">
    {{-- Header con estado de caja --}}
    <div class="card-header pb-0">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-cash-register me-2 {{ $caja_abierta ? 'text-success' : 'text-danger' }}"></i>
                {{ $caja_abierta ? 'Caja Abierta' : 'Caja Cerrada' }}
            </h6>

            @if($caja_abierta)
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success">Corte #{{ $caja->numero_corte }}</span>
                    <span class="badge bg-info" title="Actualización automática cada 5 segundos">
                        {{ date('H:i:s') }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if(!$caja_abierta)
            {{-- Estado: Caja Cerrada --}}
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-lock fa-3x text-muted"></i>
                </div>
                <p class="text-muted mb-3">No hay caja abierta</p>
                <button
                    wire:click="mostrarApertura"
                    class="btn btn-primary w-100"
                >
                    <i class="fas fa-unlock me-2"></i>Aperturar Caja
                </button>
            </div>
        @else
            {{-- Estado: Caja Abierta - Resumen --}}

            {{-- Badge de estado destacado --}}
            <div class="alert alert-success d-flex align-items-center mb-3 py-2 mb-4 mt-4">
                <i class="fas fa-check-circle fa-lg me-2"></i>
                <div class="flex-grow-1">
                    <strong>Caja Activa</strong>
                    <small class="d-block text-success">Corte #{{ $caja->numero_corte }} • Apertura: {{ $caja->fecha_apertura->format('H:i') }}</small>
                </div>
            </div>

            {{-- Tarjetas de resumen en grid compacto --}}
            <div class="row g-2 mb-3">
                {{-- Ingresos --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-success fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-arrow-up me-1"></i>Ingresos del Día
                                    </small>
                                    <h4 class="mb-1 text-success fw-bold" style="font-size: 1.5rem;"><x-dual-currency :amount="$total_ingresos" /></h4>
                                    @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                        <small class="text-success" style="font-size: 0.75rem;">≈ {{ money($total_ingresos * $tasa_cambio, 2) }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="bg-white rounded-circle p-2 shadow-sm">
                                        <i class="fas fa-chart-line text-success fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Egresos --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-danger fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-arrow-down me-1"></i>Egresos del Día
                                    </small>
                                    <h4 class="mb-1 text-danger fw-bold" style="font-size: 1.5rem;"><x-dual-currency :amount="$total_egresos" /></h4>
                                   @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                        <small class="text-success" style="font-size: 0.75rem;">≈ {{ money($total_egresos * $tasa_cambio, 2) }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="bg-white rounded-circle p-2 shadow-sm">
                                        <i class="fas fa-receipt text-danger fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Balance Final --}}
                <div class="col-12">
                    <div class="card border-0 shadow" style="background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%);">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-wallet me-1"></i>Balance en Caja
                                    </small>
                                    <h3 class="mb-1 {{ $monto_final_ajustado >= 0 ? 'text-primary' : 'text-warning' }} fw-bold" style="font-size: 1.8rem;">
                                        <x-dual-currency :amount="$monto_final_ajustado" />
                                    </h3>
                                    @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                        <small class="text-primary" style="font-size: 0.10rem;">≈ {{ money($monto_final_ajustado * $tasa_cambio, 2) }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="bg-white rounded-circle p-3 shadow">
                                        <i class="fas fa-coins text-primary fa-1x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas con botones grandes --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <button
                        wire:click="mostrarEgreso"
                        class="btn btn-danger w-100 py-2 shadow-sm"
                        style="font-weight: 500;"
                    >
                        <i class="fas fa-minus-circle me-2"></i>
                        <span>Nuevo Egreso</span>
                    </button>
                </div>
                <div class="col-6">
                    <button
                        wire:click="mostrarCierre"
                        class="btn btn-outline-secondary w-100 py-2"
                        style="font-weight: 500;"
                    >
                        <i class="fas fa-lock me-2"></i>
                        <span>Cerrar Caja</span>
                    </button>
                </div>
            </div>

            {{-- Últimos egresos --}}
            @if($this->ultimosEgresos->count() > 0)
                <div class="mt-3 pt-3 border-top">
                    <h6 class="text-muted mb-2"><small>Últimos Egresos</small></h6>
                    <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                        @foreach($this->ultimosEgresos as $egreso)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-2">
                                        <div class="fw-semibold text-dark small">{{ $egreso->concepto }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-clock me-1"></i>{{ $egreso->created_at->format('H:i') }}
                                            @if($egreso->categoria)
                                                • <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ ucfirst($egreso->categoria) }}</span>
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-danger small">-{{ money($egreso->monto, 2) }}</div>
                                        @if(auth()->user()->empresa->pais->nombre == 'Venezuela')
                                            <small class="text-muted" style="font-size: 0.7rem;">{{ money($egreso->monto_bs, 2) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Modal: Apertura de Caja --}}
    @if($mostrarModalApertura)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-unlock me-2 text-primary"></i>Aperturar Caja
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('mostrarModalApertura', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Monto Inicial (USD) <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="monto_inicial"
                                class="form-control"
                                placeholder="0.00"
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones (opcional)</label>
                            <textarea
                                wire:model="observaciones_apertura"
                                rows="3"
                                class="form-control"
                                placeholder="Notas sobre la apertura..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            wire:click="$set('mostrarModalApertura', false)"
                        >
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="aperturarCaja"
                        >
                            <i class="fas fa-check me-1"></i>Aperturar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Registrar Egreso --}}
    @if($mostrarModalEgreso)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-minus-circle me-2"></i>Registrar Egreso
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('mostrarModalEgreso', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Concepto <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                wire:model="concepto_gasto"
                                class="form-control"
                                placeholder="Ej: Compra de materiales"
                                autofocus
                            >
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Monto (USD) <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="monto_gasto"
                                    class="form-control"
                                    placeholder="0.00"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Método de Pago</label>
                                <select
                                    wire:model="metodo_pago_gasto"
                                    class="form-select"
                                >
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select
                                wire:model="categoria_gasto"
                                class="form-select"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="combustible">Combustible</option>
                                <option value="materiales">Materiales</option>
                                <option value="servicios">Servicios</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Número de Referencia</label>
                            <input
                                type="text"
                                wire:model="referencia_gasto"
                                class="form-control"
                                placeholder="Opcional"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea
                                wire:model="observaciones_gasto"
                                rows="2"
                                class="form-control"
                                placeholder="Detalles adicionales..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            wire:click="$set('mostrarModalEgreso', false)"
                        >
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            wire:click="registrarEgreso"
                        >
                            <i class="fas fa-save me-1"></i>Registrar Egreso
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Cerrar Caja --}}
    @if($mostrarModalCierre)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-lock me-2"></i>Cerrar Caja
                        </h5>
                        <button type="button" class="btn-close btn-close" wire:click="$set('mostrarModalCierre', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> Esta acción no se puede deshacer. Asegúrese de que todos los pagos estén registrados.
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Ingresos:</span>
                                <span class="fw-semibold text-success"><x-dual-currency :amount="$total_ingresos" /></span>
                            </div>
                            @if(auth()->user()->empresa->pais->nombre == 'Venezuela' && $tasa_cambio > 1)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted" style="font-size: 0.85rem;">≈ en Bs:</span>
                                    <span class="text-success" style="font-size: 0.85rem;">{{ money($total_ingresos * $tasa_cambio, 2) }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Egresos:</span>
                                <span class="fw-semibold text-danger">-{{ money($total_egresos, 2) }}</span>
                            </div>
                            @if(auth()->user()->empresa->pais->nombre == 'Venezuela' && $tasa_cambio > 1)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted" style="font-size: 0.85rem;">≈ en Bs:</span>
                                    <span class="text-danger" style="font-size: 0.85rem;">-{{ money($total_egresos * $tasa_cambio, 2) }}</span>
                                </div>
                            @endif

                            <hr>

                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Balance Final:</span>
                                <span class="fw-bold {{ $monto_final_ajustado >= 0 ? 'text-primary' : 'text-warning' }} fs-5">
                                    <x-dual-currency :amount="$monto_final_ajustado" />
                                </span>
                            </div>
                            @if(auth()->user()->empresa->pais->nombre == 'Venezuela' && $tasa_cambio > 1)
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-muted" style="font-size: 0.85rem;">≈ en Bs:</span>
                                    <span class="{{ $monto_final_ajustado >= 0 ? 'text-primary' : 'text-warning' }}" style="font-size: 0.85rem;">
                                        {{ money($monto_final_ajustado * $tasa_cambio, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones de Cierre</label>
                            <textarea
                                wire:model="observaciones_cierre"
                                rows="3"
                                class="form-control"
                                placeholder="Notas sobre el cierre de caja..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            wire:click="$set('mostrarModalCierre', false)"
                        >
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            wire:click="cerrarCaja"
                        >
                            <i class="fas fa-check me-1"></i>Confirmar Cierre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
