<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="ri-heart-pulse-line me-2"></i>
            Registro de Recuperación Post-operatoria
        </h5>
    </div>
    <div class="card-body">
        @if($consulta->mascota)
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="ri-paw-line fs-4 me-3"></i>
                <div>
                    <strong>{{ $consulta->mascota->nombre }}</strong><br>
                    <small>{{ $consulta->mascota->especie?->nombre }} - {{ $consulta->mascota->raza?->nombre }}</small>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="guardar">
            <!-- Constantes Vitales en Recuperación -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-thermometer-line text-success me-2"></i>
                        Constantes Vitales
                    </h6>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Temperatura (°C)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="temperatura_actual"
                           step="0.1"
                           min="35"
                           max="43"
                           placeholder="Temp rectal">
                    @error('temperatura_actual')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">FC (lpm)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="frecuencia_cardiaca"
                           min="30"
                           max="300"
                           placeholder="Frecuencia cardíaca">
                    @error('frecuencia_cardiaca')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">FR (rpm)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="frecuencia_respiratoria"
                           min="5"
                           max="120"
                           placeholder="Frecuencia respiratoria">
                    @error('frecuencia_respiratoria')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">SpO₂ (%)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="oxigenacion_spo2"
                           min="80"
                           max="100"
                           placeholder="Saturación O₂">
                    @error('oxigenacion_spo2')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Presión Arterial</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="presion_arterial"
                           placeholder="Ej: 120/80 mmHg">
                    @error('presion_arterial')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Hora de Ingreso a Recuperación</label>
                    <input type="time"
                           class="form-control"
                           wire:model.defer="hora_ingreso_recuperacion">
                    @error('hora_ingreso_recuperacion')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Estado Neurológico -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-brain-line text-success me-2"></i>
                        Estado Neurológico
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nivel de Conciencia</label>
                    <select class="form-select" wire:model.defer="conciencia">
                        <option value="">Seleccione...</option>
                        <option value="alerta">Alerta</option>
                        <option value="letargico">Letárgico</option>
                        <option value="estuporoso">Estuporoso</option>
                        <option value="comatoso">Comatoso</option>
                    </select>
                    @error('conciencia')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Reflejos Presentes</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="reflejos_presentes"
                           placeholder="Ej: Pupilar, deglución, pedal">
                    @error('reflejos_presentes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Manejo del Dolor -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-emotion-sad-line text-success me-2"></i>
                        Evaluación y Manejo del Dolor
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Escala de Dolor (0-10)</label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="range"
                               class="form-range"
                               min="0"
                               max="10"
                               step="1"
                               wire:model.defer="escala_dolor"
                               style="flex: 1">
                        <span class="badge bg-primary fs-5" style="min-width: 40px; text-align: center;">
                            {{ $escala_dolor ?? '0' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">0: Sin dolor</small>
                        <small class="text-muted">5: Moderado</small>
                        <small class="text-muted">10: Máximo</small>
                    </div>
                    @error('escala_dolor')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Descripción del Dolor</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="dolor_evaluado"
                           placeholder="Ej: Dolor leve en sitio quirúrgico">
                    @error('dolor_evaluado')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Analgesia Administrada</label>
                    <textarea class="form-control"
                              wire:model.defer="analgesia_administrada"
                              rows="2"
                              placeholder="Medicamentos analgésicos administrados con dosis y vía"></textarea>
                    @error('analgesia_administrada')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Fluidoterapia -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-drop-line text-success me-2"></i>
                        Fluidoterapia
                    </h6>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo de Fluido</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="tipo_fluido"
                           placeholder="Ej: Solución salina 0.9%">
                    @error('tipo_fluido')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Volumen (ml/hr)</label>
                    <input type="number"
                           class="form-control"
                           wire:model.defer="volumen_ml_hr"
                           min="0"
                           max="1000"
                           placeholder="ml/hora">
                    @error('volumen_ml_hr')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Observaciones</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="fluidoterapia"
                           placeholder="Notas sobre fluidoterapia">
                    @error('fluidoterapia')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Funciones Corporales -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-file-list-3-line text-success me-2"></i>
                        Funciones Corporales
                    </h6>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="miccion_presente"
                               wire:model.defer="miccion_presente">
                        <label class="form-check-label" for="miccion_presente">
                            <strong>Micción Presente</strong>
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="deposicion_presente"
                               wire:model.defer="deposicion_presente">
                        <label class="form-check-label" for="deposicion_presente">
                            <strong>Defecación Presente</strong>
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Apetito</label>
                    <select class="form-select" wire:model.defer="apetito">
                        <option value="">Seleccione...</option>
                        <option value="normal">Normal</option>
                        <option value="disminuido">Disminuido</option>
                        <option value="ausente">Ausente</option>
                    </select>
                    @error('apetito')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Movilidad</label>
                    <select class="form-select" wire:model.defer="movilidad">
                        <option value="">Seleccione...</option>
                        <option value="normal">Normal</option>
                        <option value="limitada">Limitada</option>
                        <option value="no_puede_caminar">No puede caminar</option>
                    </select>
                    @error('movilidad')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Herida Quirúrgica -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">
                        <i class="ri-bandage-line text-success me-2"></i>
                        Estado de Herida Quirúrgica
                    </h6>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción de la Herida</label>
                    <textarea class="form-control"
                              wire:model.defer="herida_quirurgica"
                              rows="2"
                              placeholder="Estado de incisión, suturas, inflamación, etc."></textarea>
                    @error('herida_quirurgica')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="sangrado_activo"
                               wire:model.defer="sangrado_activo">
                        <label class="form-check-label" for="sangrado_activo">
                            <strong class="text-danger">¿Sangrado Activo?</strong>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Complicaciones -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="ri-alert-triangle-line me-1"></i>
                    Complicaciones en Recuperación
                </label>
                <textarea class="form-control"
                          wire:model.defer="complicaciones"
                          rows="2"
                          placeholder="Describa cualquier complicación presentada"></textarea>
                @error('complicaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label class="form-label">Observaciones Adicionales</label>
                <textarea class="form-control"
                          wire:model.defer="observaciones"
                          rows="2"
                          placeholder="Notas adicionales sobre la recuperación"></textarea>
                @error('observaciones')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Alerta informativa -->
            <div class="alert alert-success d-flex align-items-start" role="alert">
                <i class="ri-information-line fs-5 me-2"></i>
                <div>
                    <strong>Criterios de Alta de Recuperación:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Conciencia alerta o respondiendo a estímulos</li>
                        <li>Constantes vitales estables dentro de rangos normales</li>
                        <li>Capacidad de mantenerse en posición esternal</li>
                        <li>Sin sangrado activo ni complicaciones</li>
                        <li>Dolor controlado adecuadamente</li>
                    </ul>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.gestion.consultas.recuperacion') }}"
                   class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i>
                    Cancelar
                </a>
                <button type="submit"
                        class="btn btn-success"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>
                        Guardar Registro de Recuperación
                    </span>
                    <span wire:loading>
                        <i class="ri-loader-4-line me-1"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@if(session()->has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="ri-checkbox-circle-line me-2"></i>
                        <strong class="me-auto">Éxito</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    </script>
@endif
