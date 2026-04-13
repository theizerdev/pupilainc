<div wire:ignore.self class="modal fade" id="registrarGotasModal{{ $consultaId }}" tabindex="-1" aria-labelledby="registrarGotasModalLabel{{ $consultaId }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrarGotasModalLabel{{ $consultaId }}">
                    <i class="ri ri-drop-line text-info me-2"></i>Registrar Aplicación de Gotas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Paciente</label>
                    <input type="text" class="form-control" value="{{ $consulta->paciente->nombre_completo }}" disabled>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Ojo Derecho (OD)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri ri-eye-line text-primary"></i></span>
                            <input type="number" class="form-control" wire:model="gotas_od" min="0" placeholder="Cantidad de gotas">
                        </div>
                        @error('gotas_od') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ojo Izquierdo (OI)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri ri-eye-line text-primary"></i></span>
                            <input type="number" class="form-control" wire:model="gotas_oi" min="0" placeholder="Cantidad de gotas">
                        </div>
                        @error('gotas_oi') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de Gota (Opcional)</label>
                    <input type="text" class="form-control" wire:model="tipo_gota" placeholder="Ej. Tropicamida, Ciclopentolato...">
                    @error('tipo_gota') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Observaciones (Opcional)</label>
                    <textarea class="form-control" wire:model="observaciones" rows="2" placeholder="Cualquier nota adicional sobre la aplicación"></textarea>
                    @error('observaciones') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                @if($historial->count() > 0)
                    <hr class="my-4">
                    <h6 class="mb-3 text-secondary"><i class="ri ri-history-line me-2"></i>Historial de Aplicaciones</h6>
                    <div class="table-responsive" style="max-height: 200px;">
                        <table class="table table-sm table-bordered text-center align-middle" style="font-size: 0.85rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Hora</th>
                                    <th>OD</th>
                                    <th>OI</th>
                                    <th>Tipo</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial as $registro)
                                    <tr>
                                        <td class="text-nowrap">{{ $registro->created_at->format('h:i A') }}</td>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $registro->gotas_od }}</span></td>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $registro->gotas_oi }}</span></td>
                                        <td class="text-truncate" style="max-width: 100px;" title="{{ $registro->tipo_gota ?: '-' }}">{{ $registro->tipo_gota ?: '-' }}</td>
                                        <td class="text-truncate" style="max-width: 100px;" title="{{ $registro->user->nombres ?? 'Sistema' }}">{{ $registro->user->nombres ?? 'Sistema' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" wire:click="registrar">
                    <span wire:loading.remove wire:target="registrar"><i class="ri ri-save-line me-1"></i>Registrar Gotas</span>
                    <span wire:loading wire:target="registrar"><i class="ri ri-loader-4-line ri-spin me-1"></i>Guardando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
