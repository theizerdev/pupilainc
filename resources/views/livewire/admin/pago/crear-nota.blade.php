<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Crear Nota de {{ $tipo_nota === 'credito' ? 'Crédito' : 'Débito' }}</h6>
                    <small class="text-muted">Documento origen: {{ $pago->numero_completo }} - {{ $pago->fecha->format('d/m/Y') }}</small>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <div class="row">
                            <!-- Tipo de Nota -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Nota *</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" wire:model.live="tipo_nota" value="credito" id="tipo_credito">
                                    <label class="btn btn-outline-danger" for="tipo_credito">
                                        <i class="fas fa-minus-circle"></i> Nota de Crédito
                                    </label>
                                    <input type="radio" class="btn-check" wire:model.live="tipo_nota" value="debito" id="tipo_debito">
                                    <label class="btn btn-outline-success" for="tipo_debito">
                                        <i class="fas fa-plus-circle"></i> Nota de Débito
                                    </label>
                                </div>
                            </div>

                            <!-- Tipo específico -->
                            <div class="col-md-6 mb-3">
                                @if($tipo_nota === 'credito')
                                <label class="form-label">Motivo de Nota de Crédito *</label>
                                <select wire:model="tipo_nota_credito_id" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    @foreach($tiposCredito as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->codigo }} - {{ $tipo->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_nota_credito_id') <small class="text-danger">{{ $message }}</small> @enderror
                                @else
                                <label class="form-label">Motivo de Nota de Débito *</label>
                                <select wire:model="tipo_nota_debito_id" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    @foreach($tiposDebito as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->codigo }} - {{ $tipo->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_nota_debito_id') <small class="text-danger">{{ $message }}</small> @enderror
                                @endif
                            </div>

                            <!-- Monto -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monto USD *</label>
                                <input wire:model="monto" type="number" step="0.01" class="form-control">
                                @error('monto') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">
                                    Monto original: ${{ number_format($pago->total_usd, 2) }} |
                                    Saldo disponible: ${{ number_format($pago->saldo_disponible, 2) }}
                                </small>
                            </div>

                            <!-- Motivo -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descripción del Motivo *</label>
                                <textarea wire:model="motivo_nota" class="form-control" rows="3"></textarea>
                                @error('motivo_nota') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Items afectados -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input wire:model.live="afecta_items" class="form-check-input" type="checkbox" id="afecta_items">
                                    <label class="form-check-label" for="afecta_items">Afecta items específicos</label>
                                </div>
                            </div>

                            @if($afecta_items)
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Seleccionar Items</label>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th width="50"></th>
                                                <th>Descripción</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pago->detalles as $detalle)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" wire:model="items_seleccionados" value="{{ $detalle->id }}" class="form-check-input">
                                                </td>
                                                <td>{{ $detalle->descripcion }}</td>
                                                <td class="text-end">${{ number_format($detalle->subtotal, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Referencia al Documento Afectado (SENIAT) -->
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-file-invoice me-2"></i>
                                <strong>Documento Afectado (Ref. SENIAT):</strong><br>
                                <small>
                                    Factura N°: <strong>{{ $pago->numero_completo }}</strong> |
                                    @if($pago->numero_control_fiscal)
                                    N° Control: <strong>{{ $pago->numero_control_fiscal }}</strong> |
                                    @endif
                                    Fecha: <strong>{{ $pago->fecha->format('d/m/Y') }}</strong> |
                                    Monto: <strong>${{ number_format($pago->total_usd, 2) }}</strong>
                                </small>
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="alert alert-{{ $tipo_nota === 'credito' ? 'danger' : 'success' }}">
                            <strong>Resumen:</strong><br>
                            Se creará una <strong>Nota de {{ $tipo_nota === 'credito' ? 'Crédito' : 'Débito' }}</strong> por <strong>${{ number_format($monto, 2) }}</strong><br>
                            @if($tipo_nota === 'credito')
                            Esto <strong>disminuirá</strong> el monto del documento original.
                            @else
                            Esto <strong>aumentará</strong> el monto del documento original.
                            @endif
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.pagos.show', $pago->id) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-{{ $tipo_nota === 'credito' ? 'danger' : 'success' }}">
                                <i class="fas fa-save"></i> Crear Nota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
