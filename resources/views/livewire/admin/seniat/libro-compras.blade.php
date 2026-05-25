<div>
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Filtros y Controles -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ri-shopping-cart-line me-2"></i>Libro de Compras SENIAT</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" wire:model.live="desde" class="form-control">
                                @error('desde') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" wire:model.live="hasta" class="form-control">
                                @error('hasta') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo Documento</label>
                                <select wire:model.live="tipo_documento" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="factura">Facturas</option>
                                    <option value="nota_credito">Notas de Crédito</option>
                                    <option value="nota_debito">Notas de Débito</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Proveedor</label>
                                <select wire:model.live="proveedor_id" class="form-select">
                                    <option value="">Todos los proveedores</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Acciones</label>
                                <div class="d-flex gap-2">
                                    <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary">
                                        <i class="ri-refresh-line me-1"></i>Limpiar
                                    </button>
                                    <button type="button" wire:click="exportarTxt" class="btn btn-outline-primary">
                                        <i class="ri-file-text-line me-1"></i>TXT
                                    </button>
                                    <button type="button" wire:click="exportarExcel" class="btn btn-outline-success">
                                        <i class="ri-file-excel-2-line me-1"></i>Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary"><i class="ri-file-list-3-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ number_format($stats['total_documentos']) }}</h6>
                                <small class="text-muted">Total Documentos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success"><i class="ri-receipt-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ number_format($stats['facturas']) }}</h6>
                                <small class="text-muted">Facturas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning"><i class="ri-file-reduce-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ number_format($stats['notas_credito']) }}</h6>
                                <small class="text-muted">N. Crédito</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-danger"><i class="ri-file-add-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ number_format($stats['notas_debito']) }}</h6>
                                <small class="text-muted">N. Débito</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info"><i class="ri-building-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ number_format($stats['proveedores_unicos']) }}</h6>
                                <small class="text-muted">Proveedores</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-secondary"><i class="ri-money-dollar-circle-line"></i></span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ format_money($totales['total']) }}</h6>
                                <small class="text-muted">Total General</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Documentos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Documentos de Compra</h6>
                        <small class="text-muted">
                            Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                        </small>
                    </div>
                    <div class="card-body">
                        @if($documentos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Proveedor</th>
                                            <th>RIF/CI</th>
                                            <th>Número</th>
                                            <th>Control Fiscal</th>
                                            <th class="text-end">Base Imponible</th>
                                            <th class="text-end">IVA</th>
                                            <th class="text-end">IVA Retenido</th>
                                            <th class="text-end">Exento</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentos as $doc)
                                            <tr>
                                                <td>{{ $doc->fecha->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="badge bg-label-{{ $doc->tipo_documento === 'factura' ? 'primary' : ($doc->tipo_documento === 'nota_credito' ? 'warning' : 'danger') }}">
                                                        {{ strtoupper(str_replace('_', ' ', $doc->tipo_documento)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $doc->proveedor->nombre ?? 'N/A' }}</td>
                                                <td>{{ $doc->proveedor ? $doc->proveedor->tipo_documento . '-' . $doc->proveedor->numero_documento : 'N/A' }}</td>
                                                <td><code>{{ $doc->numero_completo }}</code></td>
                                                <td><code>{{ $doc->numero_control_fiscal }}</code></td>
                                                <td class="text-end">
                                                    @if($doc->tipo_documento === 'nota_credito')
                                                        <span class="text-danger">-{{ format_money($doc->base_imponible) }}</span>
                                                    @else
                                                        {{ format_money($doc->base_imponible) }}
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($doc->tipo_documento === 'nota_credito')
                                                        <span class="text-danger">-{{ format_money($doc->iva_monto) }}</span>
                                                    @else
                                                        {{ format_money($doc->iva_monto) }}
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ format_money($doc->iva_retenido ?? 0) }}</td>
                                                <td class="text-end">
                                                    @if($doc->tipo_documento === 'nota_credito')
                                                        <span class="text-danger">-{{ format_money($doc->monto_exento) }}</span>
                                                    @else
                                                        {{ format_money($doc->monto_exento) }}
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    @if($doc->tipo_documento === 'nota_credito')
                                                        <span class="text-danger">-{{ format_money($doc->total_con_impuestos ?? $doc->total) }}</span>
                                                    @else
                                                        {{ format_money($doc->total_con_impuestos ?? $doc->total) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr class="fw-bold">
                                            <td colspan="6" class="text-end">TOTALES:</td>
                                            <td class="text-end">{{ format_money($totales['base_imponible']) }}</td>
                                            <td class="text-end">{{ format_money($totales['iva_monto']) }}</td>
                                            <td class="text-end">{{ format_money($totales['iva_retenido']) }}</td>
                                            <td class="text-end">{{ format_money($totales['monto_exento']) }}</td>
                                            <td class="text-end">{{ format_money($totales['total']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar avatar-xl mx-auto mb-3">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="ri-shopping-cart-line ri-2x"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">No hay documentos de compra</h6>
                                <p class="text-muted">No se encontraron documentos fiscales de compra en el período seleccionado.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>