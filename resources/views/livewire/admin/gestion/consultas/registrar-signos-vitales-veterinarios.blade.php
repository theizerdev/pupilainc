<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-thermometer-line me-2"></i>
                Signos Vitales Veterinarios
            </h5>
            @if($mascota)
                <span class="badge bg-warning">
                    <i class="ri-paw-line me-1"></i>
                    {{ $mascota->nombre }}
                    @if($mascota->especie || $mascota->raza)
                        - {{ $mascota->especie?->nombre ?? '' }} {{ $mascota->raza?->nombre ?? '' }}
                    @endif
                </span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form wire:submit="guardar">
            <div class="row">
                <!-- Temperatura Rectal -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-thermometer-line text-danger"></i>
                        Temperatura Rectal (°C)
                        <small class="text-muted d-block">Rango normal: 38.0-39.2°C</small>
                    </label>
                    <input type="number" step="0.1" min="35" max="43"
                           class="form-control @error('temperatura_rectal') is-invalid @enderror"
                           wire:model.defer="temperatura_rectal"
                           placeholder="38.5">
                    @error('temperatura_rectal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Frecuencia Cardíaca -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-heart-pulse-line text-danger"></i>
                        Frecuencia Cardíaca (lpm)
                        <small class="text-muted d-block">Varía por especie/tamaño</small>
                    </label>
                    <input type="number" min="30" max="300"
                           class="form-control @error('frecuencia_cardiaca') is-invalid @enderror"
                           wire:model.defer="frecuencia_cardiaca"
                           placeholder="120">
                    @error('frecuencia_cardiaca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Frecuencia Respiratoria -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="ri-lungs-line text-primary"></i>
                        Frecuencia Respiratoria (rpm)
                        <small class="text-muted d-block">10-30 rpm normal</small>
                    </label>
                    <input type="number" min="5" max="120"
                           class="form-control @error('frecuencia_respiratoria') is-invalid @enderror"
                           wire:model.defer="frecuencia_respiratoria"
                           placeholder="20">
                    @error('frecuencia_respiratoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Peso Actual -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-weight-line text-success"></i>
                        Peso Actual (kg)
                    </label>
                    <input type="number" step="0.01" min="0.1" max="100"
                           class="form-control @error('peso_actual_kg') is-invalid @enderror"
                           wire:model.defer="peso_actual_kg"
                           placeholder="5.5">
                    @error('peso_actual_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- BCS Score -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-star-line text-warning"></i>
                        BCS Score (Condición Corporal)
                        <small class="text-muted d-block">1-9 (5 ideal, >6 sobrepeso)</small>
                    </label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="range" class="form-range" min="1" max="9" step="1"
                               wire:model.defer="bcs_score"
                               style="flex: 1">
                        <span class="badge bg-primary fs-5" style="min-width: 40px; text-align: center;">
                            {{ $bcs_score ?? '-' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">1: Emaciado</small>
                        <small class="text-muted">5: Ideal</small>
                        <small class="text-muted">9: Obeso</small>
                    </div>
                    @error('bcs_score') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <!-- Observaciones -->
                <div class="col-12 mb-3">
                    <label class="form-label">
                        <i class="ri-chat-quote-line text-info"></i>
                        Observaciones de Enfermería
                    </label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror"
                              rows="4"
                              wire:model.defer="observaciones"
                              placeholder="Observaciones generales, comportamiento, actitud, etc."></textarea>
                    @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Alerta de referencias veterinarias -->
            <div class="alert alert-info d-flex align-items-start" role="alert">
                <i class="ri-information-line me-2 fs-5"></i>
                <div>
                    <strong>Referencias rápidas:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Perro mediano:</strong> FC: 70-120 lpm | FR: 10-30 rpm | Temp: 38.0-39.2°C</li>
                        <li><strong>Gato:</strong> FC: 140-220 lpm | FR: 20-40 rpm | Temp: 38.0-39.5°C</li>
                        <li><strong>Perro pequeño:</strong> FC: 100-140 lpm | FR: 15-30 rpm</li>
                        <li><strong>Perro grande:</strong> FC: 60-100 lpm | FR: 10-20 rpm</li>
                    </ul>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="history.back()">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-1"></i>Guardar Signos Vitales
                </button>
            </div>
        </form>
    </div>
</div>
