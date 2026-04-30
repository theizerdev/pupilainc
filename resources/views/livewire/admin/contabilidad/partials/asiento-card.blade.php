<div class="border rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center gap-2">
            <strong class="text-primary">{{ $asiento->numero }}</strong>
            <span class="badge bg-label-{{ $asiento->tipo === 'diario' ? 'primary' : ($asiento->tipo === 'apertura' ? 'success' : ($asiento->tipo === 'cierre' ? 'danger' : 'warning')) }}">
                {{ ucfirst($asiento->tipo) }}
            </span>
            @if($asiento->referencia_tipo)
                <span class="badge bg-label-info">{{ ucfirst(str_replace('_', ' ', $asiento->referencia_tipo)) }}</span>
            @endif
        </div>
        <div class="text-muted small">
            <i class="ri-calendar-line me-1"></i>{{ $asiento->fecha->format('d/m/Y') }}
            <span class="ms-2"><i class="ri-user-line me-1"></i>{{ $asiento->user->name ?? '-' }}</span>
        </div>
    </div>

    <p class="text-muted mb-2 small">{{ $asiento->descripcion }}</p>

    @if($mostrar_detalles)
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Código</th>
                        <th>Cuenta</th>
                        <th>Descripción</th>
                        <th class="text-end" style="width: 120px;">Debe</th>
                        <th class="text-end" style="width: 120px;">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asiento->detalles as $detalle)
                        <tr>
                            <td><code class="text-primary">{{ $detalle->cuenta->codigo }}</code></td>
                            <td>{{ $detalle->cuenta->nombre }}</td>
                            <td class="text-muted small">{{ $detalle->descripcion }}</td>
                            <td class="text-end">
                                @if($detalle->debe > 0)
                                    <span class="fw-semibold">{{ format_money($detalle->debe) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($detalle->haber > 0)
                                    <span class="fw-semibold">{{ format_money($detalle->haber) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Totales:</td>
                        <td class="text-end text-success">{{ format_money($asiento->detalles->sum('debe')) }}</td>
                        <td class="text-end text-danger">{{ format_money($asiento->detalles->sum('haber')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                {{ $asiento->detalles->count() }} movimientos
            </div>
            <div class="d-flex gap-3">
                <span class="text-success small">
                    <i class="ri-arrow-up-line me-1"></i>{{ format_money($asiento->detalles->sum('debe')) }}
                </span>
                <span class="text-danger small">
                    <i class="ri-arrow-down-line me-1"></i>{{ format_money($asiento->detalles->sum('haber')) }}
                </span>
            </div>
        </div>
    @endif
</div>