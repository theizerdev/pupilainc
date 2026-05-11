<div>
    @if($showModal && $orden)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="ri ri-file-text-line me-2"></i>
                        Orden de Compra #{{ $orden->numero }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Información General -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Información General</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Proveedor:</td>
                                    <td><strong>{{ $orden->proveedor->nombre }}</strong></td>
                                </tr>
                                @if($orden->proveedor->rif)
                                <tr>
                                    <td class="text-muted">RIF:</td>
                                    <td>{{ $orden->proveedor->rif }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Almacén:</td>
                                    <td>{{ $orden->almacen->nombre }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estado:</td>
                                    <td>
                                        <span class="badge bg-{{ $orden->estado_info['color'] }}">
                                            {{ $orden->estado_info['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3">Fechas</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Emisión:</td>
                                    <td><strong>{{ $orden->fecha_emision->format('d/m/Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Esperada:</td>
                                    <td>{{ $orden->fecha_esperada?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                @if($orden->fecha_recepcion)
                                <tr>
                                    <td class="text-muted">Recepción:</td>
                                    <td><span class="text-success">{{ $orden->fecha_recepcion->format('d/m/Y') }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Creado por:</td>
                                    <td>{{ $orden->user->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($orden->observaciones)
                    <div class="alert alert-light border">
                        <h6 class="fw-bold mb-2"><i class="ri ri-information-line me-1"></i>Observaciones</h6>
                        <p class="mb-0">{{ $orden->observaciones }}</p>
                    </div>
                    @endif
                    
                    <!-- Detalle de Productos -->
                    <h6 class="fw-bold text-primary mb-3 mt-4">
                        <i class="ri ri-shopping-bag-line me-1"></i>
                        Productos ({{ $orden->detalles->count() }})
                    </h6>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 35%;">Producto</th>
                                    <th style="width: 15%;" class="text-center">Cantidad</th>
                                    <th style="width: 20%;" class="text-end">Precio Unit.</th>
                                    <th style="width: 25%;" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orden->detalles as $index => $detalle)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $detalle->producto->nombre }}</strong>
                                        @if($detalle->producto->codigo)
                                        <br><small class="text-muted">Código: {{ $detalle->producto->codigo }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $detalle->cantidad_solicitada }}</span>
                                    </td>
                                    <td class="text-end">{{ money($detalle->precio_unitario) }}</td>
                                    <td class="text-end fw-bold">{{ money($detalle->subtotal) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                                    <td class="text-end fw-bold text-primary fs-5">{{ money($orden->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    @if($orden->generada_automaticamente)
                    <div class="alert alert-info mt-3">
                        <i class="ri ri-robot-line me-1"></i>
                        Esta orden fue generada automáticamente por el sistema.
                    </div>
                    @endif
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                        <i class="ri ri-close-line me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="ri ri-printer-line me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
