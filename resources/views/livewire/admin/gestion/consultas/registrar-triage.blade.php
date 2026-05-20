<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-first-aid-kit-line me-2"></i>
                Evaluación de Triaje
            </h5>
            @if($mascota)
                <span class="badge bg-warning">
                    <i class="ri-paw-line me-1"></i>
                    {{ $mascota->nombre }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form wire:submit="guardar">
            <!-- Tipo de Emergencia -->
            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="ri-alarm-warning-line text-danger"></i>
                        Tipo de Emergencia *
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_emergencia"
                                   id="urgente" value="urgente"
                                   wire:model.defer="tipo_emergencia">
                            <label class="form-check-label" for="urgente">
                                <span class="badge bg-danger">🚨 Urgente</span>
                                <small class="d-block text-muted">Requiere atención inmediata</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_emergencia"
                                   id="semi-urgente" value="semi-urgente"
                                   wire:model.defer="tipo_emergencia">
                            <label class="form-check-label" for="semi-urgente">
                                <span class="badge bg-warning">⚠️ Semi-urgente</span>
                                <small class="d-block text-muted">Atención en corto tiempo</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_emergencia"
                                   id="no-urgente" value="no-urgente"
                                   wire:model.defer="tipo_emergencia">
                            <label class="form-check-label" for="no-urgente">
                                <span class="badge bg-success">✅ No urgente</span>
                                <small class="d-block text-muted">Consulta rutinaria</small>
                            </label>
                        </div>
                    </div>
                    @error('tipo_emergencia') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Prioridad -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="ri-sort-asc text-primary"></i>
                        Prioridad de Atención *
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prioridad"
                                   id="prioridad1" value="1"
                                   wire:model.defer="prioridad">
                            <label class="form-check-label" for="prioridad1">
                                <span class="badge bg-danger">1 - Alta</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prioridad"
                                   id="prioridad2" value="2"
                                   wire:model.defer="prioridad">
                            <label class="form-check-label" for="prioridad2">
                                <span class="badge bg-warning">2 - Media</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prioridad"
                                   id="prioridad3" value="3"
                                   wire:model.defer="prioridad">
                            <label class="form-check-label" for="prioridad3">
                                <span class="badge bg-success">3 - Baja</span>
                            </label>
                        </div>
                    </div>
                    @error('prioridad') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Motivo de Ingreso -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="ri-file-text-line text-info"></i>
                        Motivo de Ingreso *
                    </label>
                    <textarea class="form-control @error('motivo_ingreso') is-invalid @enderror"
                              rows="2"
                              wire:model.defer="motivo_ingreso"
                              placeholder="Descripción del motivo de consulta"></textarea>
                    @error('motivo_ingreso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Duración de Síntomas -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="ri-time-line"></i>
                        Duración de Síntomas
                    </label>
                    <input type="text" class="form-control @error('duracion_sintomas') is-invalid @enderror"
                           wire:model.defer="duracion_sintomas"
                           placeholder="Ej: 2 días, desde ayer">
                    @error('duracion_sintomas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Signos Vitales Iniciales -->
            <div class="card bg-light mb-3">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="ri-pulse-line"></i> Signos Vitales Iniciales</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Temperatura (°C)</label>
                            <input type="number" step="0.1" min="35" max="43"
                                   class="form-control"
                                   wire:model.defer="temperatura_rectal"
                                   placeholder="38.5">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">FC (lpm)</label>
                            <input type="number" min="30" max="300"
                                   class="form-control"
                                   wire:model.defer="frecuencia_cardiaca"
                                   placeholder="120">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">FR (rpm)</label>
                            <input type="number" min="5" max="120"
                                   class="form-control"
                                   wire:model.defer="frecuencia_respiratoria"
                                   placeholder="20">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.01" min="0.1" max="100"
                                   class="form-control"
                                   wire:model.defer="peso_actual_kg"
                                   placeholder="5.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluación Rápida -->
            <div class="card bg-light mb-3">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="ri-checkbox-circle-line"></i> Evaluación Rápida</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="hemorragias_controladas"
                                       wire:model.defer="hemorragias_controladas">
                                <label class="form-check-label" for="hemorragias_controladas">
                                    <i class="ri-droplet-line text-danger"></i>
                                    Hemorragias controladas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="heridas_abiertas"
                                       wire:model.defer="heridas_abiertas">
                                <label class="form-check-label" for="heridas_abiertas">
                                    <i class="ri-bandage-line text-warning"></i>
                                    Heridas abiertas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="fractura_sospechada"
                                       wire:model.defer="fractura_sospechada">
                                <label class="form-check-label" for="fractura_sospechada">
                                    <i class="ri-bone-line text-info"></i>
                                    Fractura sospechada
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">
                        <i class="ri-chat-quote-line text-info"></i>
                        Observaciones de Triaje
                    </label>
                    <textarea class="form-control @error('observaciones_triage') is-invalid @enderror"
                              rows="3"
                              wire:model.defer="observaciones_triage"
                              placeholder="Observaciones adicionales, comportamiento, etc."></textarea>
                    @error('observaciones_triage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="history.back()">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="ri-save-line me-1"></i>Guardar Triaje
                </button>
            </div>
        </form>
    </div>
</div>
